<?php

function asset_image_url(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $absolutePath = __DIR__ . '/../' . ltrim($path, '/\\');
    if (is_file($absolutePath)) {
        return $path . '?v=' . filemtime($absolutePath);
    }

    return $path;
}

function user_avatar_fallback_url(string $name): string
{
    $name = trim($name);
    $parts = preg_split('/\s+/u', $name) ?: [];
    $initials = '';

    if (!empty($parts[0])) {
        $first = function_exists('mb_substr') ? mb_substr($parts[0], 0, 1) : substr($parts[0], 0, 1);
        $initials .= function_exists('mb_strtoupper') ? mb_strtoupper($first) : strtoupper($first);
    }
    if (!empty($parts[1])) {
        $second = function_exists('mb_substr') ? mb_substr($parts[1], 0, 1) : substr($parts[1], 0, 1);
        $initials .= function_exists('mb_strtoupper') ? mb_strtoupper($second) : strtoupper($second);
    }

    if ($initials === '') {
        $initials = 'U';
    }

    $svg = sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><rect width="200" height="200" rx="100" fill="#0a0a0a"/><text x="50%%" y="54%%" text-anchor="middle" dominant-baseline="middle" fill="#D4AF37" font-family="Montserrat,Arial,sans-serif" font-size="72" font-weight="600">%s</text></svg>',
        htmlspecialchars($initials, ENT_QUOTES, 'UTF-8')
    );

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function user_avatar_url(?string $avatarPath, string $name = ''): string
{
    $avatarPath = trim((string) $avatarPath);

    if ($avatarPath !== '') {
        $resolved = asset_image_url($avatarPath);
        if ($resolved !== '') {
            return $resolved;
        }
    }

    return user_avatar_fallback_url($name);
}

function ensure_user_profile_schema(PDO $conn): void
{
    static $ran = false;
    if ($ran) {
        return;
    }
    $ran = true;

    $columns = [
        'avatar_url' => "ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) NULL AFTER password",
        'address' => "ALTER TABLE users ADD COLUMN address VARCHAR(255) NULL AFTER avatar_url",
        'birth_date' => "ALTER TABLE users ADD COLUMN birth_date DATE NULL AFTER address",
        'gender' => "ALTER TABLE users ADD COLUMN gender VARCHAR(20) NULL AFTER birth_date",
        'updated_at' => "ALTER TABLE users ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
    ];

    foreach ($columns as $column => $alterSql) {
        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = :column
        ");
        $stmt->execute(['column' => $column]);

        if ((int) $stmt->fetchColumn() === 0) {
            $conn->exec($alterSql);
        }
    }
}

function log_admin_action(PDO $conn, int $admin_id, string $action, string $details = ''): void
{
    try {
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (:admin_id, :action, :details)");
        $stmt->execute([
            'admin_id' => $admin_id,
            'action' => $action,
            'details' => $details
        ]);
    } catch (Throwable $e) {
        // Silently ignore log insertion errors to prevent breaking the main flow
    }
}
