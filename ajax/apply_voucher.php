<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$code = isset($input['code']) ? strtoupper(trim($input['code'])) : '';

if (empty($code) || strlen($code) !== 6) {
    echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá không đúng hoặc đã được sử dụng']);
    exit();
}

// Check if voucher exists
$stmt = $conn->prepare("SELECT id, discount_percent, is_active, max_uses, current_uses FROM vouchers WHERE code = :code");
$stmt->execute(['code' => $code]);
$voucher = $stmt->fetch();

if (!$voucher) {
    echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá không đúng hoặc đã được sử dụng']);
    exit();
}

if ($voucher['is_active'] == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá này đã bị vô hiệu hóa hoặc đã hết lượt sử dụng.']);
    exit();
}

if ($voucher['max_uses'] !== null && $voucher['current_uses'] >= $voucher['max_uses']) {
    echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá này đã hết lượt sử dụng.']);
    exit();
}

// Check if user already used this voucher
$stmt = $conn->prepare("SELECT id FROM voucher_usage WHERE voucher_id = :voucher_id AND user_id = :user_id");
$stmt->execute([
    'voucher_id' => $voucher['id'],
    'user_id' => $_SESSION['user_id']
]);
$usage = $stmt->fetch();

if ($usage) {
    echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá không đúng hoặc đã được sử dụng']);
    exit();
}

// Valid voucher
$_SESSION['applied_voucher_id'] = $voucher['id'];
$_SESSION['applied_voucher_discount'] = $voucher['discount_percent'];

echo json_encode([
    'status' => 'success',
    'code' => $code,
    'discount_percent' => $voucher['discount_percent']
]);
