<?php
require_once 'includes/newsletter_mailer.php';
require_once 'db_connect.php';

$email = isset($_GET['email']) ? trim((string) $_GET['email']) : '';
$token = isset($_GET['token']) ? trim((string) $_GET['token']) : '';
$expectedToken = newsletter_unsubscribe_token($email);
$validRequest = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && hash_equals($expectedToken, $token);

$title = 'Hủy đăng ký bản tin';
$message = 'Yêu cầu không hợp lệ.';
$success = false;

if ($validRequest) {
    try {
        $stmt = $conn->prepare('DELETE FROM newsletter_subscribers WHERE email = :email');
        $stmt->execute(['email' => strtolower($email)]);

        $success = true;
        $message = 'Bạn đã hủy đăng ký bản tin thành công.';
    } catch (PDOException $e) {
        $message = 'Không thể hủy đăng ký lúc này. Vui lòng thử lại sau.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; background: #f5f5f0; color: #111; }
        .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { max-width: 560px; width: 100%; background: #fff; border: 1px solid #e5e7eb; border-radius: 24px; padding: 40px 32px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,.06); }
        .eyebrow { text-transform: uppercase; letter-spacing: .2em; font-size: 12px; color: #6b7280; margin-bottom: 14px; }
        h1 { font-family: Georgia, 'Times New Roman', serif; font-size: 32px; line-height: 1.2; margin: 0 0 16px; }
        p { line-height: 1.8; color: #374151; }
        .btn { display: inline-block; margin-top: 16px; padding: 12px 22px; border-radius: 999px; background: #0a0a0a; color: #fff; text-decoration: none; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; font-size: 13px; }
        .btn:hover { background: #d4af37; color: #0a0a0a; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="eyebrow"><?= $success ? 'Thành công' : 'Lưu ý' ?></div>
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><?= htmlspecialchars($message) ?></p>
            <a class="btn" href="<?= htmlspecialchars(newsletter_mail_config()['base_url']) ?>">Quay lại trang chủ</a>
        </div>
    </div>
</body>
</html>
