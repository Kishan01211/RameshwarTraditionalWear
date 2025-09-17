<?php
// Secure user invoice download (single or multi-order)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../config/db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Simple HTML invoice as fallback
function generateHtmlInvoice($rows) {
    $first = $rows[0];
    $total = 0;
    $items = [];
    
    // Calculate totals
    foreach ($rows as $row) {
        $days = max(1, (int)((strtotime($row['end_date']) - strtotime($row['start_date'])) / 86400));
        $subtotal = $days * $row['price'];
        $total += $subtotal;
        $items[] = [
            'id' => $row['id'],
            'name' => $row['product_name'],
            'days' => $days,
            'price' => $row['price'],
            'subtotal' => $subtotal,
            'rental_period' => date('M j', strtotime($row['start_date'])) . ' - ' . date('M j, Y', strtotime($row['end_date']))
        ];
    }
    
    $gst = $total * 0.18; // 18% GST
    $grandTotal = $total + $gst;
    
    // Generate HTML
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <title>Invoice #' . $first['id'] . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
            .container { max-width: 800px; margin: 0 auto; }
            .header { text-align: center; margin-bottom: 20px; }
            .bill-to { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .mt-20 { margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Rameshwar Traditional Wear</h1>
                <p>GSTIN: N/A | Email: traditionawear2025@gmail.com | Phone: +91-00000-00000</p>
            </div>
            
            <h2>INVOICE</h2>
            
            <div class="bill-to">
                <p><strong>Bill To:</strong> ' . htmlspecialchars($first['uname']) . '</p>
                <p>' . htmlspecialchars($first['email']) . ' | ' . htmlspecialchars($first['address'] ?? '') . '</p>
                <p><strong>Date:</strong> ' . date('M j, Y') . '</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Order#</th>
                        <th>Product</th>
                        <th>Rental Period</th>
                        <th>Days</th>
                        <th>Price/Day</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($items as $item) {
        $html .= '<tr>
                    <td>#' . $item['id'] . '</td>
                    <td>' . htmlspecialchars($item['name']) . '</td>
                    <td>' . $item['rental_period'] . '</td>
                    <td>' . $item['days'] . '</td>
                    <td class="text-right">₹' . number_format($item['price'], 2) . '</td>
                    <td class="text-right">₹' . number_format($item['subtotal'], 2) . '</td>
                </tr>';
    }
    
    $html .= '        <tr>
                        <td colspan="5" class="text-right"><strong>Subtotal</strong></td>
                        <td class="text-right"><strong>₹' . number_format($total, 2) . '</strong></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">GST (18%)</td>
                        <td class="text-right">₹' . number_format($gst, 2) . '</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right"><strong>Grand Total</strong></td>
                        <td class="text-right"><strong>₹' . number_format($grandTotal, 2) . '</strong></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="mt-20">
                <h3>Terms & Conditions:</h3>
                <ol>
                    <li>This is a computer generated invoice and does not require a physical signature.</li>
                    <li>Payment is due within 7 days of invoice date.</li>
                    <li>For any queries, please contact traditionawear2025@gmail.com</li>
                </ol>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

function fetch_user_bookings(PDO $pdo, array $ids, int $userId): array {
    if (empty($ids)) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT b.*, p.product_name, p.price_per_day as price, p.image_url, u.name as uname, u.email, u.phone, u.address 
            FROM bookings b 
            JOIN products p ON b.product_id = p.id 
            JOIN users u ON b.user_id = u.id 
            WHERE b.id IN ($placeholders) AND b.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($ids, [$userId]));
    return $stmt->fetchAll();
}

// Get booking IDs from request
$idsParam = trim((string)($_GET['ids'] ?? ''));
$singleId = (int)($_GET['id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

// Process IDs
$ids = [];
if ($idsParam !== '') {
    foreach (explode(',', $idsParam) as $v) {
        $n = (int)trim($v);
        if ($n > 0) $ids[] = $n;
    }
} elseif ($singleId > 0) {
    $ids = [$singleId];
}

// Fetch bookings
$rows = fetch_user_bookings($pdo, $ids, $userId);
if (empty($rows)) {
    http_response_code(404);
    exit('No bookings found for your account with the given IDs.');
}

// Include FPDF library
require_once __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Add header
$pdf->Cell(0, 10, 'Rameshwar Traditional Wear', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'GSTIN: N/A | Email: traditionawear2025@gmail.com | Phone: +91-00000-00000', 0, 1);
$pdf->Ln(5);

// Add invoice title
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'INVOICE', 0, 1, 'R');

// Add customer info
$pdf->SetFont('Arial', '', 10);
$first = $rows[0];
$pdf->Cell(0, 5, 'Bill To: ' . $first['uname'], 0, 1);
$pdf->Cell(0, 5, $first['email'], 0, 1);
$pdf->Cell(0, 5, $first['address'] ?? '', 0, 1);
$pdf->Cell(0, 5, 'Date: ' . date('M j, Y'), 0, 1);
$pdf->Ln(5);

// Add table headers
$pdf->SetFillColor(211, 211, 211);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 10, 'Order#', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Product', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Rental Period', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Days', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Price/Day', 1, 0, 'R', true);
$pdf->Cell(30, 10, 'Total', 1, 1, 'R', true);

// Add table rows
$pdf->SetFont('Arial', '', 10);
$total = 0;
foreach ($rows as $row) {
    $days = max(1, (int)((strtotime($row['end_date']) - strtotime($row['start_date'])) / 86400));
    $subtotal = $days * $row['price'];
    $total += $subtotal;
    
    $pdf->Cell(20, 10, '#' . $row['id'], 1, 0, 'C');
    $pdf->Cell(50, 10, substr($row['product_name'], 0, 25), 1, 0, 'L');
    $pdf->Cell(40, 10, date('M j', strtotime($row['start_date'])) . ' - ' . date('M j', strtotime($row['end_date'])), 1, 0, 'C');
    $pdf->Cell(20, 10, $days, 1, 0, 'C');
    $pdf->Cell(30, 10, '₹' . number_format($row['price'], 2), 1, 0, 'R');
    $pdf->Cell(30, 10, '₹' . number_format($subtotal, 2), 1, 1, 'R');
}

// Add totals
$gst = $total * 0.18;
$grandTotal = $total + $gst;

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(160, 10, 'Subtotal', 1, 0, 'R', true);
$pdf->Cell(30, 10, '₹' . number_format($total, 2), 1, 1, 'R');
$pdf->Cell(160, 10, 'GST (18%)', 1, 0, 'R', true);
$pdf->Cell(30, 10, '₹' . number_format($gst, 2), 1, 1, 'R');
$pdf->Cell(160, 10, 'Grand Total', 1, 0, 'R', true);
$pdf->Cell(30, 10, '₹' . number_format($grandTotal, 2), 1, 1, 'R');

// Add terms and conditions
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Terms & Conditions:', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, "1. This is a computer generated invoice and does not require a physical signature.\n2. Payment is due within 7 days of invoice date.\n3. For any queries, please contact traditionawear2025@gmail.com");

// Output PDF
$invoiceNumber = 'INV-' . date('Ymd') . '-' . implode('-', $ids);
$pdf->Output('D', $invoiceNumber . '.pdf');
exit;
