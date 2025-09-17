<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? $_POST['type'] ?? '';

try {
    switch ($type) {
        case 'categories':
            $stmt = $pdo->prepare("SELECT DISTINCT c.id, c.name FROM categories c 
                                   JOIN products p ON p.category_id = c.id 
                                   WHERE p.status = 'active' AND p.quantity_available > 0");
            $stmt->execute();
            $categories = $stmt->fetchAll();
            echo json_encode($categories);
            break;

        case 'sizes':
            $stmt = $pdo->query("SELECT size FROM products WHERE status = 'active' AND quantity_available > 0");
            $sizes = [];
            while ($row = $stmt->fetch()) {
                $product_sizes = explode(',', $row['size']);
                foreach ($product_sizes as $size) {
                    $size = trim($size);
                    if ($size) {
                        $sizes[$size] = true;
                    }
                }
            }
            $sizes = array_keys($sizes);
            sort($sizes, SORT_NATURAL);
            echo json_encode($sizes);
            break;

        case 'colors':
            $sql = "SELECT color FROM products WHERE status = 'active' AND quantity_available > 0";
            $params = [];

            if (!empty($_GET['category'])) {
                $sql .= " AND category_id = ?";
                $params[] = $_GET['category'];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $colors = [];
            while ($row = $stmt->fetch()) {
                $product_colors = explode(',', $row['color']);
                foreach ($product_colors as $color) {
                    $color = trim(strtolower($color));
                    if ($color) {
                        $colors[$color] = true;
                    }
                }
            }
            $colors = array_keys($colors);
            sort($colors);
            echo json_encode($colors);
            break;

        case 'price-range':
            $stmt = $pdo->query("SELECT MIN(price_per_day) as min_price, MAX(price_per_day) as max_price 
                                FROM products WHERE status = 'active' AND quantity_available > 0");
            $range = $stmt->fetch();
            echo json_encode($range);
            break;

        case 'products':
        default:
            $where = ["p.status = 'active'", "p.quantity_available > 0"];
            $params = [];

            if (!empty($_POST['category'])) {
                $where[] = "p.category_id = ?";
                $params[] = $_POST['category'];
            }

            if (!empty($_POST['size'])) {
                $where[] = "FIND_IN_SET(?, p.size)";
                $params[] = $_POST['size'];
            }

            if (!empty($_POST['color'])) {
                $where[] = "FIND_IN_SET(?, p.color)";
                $params[] = $_POST['color'];
            }

            if (!empty($_POST['minPrice'])) {
                $where[] = "p.price_per_day >= ?";
                $params[] = $_POST['minPrice'];
            }

            if (!empty($_POST['maxPrice'])) {
                $where[] = "p.price_per_day <= ?";
                $params[] = $_POST['maxPrice'];
            }

            // Try multiple schema variants safely
            $baseWhere = " WHERE " . implode(" AND ", $where);
            $orderFallback = " ORDER BY p.id DESC"; // guaranteed column

            $variants = [
                // Variant 1: image_url + created_at
                "SELECT p.id, p.product_name, p.description, p.image_url AS image_url, p.size, p.color, p.price_per_day, p.quantity_available, p.status, p.created_at AS created_at, c.name AS category FROM products p LEFT JOIN categories c ON p.category_id = c.id" . $baseWhere . " ORDER BY p.created_at DESC",
                // Variant 2: image + created_at
                "SELECT p.id, p.product_name, p.description, p.image AS image_url, p.size, p.color, p.price_per_day, p.quantity_available, p.status, p.created_at AS created_at, c.name AS category FROM products p LEFT JOIN categories c ON p.category_id = c.id" . $baseWhere . " ORDER BY p.created_at DESC",
                // Variant 3: product_image + added_on
                "SELECT p.id, p.product_name, p.description, p.product_image AS image_url, p.size, p.color, p.price_per_day, p.quantity_available, p.status, p.added_on AS created_at, c.name AS category FROM products p LEFT JOIN categories c ON p.category_id = c.id" . $baseWhere . " ORDER BY p.added_on DESC",
                // Variant 4: minimal select, order by id
                "SELECT p.id, p.product_name, p.description, NULL AS image_url, p.size, p.color, p.price_per_day, p.quantity_available, p.status, p.id AS created_at, c.name AS category FROM products p LEFT JOIN categories c ON p.category_id = c.id" . $baseWhere . $orderFallback,
            ];

            $products = [];
            $lastErr = null;
            foreach ($variants as $sql) {
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $products = $stmt->fetchAll();
                    $lastErr = null;
                    break;
                } catch (Throwable $e) {
                    $lastErr = $e;
                    continue;
                }
            }

            if ($lastErr) {
                // Return a well-formed error for diagnostics
                http_response_code(500);
                echo json_encode(['error' => 'Query failed', 'details' => $lastErr->getMessage()]);
                break;
            }

            echo json_encode($products);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>