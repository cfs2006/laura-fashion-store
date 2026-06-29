<?php
session_start();
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($login_id) || empty($new_password) || empty($confirm_password)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } elseif (strlen($new_password) < 3) {
        $error = 'Mật khẩu phải có ít nhất 3 ký tự.';
    } else {
        $stmt = $conn->prepare("SELECT id, role_id FROM users WHERE phone = :login_id OR email = :login_id");
        $stmt->execute(['login_id' => $login_id]);
        $user = $stmt->fetch();

        if ($user) {
            if (in_array($user['role_id'], [1, 2])) {
                $error = 'Tài khoản quản trị không thể đặt lại mật khẩu tại đây. Vui lòng liên hệ Root.';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = :pw WHERE id = :id");
                $update->execute(['pw' => $hashed_password, 'id' => $user['id']]);
                $success = 'Đổi mật khẩu thành công. Đang chuyển hướng về trang đăng nhập...';
                echo "<script>setTimeout(function() { window.location.href = 'auth.php'; }, 2000);</script>";
            }
        } else {
            $error = 'Không tìm thấy tài khoản với số điện thoại/email này.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto py-20 px-6 flex justify-center items-center min-h-[70vh]">
    <div class="w-full max-w-md bg-white p-8 rounded shadow-lg border border-gray-100">
        <h2 class="text-2xl font-serif font-bold text-center mb-6">Quên Mật Khẩu</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại / Email *</label>
                <input type="text" name="login_id" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-brand-gold">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới *</label>
                <input type="password" name="new_password" required minlength="3" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-brand-gold">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới *</label>
                <input type="password" name="confirm_password" required minlength="3" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-brand-gold">
            </div>
            <button type="submit" class="w-full bg-brand-black text-white py-3 rounded uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors">
                Đặt Lại Mật Khẩu
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <a href="auth.php" class="text-brand-gold hover:text-brand-black transition-colors">Quay lại đăng nhập</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
