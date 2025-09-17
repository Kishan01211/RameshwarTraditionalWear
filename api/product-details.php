<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    // Normalize image_url for modal display
    if (!empty($product['image_url'])) {
        $imgs = array_map(function($img) {
            $img = ltrim(trim($img), '.');
            if (strpos($img, '/rtwrs_web/') !== 0) {
                $img = '/rtwrs_web/' . ltrim($img, '/');
            }
            return $img;
        }, explode(',', $product['image_url']));
        $product['image_url'] = implode(',', $imgs);
    }
    echo json_encode($product);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>