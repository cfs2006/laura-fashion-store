<?php
session_start();
require_once '../db_connect.php';
require_once '../includes/newsletter_mailer.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? strtolower(trim($input['email'])) : '';
$now = time();

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Vui lòng nhập email hợp lệ.'
    ]);
    exit();
}

if (isset($_SESSION['newsletter_last_submit_at']) && ($now - (int) $_SESSION['newsletter_last_submit_at']) < 10) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Vui lòng chờ 10 giây trước khi đăng ký lại.'
    ]);
    exit();
}

try {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $stmt = $conn->prepare("
        INSERT INTO newsletter_subscribers (email)
        VALUES (:email)
        ON DUPLICATE KEY UPDATE email = VALUES(email)
    ");
    $stmt->execute(['email' => $email]);
    $_SESSION['newsletter_last_submit_at'] = $now;

    $mailSent = sendNewsletterWelcomeEmail($email);
    if (!$mailSent) {
        error_log('[newsletter] Welcome email failed for ' . $email);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Đăng ký thành công. Vui lòng kiểm tra cả hộp thư đến và thư rác.'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Không thể lưu email vào hệ thống.'
    ]);
}
