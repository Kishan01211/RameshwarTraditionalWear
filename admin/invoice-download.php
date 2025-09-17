<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

function fetch_bookings(PDO $pdo, array $ids): array {
    if (empty($ids)) return [];
    $in = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT b.*, u.name AS uname, u.email, u.address,
                   p.product_name, p.price_per_day
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN products p ON b.product_id = p.id
            WHERE b.id IN ($in)
            ORDER BY b.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    return $stmt->fetchAll();
}

function rental_days($start, $end): int {
    $s = new DateTime($start);
    $e = new DateTime($end);
    $diff = $s->diff($e);
    return max(1, (int)$diff->format('%a'));
}

class InvoicePDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,8,'Rameshwar Traditional Wear',0,1,'L');
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,'GSTIN: N/A | Email: traditionawear2025@gmail.com | Phone: +91-00000-00000',0,1,'L');
        $this->Ln(2);
        $this->SetDrawColor(200,200,200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(6);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$idsParam = trim((string)($_GET['ids'] ?? ''));
$singleId = (int)($_GET['id'] ?? 0);

$ids = [];
if ($idsParam !== '') {
    foreach (explode(',', $idsParam) as $v) {
        $n = (int)trim($v);
        if ($n > 0) $ids[] = $n;
    }
} elseif ($singleId > 0) {
    $ids = [$singleId];
}

$rows = fetch_bookings($pdo, $ids);
if (empty($rows)) {
    http_response_code(404);
    exit('No bookings found for the given IDs.');
}

$userIds = array_unique(array_column($rows, 'user_id'));
$customerNote = count($userIds) > 1 ? 'Multiple Customers' : '';

$pdf = new InvoicePDF('P','mm','A4');
$pdf->AddPage();

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,7,'Invoice',0,1,'R');
$pdf->SetFont('Arial','',10);
$first = $rows[0];
$pdf->Cell(120,6,'Bill To: '.($customerNote ?: $first['uname']),0,0,'L');
$pdf->Cell(0,6,'Date: '.date('M j, Y'),0,1,'R');
$pdf->Cell(120,6,($customerNote ? '' : ($first['email'].' | '.($first['address'] ?: ''))),0,1,'L');
$pdf->Ln(3);

$pdf->SetFillColor(245,245,245);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(18,8,'Order#',1,0,'C',true);
$pdf->Cell(52,8,'Product',1,0,'L',true);
$pdf->Cell(28,8,'Start',1,0,'C',true);
$pdf->Cell(28,8,'End',1,0,'C',true);
$pdf->Cell(14,8,'Days',1,0,'C',true);
$pdf->Cell(24,8,'Rate/Day',1,0,'R',true);
$pdf->Cell(26,8,'Line Total',1,1,'R',true);

$pdf->SetFont('Arial','',10);
$grand = 0.0;
foreach ($rows as $r) {
    $days = rental_days($r['start_date'], $r['end_date']);
    $rate = (float)$r['price_per_day'];
    $line = (float)$r['total_price'];
    $grand += $line;

    $pdf->Cell(18,8,'#'.$r['id'],1,0,'C');
    $pdf->Cell(52,8,iconv('UTF-8','windows-1252//TRANSLIT',$r['product_name']),1,0,'L');
    $pdf->Cell(28,8,date('Y-m-d', strtotime($r['start_date'])),1,0,'C');
    $pdf->Cell(28,8,date('Y-m-d', strtotime($r['end_date'])),1,0,'C');
    $pdf->Cell(14,8,(string)$days,1,0,'C');
    $pdf->Cell(24,8,'₹'.number_format($rate,2),1,0,'R');
    $pdf->Cell(26,8,'₹'.number_format($line,2),1,1,'R');
}

$pdf->SetFont('Arial','B',11);
$pdf->Cell(160,9,'Grand Total',1,0,'R');
$pdf->Cell(26,9,'₹'.number_format($grand,2),1,1,'R');

$pdf->Ln(6);
$pdf->SetFont('Arial','',9);
$pdf->MultiCell(0,5,"Thank you for your business. Please retain this invoice for your records.");

$filename = 'invoice_'.(count($rows) === 1 ? $rows[0]['id'] : ('multi_'.date('Ymd_His'))).'.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
$pdf->Output('D', $filename);
exit;
