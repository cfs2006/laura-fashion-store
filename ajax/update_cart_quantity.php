<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$change = isset($input['qty_change']) ? (int)$input['qty_change'] : 0;

if ($product_id <= 0 || $change === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

if (!isset($_SESSION['cart'][$product_id])) {
    echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không có trong giỏ hàng.']);
    exit();
}

require_once '../db_connect.php';

$current_qty = $_SESSION['cart'][$product_id]['quantity'];
$new_qty = $current_qty + $change;

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

if ($new_qty > $allowed) {
    if ($allowed <= 0) {
        $msg = "Xin lỗi, mỗi khách hàng chỉ được mua tối đa 100 sản phẩm giống nhau. Bạn đã mua đủ giới hạn này rồi.";
    } else {
        $msg = "Xin lỗi, giới hạn là 100 sản phẩm. Bạn đã mua $past_bought sản phẩm này trước đó, nên chỉ có thể thêm tối đa $allowed sản phẩm nữa.";
    }
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit();
}

if ($new_qty <= 0) {
    unset($_SESSION['cart'][$product_id]);
    $new_qty = 0;
} else {
    $_SESSION['cart'][$product_id]['quantity'] = $new_qty;
}

$item_total = 0;
if ($new_qty > 0) {
    $item_total = $_SESSION['cart'][$product_id]['price'] * $new_qty;
}

$cart_count = 0;
$total_amount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
    $total_amount += $item['price'] * $item['quantity'];
}

echo json_encode([
    'status' => 'success',
    'new_qty' => $new_qty,
    'item_total' => $item_total,
    'cart_count' => $cart_count,
    'total_amount' => $total_amount,
    'cart_empty' => empty($_SESSION['cart'])
]);
