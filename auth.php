<?php
session_start();
require_once 'db_connect.php';

$error = '';
$success = '';
$action_type = 'login';

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'register') {
        $action_type = 'register';
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']) ?: null;
        $password = $_POST['password'];

        if (empty($full_name) || empty($phone) || empty($password)) {
            $error = 'Vui lòng điền đầy đủ các thông tin bắt buộc.';
        } elseif (!preg_match('/^(0[3|5|7|8|9])+([0-9]{8})$/', $phone)) {
            $error = 'Số điện thoại không hợp lệ.';
        } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Định dạng email không hợp lệ.';
        } elseif (strlen($password) < 3) {
            $error = 'Mật khẩu phải có ít nhất 3 ký tự.';
        } else {
            // Check if phone or email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE phone = :phone OR (email = :email AND email IS NOT NULL)");
            $stmt->execute(['phone' => $phone, 'email' => $email]);
            if ($stmt->rowCount() > 0) {
                $error = 'Số điện thoại hoặc Email đã được sử dụng.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (full_name, phone, email, password) VALUES (:full_name, :phone, :email, :password)");
                try {
                    $stmt->execute([
                        'full_name' => $full_name,
                        'phone' => $phone,
                        'email' => $email,
                        'password' => $hashed_password
                    ]);
                    $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
                    $action_type = 'login'; // Show login form on success
                } catch(PDOException $e) {
                    $error = 'Đã có lỗi xảy ra: ' . $e->getMessage();
                }
            }
        }
    } elseif ($action == 'login') {
        $login_id = trim($_POST['login_id']); // Can be phone or email
        $password = $_POST['password'];

        if (empty($login_id) || empty($password)) {
            $error = 'Vui lòng điền số điện thoại/email và mật khẩu.';
        } else {
            $stmt = $conn->prepare("SELECT id, full_name, password, role_id, avatar_url FROM users WHERE phone = :login_id OR email = :login_id");
            $stmt->execute(['login_id' => $login_id]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['user_avatar'] = $user['avatar_url'] ?? null;
                
                if (in_array($user['role_id'], [1, 2])) {
                    require_once 'includes/helpers.php';
                    log_admin_action($conn, $user['id'], "Login", "Đăng nhập thành công.");
                    header("Location: admin/index.php");
                    exit();
                }

                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: $redirect");
                } else {
                    header("Location: index.php");
                }
                exit();

            } else {
                $error = 'Thông tin đăng nhập không chính xác.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto py-20 px-6 flex justify-center items-center min-h-[70vh]">
    <div class="w-full max-w-md bg-white p-8 rounded shadow-lg border border-gray-100 relative overflow-hidden">
        
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Form Đăng nhập -->
        <div id="login-form" class="transition-opacity duration-300 <?= $action_type == 'register' ? 'hidden opacity-0' : 'opacity-100' ?>">
            <h2 class="font-serif text-3xl font-bold text-center mb-8">Đăng Nhập</h2>
            <form method="POST" action="auth.php">
                <input type="hidden" name="action" value="login">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Số điện thoại hoặc Email *</label>
                    <input type="text" name="login_id" required class="w-full border-b border-gray-300 py-2 focus:outline-none focus:border-brand-gold transition-colors bg-transparent">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">Mật khẩu *</label>
                    <input type="password" name="password" required class="w-full border-b border-gray-300 py-2 focus:outline-none focus:border-brand-gold transition-colors bg-transparent">
                </div>
                <div class="flex justify-between items-center mb-6">
                    <a href="forgot_password.php" class="text-sm text-gray-500 hover:text-brand-gold">Quên mật khẩu?</a>
                </div>
                <button type="submit" class="w-full bg-brand-black text-white py-3 uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors">
                    Đăng Nhập
                </button>
            </form>
            <div class="mt-6 text-center text-sm">
                Chưa có tài khoản? <a href="#" id="show-register" class="font-bold hover:text-brand-gold">Đăng ký ngay</a>
            </div>
        </div>

        <!-- Form Đăng ký -->
        <div id="register-form" class="transition-opacity duration-300 <?= $action_type == 'register' ? 'opacity-100' : 'hidden opacity-0' ?>">
            <h2 class="font-serif text-3xl font-bold text-center mb-8">Đăng Ký</h2>
            <form method="POST" action="auth.php">
                <input type="hidden" name="action" value="register">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Họ và tên *</label>
                    <input type="text" name="full_name" required class="w-full border-b border-gray-300 py-2 focus:outline-none focus:border-brand-gold transition-colors bg-transparent">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Số điện thoại *</label>
                    <input type="text" name="phone" required class="w-full border-b border-gray-300 py-2 focus:outline-none focus:border-brand-gold transition-colors bg-transparent">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Email (Tùy chọn)</label>
                    <input type="email" name="email" class="w-full border-b border-gray-300 py-2 focus:outline-none focus:border-brand-gold transition-colors bg-transparent">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">Mật khẩu *</label>
                    <input type="password" name="password" required minlength="3" class="w-full border-b border-gray-300 py-2 focus:outline-none focus:border-brand-gold transition-colors bg-transparent">
                </div>
                <button type="submit" class="w-full bg-brand-black text-white py-3 uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors">
                    Đăng Ký
                </button>
            </form>
            <div class="mt-6 text-center text-sm">
                Đã có tài khoản? <a href="#" id="show-login" class="font-bold hover:text-brand-gold">Đăng nhập</a>
            </div>
        </div>

    </div>
</div>

<script>
    document.getElementById('show-register').addEventListener('click', function(e) {
        e.preventDefault();
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        
        loginForm.classList.add('hidden', 'opacity-0');
        loginForm.classList.remove('opacity-100');
        
        registerForm.classList.remove('hidden', 'opacity-0');
        registerForm.classList.add('opacity-100');
    });

    document.getElementById('show-login').addEventListener('click', function(e) {
        e.preventDefault();
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        
        registerForm.classList.add('hidden', 'opacity-0');
        registerForm.classList.remove('opacity-100');
        
        loginForm.classList.remove('hidden', 'opacity-0');
        loginForm.classList.add('opacity-100');
    });
</script>

<?php require_once 'includes/footer.php'; ?>
