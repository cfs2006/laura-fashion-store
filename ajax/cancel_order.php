<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = isset($input['order_id']) ? (int)$input['order_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mã đơn hàng không hợp lệ.']);
    exit();
}

try {
    $conn->beginTransaction();

    // Check if order exists, belongs to user, and is 'Chờ xác nhận'
    $stmt = $conn->prepare("SELECT status, voucher_id FROM orders WHERE id = :order_id AND user_id = :user_id FOR UPDATE");
    $stmt->execute(['order_id' => $order_id, 'user_id' => $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng.']);
        exit();
    }

    if ($order['status'] !== 'Chờ xác nhận') {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Không thể hủy đơn hàng ở trạng thái này.']);
        exit();
    }

    // Update order status
    $stmtUpdate = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = :order_id");
    $stmtUpdate->execute(['order_id' => $order_id]);

    // Handle voucher logic
    if ($order['voucher_id']) {
        $voucher_id = $order['voucher_id'];
        
        // Delete voucher usage
        $stmtDel = $conn->prepare("DELETE FROM voucher_usage WHERE voucher_id = :voucher_id AND user_id = :user_id LIMIT 1");
        $stmtDel->execute(['voucher_id' => $voucher_id, 'user_id' => $user_id]);
        
        // Restore uses count
        $stmtRestore = $conn->prepare("UPDATE vouchers SET current_uses = GREATEST(0, current_uses - 1) WHERE id = :voucher_id");
        $stmtRestore->execute(['voucher_id' => $voucher_id]);
        
        // Re-enable voucher if it was disabled due to reaching max_uses
        $stmtReEnable = $conn->prepare("UPDATE vouchers SET is_active = 1 WHERE id = :voucher_id AND is_active = 0 AND (max_uses IS NULL OR current_uses < max_uses)");
        $stmtReEnable->execute(['voucher_id' => $voucher_id]);
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Hủy đơn thành công, nhân viên của chúng tôi sẽ liên hệ bạn để hoàn tiền trong thời gian sớm nhất']);
} catch(PDOException $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}
