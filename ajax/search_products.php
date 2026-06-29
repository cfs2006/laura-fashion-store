<?php
require_once '../db_connect.php';
require_once '../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$query = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$likeQuery = '%' . $query . '%';

try {
    if ($query === '') {
        $stmt = $conn->prepare("
            SELECT id, name, image_url, price
            FROM products
            ORDER BY created_at DESC
        ");
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("
            SELECT id, name, image_url, price
            FROM products
            WHERE name LIKE :query
            ORDER BY created_at DESC
        ");
        $stmt->execute(['query' => $likeQuery]);
    }

    $products = $stmt->fetchAll();

    foreach ($products as &$product) {
        $product['image_url'] = asset_image_url($product['image_url'] ?? '');
    }
    unset($product);

    echo json_encode([
        'status' => 'success',
        'products' => $products,
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Không thể tìm kiếm sản phẩm.'
    ], JSON_UNESCAPED_UNICODE);
}
