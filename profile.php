<?php
require_once 'db_connect.php';
require_once 'includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'profile.php';
    header('Location: auth.php');
    exit();
}

$userId = (int) $_SESSION['user_id'];
$error = '';
if (isset($_SESSION['profile_error'])) {
    $error = $_SESSION['profile_error'];
    unset($_SESSION['profile_error']);
}
$success = '';

function profile_local_avatar_path(?string $avatarUrl): ?string
{
    $avatarUrl = trim((string) $avatarUrl);
    if ($avatarUrl === '') {
        return null;
    }

    if (preg_match('#^https?://#i', $avatarUrl)) {
        return null;
    }

    $relative = ltrim($avatarUrl, '/\\');
    if (str_starts_with($relative, 'assets/avatars/')) {
        return __DIR__ . '/' . $relative;
    }

    return null;
}

$stmt = $conn->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: auth.php');
    exit();
}

$formValues = [
    'full_name' => $user['full_name'] ?? '',
    'phone' => $user['phone'] ?? '',
    'email' => $user['email'] ?? '',
    'address' => $user['address'] ?? '',
    'birth_date' => $user['birth_date'] ?? '',
    'gender' => $user['gender'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save_profile';

    if ($action === 'remove_avatar') {
        $localAvatarPath = profile_local_avatar_path($user['avatar_url'] ?? null);
        if ($localAvatarPath && is_file($localAvatarPath)) {
            @unlink($localAvatarPath);
        }

        $stmt = $conn->prepare('UPDATE users SET avatar_url = NULL WHERE id = :id');
        $stmt->execute(['id' => $userId]);

        $_SESSION['user_avatar'] = null;
        $success = 'Đã xóa ảnh đại diện.';
    } else {
        $formValues['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
        $formValues['phone'] = trim((string) ($_POST['phone'] ?? ''));
        $formValues['email'] = trim((string) ($_POST['email'] ?? ''));
        $formValues['address'] = trim((string) ($_POST['address'] ?? ''));
        $formValues['birth_date'] = trim((string) ($_POST['birth_date'] ?? ''));
        $formValues['gender'] = trim((string) ($_POST['gender'] ?? ''));
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($formValues['full_name'] === '' || $formValues['phone'] === '' || $formValues['address'] === '') {
            $error = 'Vui lòng nhập họ tên, số điện thoại và địa chỉ giao hàng.';
        } elseif ($formValues['email'] !== '' && !filter_var($formValues['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không hợp lệ.';
        } elseif ($formValues['birth_date'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $formValues['birth_date'])) {
            $error = 'Ngày sinh không hợp lệ.';
        } elseif ($formValues['gender'] !== '' && !in_array($formValues['gender'], ['Nam', 'Nữ', 'Khác'], true)) {
            $error = 'Giới tính không hợp lệ.';
        } elseif (($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '') && (!$currentPassword || !$newPassword || !$confirmPassword)) {
            $error = 'Nếu muốn đổi mật khẩu, hãy điền đầy đủ mật khẩu hiện tại, mật khẩu mới và xác nhận mật khẩu.';
        } elseif ($newPassword !== '' && strlen($newPassword) < 3) {
            $error = 'Mật khẩu mới phải có ít nhất 3 ký tự.';
        } elseif ($newPassword !== '' && $newPassword !== $confirmPassword) {
            $error = 'Xác nhận mật khẩu không khớp.';
        } elseif (($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '') && !password_verify($currentPassword, $user['password'])) {
            $error = 'Mật khẩu hiện tại không đúng.';
        } else {
            $duplicateQuery = '
                SELECT id
                FROM users
                WHERE id <> :id
                  AND (phone = :phone';
            $duplicateParams = [
                'id' => $userId,
                'phone' => $formValues['phone'],
            ];

            if ($formValues['email'] !== '') {
                $duplicateQuery .= ' OR email = :email';
                $duplicateParams['email'] = $formValues['email'];
            }

            $duplicateQuery .= ')
                LIMIT 1';

            $stmt = $conn->prepare($duplicateQuery);
            $stmt->execute($duplicateParams);

            if ($stmt->fetch()) {
                $error = 'Số điện thoại hoặc email đã được sử dụng bởi tài khoản khác.';
            } else {
                $avatarUrl = $user['avatar_url'] ?? null;

                if (!empty($_FILES['avatar']['name'])) {
                    $avatar = $_FILES['avatar'];

                    if (!isset($avatar['error']) || $avatar['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Không thể tải ảnh lên.';
                    } elseif (($avatar['size'] ?? 0) > 2 * 1024 * 1024) {
                        $error = 'Ảnh đại diện không được lớn hơn 2MB.';
                    } else {
                        $imageInfo = @getimagesize($avatar['tmp_name']);
                        $mimeType = $imageInfo['mime'] ?? '';
                        $allowedMimeTypes = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/gif' => 'gif',
                            'image/webp' => 'webp',
                        ];

                        if (!isset($allowedMimeTypes[$mimeType])) {
                            $error = 'Ảnh đại diện phải là JPG, PNG, GIF hoặc WEBP.';
                        } else {
                            $uploadDir = __DIR__ . '/assets/avatars';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0775, true);
                            }

                            $newFileName = 'user_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowedMimeTypes[$mimeType];
                            $targetPath = $uploadDir . '/' . $newFileName;

                            if (!move_uploaded_file($avatar['tmp_name'], $targetPath)) {
                                $error = 'Không thể lưu ảnh đại diện.';
                            } else {
                                $oldAvatarPath = profile_local_avatar_path($user['avatar_url'] ?? null);
                                if ($oldAvatarPath && is_file($oldAvatarPath)) {
                                    @unlink($oldAvatarPath);
                                }

                                $avatarUrl = 'assets/avatars/' . $newFileName;
                            }
                        }
                    }
                }

                if ($error === '') {
                    $updateParams = [
                        'id' => $userId,
                        'full_name' => $formValues['full_name'],
                        'phone' => $formValues['phone'],
                        'email' => $formValues['email'] !== '' ? $formValues['email'] : null,
                        'address' => $formValues['address'] !== '' ? $formValues['address'] : null,
                        'birth_date' => $formValues['birth_date'] !== '' ? $formValues['birth_date'] : null,
                        'gender' => $formValues['gender'] !== '' ? $formValues['gender'] : null,
                        'avatar_url' => $avatarUrl !== '' ? $avatarUrl : null,
                    ];

                    $sql = '
                        UPDATE users
                        SET full_name = :full_name,
                            phone = :phone,
                            email = :email,
                            address = :address,
                            birth_date = :birth_date,
                            gender = :gender,
                            avatar_url = :avatar_url';

                    if ($newPassword !== '') {
                        $sql .= ', password = :password';
                        $updateParams['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                    }

                    $sql .= ' WHERE id = :id';

                    $stmt = $conn->prepare($sql);
                    $stmt->execute($updateParams);

                    $_SESSION['user_name'] = $formValues['full_name'];
                    $_SESSION['user_avatar'] = $avatarUrl;
                    $success = 'Đã cập nhật hồ sơ thành công.';

                    $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
                    $stmt->execute(['id' => $userId]);
                    $user = $stmt->fetch();

                    if (isset($_SESSION['redirect_after_profile'])) {
                        $redirect_url = $_SESSION['redirect_after_profile'];
                        unset($_SESSION['redirect_after_profile']);
                        header("Location: " . $redirect_url);
                        exit();
                    }
                }
            }
        }
    }
}

$avatarDisplayUrl = user_avatar_url($user['avatar_url'] ?? '', $user['full_name'] ?? 'User');
$hasLocalAvatar = trim((string) ($user['avatar_url'] ?? '')) !== '';
require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto py-12 px-6 min-h-[70vh]">
    <div class="mb-10">
        <p class="text-sm uppercase tracking-[0.3em] text-gray-500 mb-3">Tài khoản</p>
        <h1 class="font-serif text-4xl md:text-5xl font-bold">Hồ sơ của tôi</h1>
    </div>

    <?php if ($error): ?>
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="grid gap-8 lg:grid-cols-[360px_minmax(0,1fr)]">
        <div class="bg-white border border-gray-200 shadow-sm rounded-3xl p-6 lg:sticky lg:top-28 h-fit">
            <div class="flex flex-col items-center text-center">
                <div class="relative">
                    <img
                        id="avatar-preview"
                        src="<?= htmlspecialchars($avatarDisplayUrl) ?>"
                        alt="<?= htmlspecialchars($user['full_name']) ?>"
                        class="w-56 h-56 rounded-full object-cover border-8 border-brand-beige shadow-lg bg-brand-black"
                    >
                    <button
                        type="button"
                        id="avatar-pick-btn"
                        class="absolute bottom-3 right-3 w-11 h-11 rounded-full bg-brand-black text-white shadow-lg hover:bg-brand-gold transition-colors"
                        title="Chọn ảnh đại diện"
                    >
                        <i class="fas fa-camera"></i>
                    </button>
                </div>

                <div class="mt-6">
                    <h2 class="font-serif text-2xl font-bold"><?= htmlspecialchars($user['full_name']) ?></h2>
                    <p class="text-sm text-gray-500 mt-2"><?= htmlspecialchars($user['phone']) ?></p>
                    <?php if (!empty($user['email'])): ?>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-8 space-y-4">
                <input type="file" id="avatar-input" name="avatar" accept="image/*" form="profile-save-form" class="hidden">
                <label for="avatar-input" class="block w-full text-center cursor-pointer rounded-2xl border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-600 hover:border-brand-gold hover:text-brand-black transition-colors">
                    Chọn ảnh mới
                </label>
                <?php if ($hasLocalAvatar): ?>
                    <form method="POST" action="profile.php">
                        <input type="hidden" name="action" value="remove_avatar">
                        <button
                            type="submit"
                            class="w-full rounded-2xl border border-red-200 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors"
                            onclick="return confirm('Bạn chắc chắn muốn xóa ảnh đại diện?');"
                        >
                            Xóa ảnh đại diện
                        </button>
                    </form>
                <?php endif; ?>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Ảnh đại diện hỗ trợ JPG, PNG, GIF và WEBP, tối đa 2MB.
                </p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 shadow-sm rounded-3xl p-6 md:p-8">
            <form id="profile-save-form" method="POST" action="profile.php" enctype="multipart/form-data" class="space-y-8">
                <input type="hidden" name="action" value="save_profile">
                <div class="grid gap-6 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Tên của bạn*</span>
                        <input
                            type="text"
                            name="full_name"
                            value="<?= htmlspecialchars($formValues['full_name']) ?>"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition-colors focus:border-brand-gold"
                            required
                        >
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Số điện thoại*</span>
                        <input
                            type="text"
                            name="phone"
                            value="<?= htmlspecialchars($formValues['phone']) ?>"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition-colors focus:border-brand-gold"
                            required
                        >
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Email</span>
                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars($formValues['email']) ?>"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition-colors focus:border-brand-gold"
                            placeholder="Không bắt buộc"
                        >
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Ngày sinh</span>
                        <input
                            type="date"
                            name="birth_date"
                            value="<?= htmlspecialchars($formValues['birth_date']) ?>"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition-colors focus:border-brand-gold"
                        >
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Giới tính</span>
                        <select
                            name="gender"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition-colors focus:border-brand-gold"
                        >
                            <option value="" <?= $formValues['gender'] === '' ? 'selected' : '' ?>>Chưa chọn</option>
                            <option value="Nam" <?= $formValues['gender'] === 'Nam' ? 'selected' : '' ?>>Nam</option>
                            <option value="Nữ" <?= $formValues['gender'] === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                            <option value="Khác" <?= $formValues['gender'] === 'Khác' ? 'selected' : '' ?>>Khác</option>
                        </select>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Địa chỉ *</span>
                        <input
                            type="text"
                            name="address"
                            value="<?= htmlspecialchars($formValues['address']) ?>"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition-colors focus:border-brand-gold"
                            placeholder="Vui lòng nhập địa chỉ giao hàng"
                            required
                        >
                    </label>
                </div>

                <div class="border-t border-gray-200 pt-8">
                    <h2 class="font-serif text-2xl font-bold mb-5">Đổi mật khẩu</h2>
                    <div class="grid gap-6 md:grid-cols-3">
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-gray-700">Mật khẩu hiện tại</span>
                            <input
                                type="password"
                                name="current_password"
                                class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition-colors focus:border-brand-gold"
                                placeholder="Bỏ trống nếu không đổi"
                            >
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-gray-700">Mật khẩu mới</span>
                            <input
                            type="password"
                            name="new_password"
                            minlength="3"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition-colors focus:border-brand-gold"
                            placeholder="Mật khẩu mới (tối thiểu 3 ký tự)"
                        >
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-gray-700">Xác nhận mật khẩu</span>
                            <input
                                type="password"
                                name="confirm_password"
                                class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition-colors focus:border-brand-gold"
                                placeholder="Nhập lại mật khẩu mới"
                            >
                        </label>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-3 rounded-full bg-brand-black px-8 py-4 text-sm uppercase tracking-[0.25em] text-white transition-colors hover:bg-brand-gold">
                        <i class="fas fa-save"></i>
                        Lưu thay đổi
                    </button>
                    <p class="text-sm text-gray-500">
                        Bạn có thể bỏ trống email, ngày sinh, giới tính hoặc mật khẩu nếu không muốn thay đổi.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const avatarInput = document.getElementById('avatar-input');
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarPickBtn = document.getElementById('avatar-pick-btn');

    if (avatarInput && avatarPreview) {
        const openPicker = () => avatarInput.click();

        avatarPickBtn?.addEventListener('click', openPicker);

        avatarInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }

            const url = URL.createObjectURL(file);
            avatarPreview.src = url;
            avatarPreview.onload = () => URL.revokeObjectURL(url);
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>
