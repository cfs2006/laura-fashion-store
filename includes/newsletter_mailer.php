<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';

function newsletter_local_mail_config(): array
{
    $configPath = __DIR__ . '/../config/newsletter_mail.php';
    if (is_file($configPath)) {
        $config = require $configPath;
        return is_array($config) ? $config : [];
    }

    return [];
}

function newsletter_detect_base_url(): string
{
    $envBaseUrl = trim((string) getenv('NEWSLETTER_BASE_URL'));
    if ($envBaseUrl !== '') {
        return rtrim($envBaseUrl, '/');
    }

    $scheme = 'http';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $scheme = 'https';
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $rootPath = '';

    if ($scriptName !== '') {
        $ajaxPos = strpos($scriptName, '/ajax/');
        if ($ajaxPos !== false) {
            $rootPath = substr($scriptName, 0, $ajaxPos);
        } else {
            $rootPath = dirname($scriptName);
        }
    }

    if ($rootPath === '.' || $rootPath === '/' || $rootPath === '\\') {
        $rootPath = '';
    }

    return rtrim($scheme . '://' . $host . $rootPath, '/');
}

function newsletter_mail_config(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $baseUrl = newsletter_detect_base_url();
    $localConfig = newsletter_local_mail_config();
    $defaultSenderEmail = $localConfig['smtp_username'] ?? 'your_email@gmail.com';
    $defaultFromEmail = $localConfig['from_email'] ?? $defaultSenderEmail;
    $defaultReplyToEmail = $localConfig['reply_to_email'] ?? $defaultFromEmail;

    $config = [
        'site_name' => trim((string) getenv('NEWSLETTER_SITE_NAME')) ?: "L'AURA",
        'base_url' => $baseUrl,
        'follow_url' => trim((string) getenv('NEWSLETTER_FOLLOW_URL')) ?: $baseUrl,
        'from_email' => trim((string) getenv('NEWSLETTER_FROM_EMAIL')) ?: ($localConfig['from_email'] ?? $defaultFromEmail),
        'from_name' => trim((string) getenv('NEWSLETTER_FROM_NAME')) ?: ($localConfig['from_name'] ?? "L'AURA"),
        'reply_to_email' => trim((string) getenv('NEWSLETTER_REPLY_TO_EMAIL')) ?: $defaultReplyToEmail,
        'reply_to_name' => trim((string) getenv('NEWSLETTER_REPLY_TO_NAME')) ?: ($localConfig['reply_to_name'] ?? "L'AURA"),
        'smtp_host' => trim((string) getenv('NEWSLETTER_SMTP_HOST')) ?: ($localConfig['smtp_host'] ?? 'smtp.gmail.com'),
        'smtp_username' => trim((string) getenv('NEWSLETTER_SMTP_USERNAME')) ?: $defaultSenderEmail,
        'smtp_password' => (string) getenv('NEWSLETTER_SMTP_PASSWORD') ?: ($localConfig['smtp_password'] ?? ''),
        'smtp_port' => (int) (getenv('NEWSLETTER_SMTP_PORT') ?: ($localConfig['smtp_port'] ?? 587)),
        'smtp_secure' => trim((string) getenv('NEWSLETTER_SMTP_SECURE')) ?: ($localConfig['smtp_secure'] ?? PHPMailer::ENCRYPTION_STARTTLS),
        'unsubscribe_secret' => trim((string) getenv('NEWSLETTER_UNSUBSCRIBE_SECRET')) ?: ($localConfig['unsubscribe_secret'] ?? 'change-this-secret'),
        'smtp_auth' => filter_var(getenv('NEWSLETTER_SMTP_AUTH') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    ];

    return $config;
}

function newsletter_unsubscribe_token(string $email): string
{
    $config = newsletter_mail_config();
    return hash_hmac('sha256', strtolower(trim($email)), $config['unsubscribe_secret']);
}

function newsletter_unsubscribe_url(string $email): string
{
    $config = newsletter_mail_config();
    return $config['base_url'] . '/unsubscribe_newsletter.php?email=' . rawurlencode(trim($email)) . '&token=' . rawurlencode(newsletter_unsubscribe_token($email));
}

function newsletter_build_email_content(string $email): array
{
    $config = newsletter_mail_config();
    $unsubscribeUrl = newsletter_unsubscribe_url($email);
    $safeSiteName = htmlspecialchars($config['site_name'], ENT_QUOTES, 'UTF-8');

    $subject = "Bạn đã đăng ký nhận tin từ " . $config['site_name'];

    $html = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f0;font-family:Arial,Helvetica,sans-serif;color:#111111;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        Bạn đã được thêm vào danh sách nhận email của {$safeSiteName}.
    </div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f5f5f0;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background:#ffffff;border:1px solid #e5e7eb;border-radius:24px;overflow:hidden;">
                    <tr>
                        <td style="background:#0a0a0a;padding:28px 32px;text-align:center;">
                            <div style="font-size:28px;letter-spacing:0.28em;font-weight:700;color:#ffffff;">{$safeSiteName}</div>
                            <div style="margin-top:8px;font-size:12px;letter-spacing:0.28em;text-transform:uppercase;color:#d4af37;">Maison De Luxe</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 32px 12px 32px;">
                            <div style="font-size:14px;letter-spacing:0.18em;text-transform:uppercase;color:#6b7280;margin-bottom:18px;">Đăng ký thành công</div>
                            <h1 style="margin:0 0 18px 0;font-size:30px;line-height:1.25;color:#111111;font-family:Georgia,'Times New Roman',serif;">Bạn đã được thêm vào danh sách nhận tin</h1>
                            <p style="margin:0 0 14px 0;font-size:16px;line-height:1.75;color:#374151;">Xin chào,</p>
                            <p style="margin:0 0 14px 0;font-size:16px;line-height:1.75;color:#374151;">
                                Từ bây giờ, chúng tôi sẽ gửi cho bạn các cập nhật ngắn gọn về bộ sưu tập mới, thông tin nổi bật và thay đổi quan trọng từ {$safeSiteName}.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    $text = implode("\n", [
        "Cảm ơn bạn đã đăng ký bản tin {$config['site_name']}.",
        "",
        "Từ bây giờ bạn sẽ nhận được cập nhật về bộ sưu tập mới, ưu đãi độc quyền và các tin tức phong cách.",
    ]);

    return [
        'subject' => $subject,
        'html' => $html,
        'text' => $text,
        'unsubscribe_url' => $unsubscribeUrl,
    ];
}

function sendNewsletterWelcomeEmail(string $email): bool
{
    $config = newsletter_mail_config();
    $content = newsletter_build_email_content($email);

    if ($config['smtp_host'] === '' || $config['smtp_username'] === '') {
        error_log('[newsletter] SMTP is not configured. Email not sent to ' . $email);
        return false;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = $config['smtp_auth'];
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];
        $mail->Port = $config['smtp_port'];

        if (!empty($config['smtp_secure'])) {
            $mail->SMTPSecure = $config['smtp_secure'];
        }

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email);

        if ($config['reply_to_email'] !== '') {
            $mail->addReplyTo($config['reply_to_email'], $config['reply_to_name']);
        }

        $mail->isHTML(true);
        $mail->Subject = $content['subject'];
        $mail->Body = $content['html'];
        $mail->AltBody = $content['text'];

        $mail->addCustomHeader('List-Unsubscribe', '<' . $content['unsubscribe_url'] . '>');

        return $mail->send();
    } catch (Exception $e) {
        error_log('[newsletter] Mailer error for ' . $email . ': ' . $e->getMessage());
        return false;
    } catch (Throwable $e) {
        error_log('[newsletter] Unexpected error for ' . $email . ': ' . $e->getMessage());
        return false;
    }
}

