<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // get JSON or POST payload
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $message = trim($input['message'] ?? '');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');
    }

    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (:name, :email, :message)");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'message' => $message
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Tin nhắn của bạn đã được gửi. Chúng tôi sẽ phản hồi sớm nhất!']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
