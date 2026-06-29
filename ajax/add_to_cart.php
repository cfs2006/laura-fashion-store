<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    // Luu lai URL trang hien tai de chuyen huong sau khi login
    $_SESSION['redirect_after_login'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../shop.php';
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để thêm vào giỏ hàng.', 'redirect' => 'auth.php']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

// Check product exists
$stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id = :id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại.']);
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate past purchases
$stmt_past = $conn->prepare("
    SELECT SUM(oi.quantity) as total_bought 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.user_id = :user_id 
    AND o.status != 'Cancelled' 
    AND oi.product_id = :product_id
");
$stmt_past->execute([
    'user_id' => $_SESSION['user_id'], 
    'product_id' => $product_id
]);
$past_data = $stmt_past->fetch();
$past_bought = $past_data['total_bought'] ? (int)$past_data['total_bought'] : 0;

$allowed = 100 - $past_bought;

// Add to cart session
$current_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['quantity'] : 0;

if ($current_qty + $quantity > $allowed) {
    if ($allowed <= 0) {
        $msg = "Xin lỗi, mỗi khách hàng chỉ được mua tối đa 100 sản phẩm giống nhau. Bạn đã mua đủ giới hạn này rồi.";
    } else {
        $msg = "Xin lỗi, giới hạn là 100 sản phẩm. Bạn đã mua $past_bought sản phẩm này trước đó, nên chỉ có thể thêm tối đa $allowed sản phẩm nữa.";
    }
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit();
}

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = [
        'id' => $product_id,
        'name' => $product['name'],
        'price' => $product['price'],
        'image_url' => $product['image_url'],
        'quantity' => $quantity
    ];
}

// Count total
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

echo json_encode([
    'status' => 'success',
    'message' => 'Thêm vào giỏ hàng thành công',
    'cart_count' => $cart_count
]);
