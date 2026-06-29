<?php
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SESSION['role_id'] != 1) {
    echo "<div class='p-8 text-center text-red-500'>Bạn không có quyền thêm người dùng.</div>";
    require_once 'includes/footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = (int)($_POST['role_id'] ?? 3);
    
    // Prevent staff from assigning root/staff roles
    if ($_SESSION['role_id'] != 1 && in_array($role_id, [1, 2])) {
        $role_id = 3;
    }

    if (empty($full_name) || empty($phone) || empty($password)) {
        $error = "Vui lòng nhập Họ tên, Số điện thoại và Mật khẩu.";
    } elseif (strlen($password) < 3) {
        $error = "Mật khẩu phải từ 3 ký tự trở lên.";
    } else {
        // Kiểm tra trùng SĐT / Email
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE phone = :p OR (email = :e AND email != '')");
        $stmtCheck->execute(['p' => $phone, 'e' => $email]);
        if ($stmtCheck->rowCount() > 0) {
            $error = "Số điện thoại hoặc Email đã tồn tại trong hệ thống.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, phone, email, password, role_id) VALUES (:fn, :p, :e, :pw, :r)");
            if ($stmt->execute([
                'fn' => $full_name,
                'p' => $phone,
                'e' => $email ? $email : null,
                'pw' => $hashed,
                'r' => $role_id
            ])) {
                $new_id = $conn->lastInsertId();
                log_admin_action($conn, $_SESSION['user_id'], 'Add User', "Thêm người dùng mới ID $new_id ($full_name)");
                $success = "Đã tạo tài khoản thành công.";
            } else {
                $error = "Có lỗi xảy ra, không thể tạo tài khoản.";
            }
        }
    }
}
?>

<div class="mb-8 flex items-center gap-4">
    <a href="users.php" class="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-500 hover:text-brand-black hover:border-brand-black transition-colors">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-serif text-3xl font-bold">Thêm Người Dùng</h1>
    </div>
</div>

<?php if($success): ?>
    <div class="bg-green-100 text-green-700 p-4 rounded mb-6 text-sm flex items-center gap-3">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>
<?php if($error): ?>
    <div class="bg-red-100 text-red-700 p-4 rounded mb-6 text-sm flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 max-w-2xl">
    <form method="POST" class="space-y-6">
        <?php if ($_SESSION['role_id'] == 1): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Vai trò (Phân quyền) *</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="role_id" value="3" checked class="w-4 h-4 text-brand-gold accent-brand-gold">
                    <span class="text-sm">Khách hàng</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="role_id" value="2" class="w-4 h-4 text-brand-gold accent-brand-gold">
                    <span class="text-sm">Nhân viên</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="role_id" value="1" class="w-4 h-4 text-brand-gold accent-brand-gold">
                    <span class="text-sm">Root</span>
                </label>
            </div>
        </div>
        <?php else: ?>
            <input type="hidden" name="role_id" value="3">
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Họ và tên *</label>
            <input type="text" name="full_name" required class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:border-brand-gold">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại *</label>
            <input type="text" name="phone" required class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:border-brand-gold">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="text" name="email" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:border-brand-gold">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu *</label>
            <input type="password" name="password" required minlength="3" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:border-brand-gold">
        </div>

        <div class="pt-6 border-t border-gray-200 flex justify-end">
            <button type="submit" class="bg-brand-black text-white px-8 py-3 uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors font-medium rounded">
                Tạo Tài Khoản
            </button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
