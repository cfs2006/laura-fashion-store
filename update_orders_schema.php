<?php
require_once 'db_connect.php';

try {
    $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS voucher_id INT NULL;");
    $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS admin_note TEXT NULL;");
    $conn->exec("ALTER TABLE orders ADD CONSTRAINT fk_order_voucher FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE SET NULL;");
    echo "Cập nhật bảng orders thành công!";
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
