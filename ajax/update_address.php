<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$address = isset($input['address']) ? trim($input['address']) : '';

if (empty($address)) {
    echo json_encode(['status' => 'error', 'message' => 'Địa chỉ không được để trống.']);
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE users SET address = :address WHERE id = :id");
    $stmt->execute([
        'address' => $address,
        'id' => $_SESSION['user_id']
    ]);

    echo json_encode(['status' => 'success']);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}
