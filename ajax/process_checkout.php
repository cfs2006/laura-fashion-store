<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$payment_method = isset($input['payment_method']) ? $input['payment_method'] : '';

if (empty($payment_method)) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng chọn phương thức thanh toán.']);
    exit();
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if(empty($cart_items)){
    echo json_encode(['status' => 'error', 'message' => 'Giỏ hàng đang trống.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$total_amount = 0;
foreach($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

$voucher_id = null;
if (isset($_SESSION['applied_voucher_id'], $_SESSION['applied_voucher_discount'])) {
    $voucher_id = $_SESSION['applied_voucher_id'];
    $discount_percent = $_SESSION['applied_voucher_discount'];
    $discount_amount = $total_amount * ($discount_percent / 100);
    $total_amount -= $discount_amount;
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // Insert order (status 'Chờ xác nhận' = Pending)
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, status, voucher_id) VALUES (:user_id, :total_amount, :payment_method, 'Chờ xác nhận', :voucher_id)");
    $stmt->execute([
        'user_id' => $user_id,
        'total_amount' => $total_amount,
        'payment_method' => $payment_method,
        'voucher_id' => $voucher_id
    ]);
    $order_id = $conn->lastInsertId();

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
    foreach($cart_items as $item) {
        $stmt->execute([
            'order_id' => $order_id,
            'product_id' => $item['id'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ]);
    }

    // Check and update voucher atomically to prevent race conditions (nano-second sync)
    if ($voucher_id) {
        $stmtUpdate = $conn->prepare("
            UPDATE vouchers 
            SET current_uses = current_uses + 1 
            WHERE id = :id 
              AND is_active = 1 
              AND (max_uses IS NULL OR current_uses < max_uses)
        ");
        $stmtUpdate->execute(['id' => $voucher_id]);
        
        // If rowCount is 0, it means the condition failed (someone else took the last slot)
        if ($stmtUpdate->rowCount() === 0) {
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Rất tiếc! Mã giảm giá vừa hết lượt sử dụng ngay trước khi bạn bấm thanh toán. Vui lòng gỡ mã và thử lại.']);
            exit();
        }

        // Insert voucher usage history safely
        $stmt = $conn->prepare("INSERT INTO voucher_usage (voucher_id, user_id) VALUES (:voucher_id, :user_id)");
        $stmt->execute([
            'voucher_id' => $voucher_id,
            'user_id' => $user_id
        ]);

        // Auto-disable if max uses reached (for UI sync)
        $stmtCheck = $conn->prepare("SELECT max_uses, current_uses FROM vouchers WHERE id = :id");
        $stmtCheck->execute(['id' => $voucher_id]);
        $vData = $stmtCheck->fetch();
        if ($vData['max_uses'] !== null && $vData['current_uses'] >= $vData['max_uses']) {
            $stmtDisable = $conn->prepare("UPDATE vouchers SET is_active = 0 WHERE id = :id");
            $stmtDisable->execute(['id' => $voucher_id]);
        }
    }

    // Commit
    $conn->commit();

    // Clear cart and voucher
    unset($_SESSION['cart']);
    unset($_SESSION['applied_voucher_id']);
    unset($_SESSION['applied_voucher_discount']);

    echo json_encode(['status' => 'success', 'message' => 'Thanh toán thành công.']);
} catch(PDOException $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
}
