<?php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "Người dùng không tồn tại.";
    require_once 'includes/footer.php';
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role_id'] == 1 && isset($_POST['action']) && $_POST['action'] == 'edit_staff') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $birth_date = trim($_POST['birth_date'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($full_name) || empty($phone)) {
        $error = "Họ tên và Số điện thoại không được để trống.";
    } else {
        $sql = "UPDATE users SET full_name = :fn, phone = :p, email = :e, gender = :g, birth_date = :bd WHERE id = :id";
        $params = [
            'fn' => $full_name, 'p' => $phone, 'e' => $email ?: null,
            'g' => $gender ?: null, 'bd' => $birth_date ?: null, 'id' => $id
        ];
        if (!empty($password)) {
            $sql = "UPDATE users SET full_name = :fn, phone = :p, email = :e, gender = :g, birth_date = :bd, password = :pw WHERE id = :id";
            $params['pw'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $stmtUpdate = $conn->prepare($sql);
        if ($stmtUpdate->execute($params)) {
            $success = "Cập nhật thông tin nhân viên thành công.";
        } else {
            $error = "Có lỗi xảy ra, vui lòng thử lại.";
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Người dùng không tồn tại.";
    require_once 'includes/footer.php';
    exit();
}

$age_str = '';
if ($user['birth_date']) {
    $dob = new DateTime($user['birth_date']);
    $now = new DateTime();
    $age_str = " (" . $now->diff($dob)->y . " tuổi)";
}

// Fetch orders
$stmtOrders = $conn->prepare("SELECT * FROM orders WHERE user_id = :id ORDER BY created_at DESC");
$stmtOrders->execute(['id' => $id]);
$orders = $stmtOrders->fetchAll();

$orderItems = [];
if (!empty($orders)) {
    $orderIds = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $stmtItems = $conn->prepare("
        SELECT oi.*, p.name, p.image_url 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id IN ($placeholders)
    ");
    $stmtItems->execute($orderIds);
    $items = $stmtItems->fetchAll();
    foreach ($items as $item) {
        $orderItems[$item['order_id']][] = $item;
    }
}
?>

<div class="mb-8 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="users.php<?= $user['role_id']==1 ? '?tab=admin' : '' ?>" class="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-500 hover:text-brand-black hover:border-brand-black transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="font-serif text-3xl font-bold">Chi tiết Người dùng #<?= $user['id'] ?></h1>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- User Info Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-center">
            <img src="<?= htmlspecialchars(user_avatar_url($user['avatar_url'], $user['full_name'])) ?>" class="w-24 h-24 rounded-full border border-gray-200 object-cover mx-auto bg-gray-200 mb-4">
            <h2 class="font-serif text-xl font-bold mb-1"><?= htmlspecialchars($user['full_name']) ?></h2>
            <p class="text-sm text-gray-500 mb-4">
                <?php if ($user['role_id'] == 1): ?>
                    <span class="bg-red-100 text-red-700 font-bold px-2 py-1 text-xs rounded uppercase tracking-widest">Root</span>
                <?php elseif ($user['role_id'] == 2): ?>
                    <span class="bg-brand-black text-white px-2 py-1 text-xs rounded uppercase tracking-widest">Nhân viên</span>
                <?php else: ?>
                    <span class="bg-brand-beige text-brand-black px-2 py-1 text-xs rounded uppercase tracking-widest">Khách hàng</span>
                <?php endif; ?>
            </p>
            
            <div class="text-left border-t border-gray-100 pt-4 mt-4 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Số điện thoại:</span>
                    <span class="font-medium"><?= htmlspecialchars($user['phone']) ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Email:</span>
                    <span class="font-medium"><?= htmlspecialchars($user['email'] ?: 'Chưa cập nhật') ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Giới tính:</span>
                    <span class="font-medium"><?= htmlspecialchars($user['gender'] ?: 'Chưa cập nhật') ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Ngày sinh:</span>
                    <span class="font-medium"><?= htmlspecialchars($user['birth_date'] ? date('d/m/Y', strtotime($user['birth_date'])) : 'Chưa cập nhật') ?><?= $age_str ?></span>
                </div>
                <?php if ($user['role_id'] == 3): ?>
                <div class="flex flex-col text-sm pt-2">
                    <span class="text-gray-500 mb-1">Địa chỉ giao hàng:</span>
                    <span class="font-medium"><?= htmlspecialchars($user['address'] ?: 'Chưa cập nhật') ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between text-sm pt-4 border-t border-gray-100">
                    <span class="text-gray-500">Ngày đăng ký:</span>
                    <span class="font-medium"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Order History -->
    <div class="lg:col-span-2">
        <?php if ($user['role_id'] == 3): ?>
        <!-- Order History for Customers -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h2 class="font-serif text-xl font-bold mb-6">Lịch Sử Mua Hàng</h2>
            
            <?php if(empty($orders)): ?>
                <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    Người dùng này chưa có đơn hàng nào.
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach($orders as $order): ?>
                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 flex flex-col md:flex-row md:items-center justify-between border-b border-gray-200">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Đơn hàng <span class="font-medium text-brand-black">#<?= $order['id'] ?></span></p>
                                    <p class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                                </div>
                                <div class="mt-2 md:mt-0 text-right">
                                    <p class="text-sm text-gray-500 mb-1">Tổng tiền: <span class="font-medium text-brand-gold"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</span></p>
                                    <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                        <?= $order['status'] == 'Pending' ? 'bg-yellow-100 text-yellow-700' : '' ?>
                                        <?= $order['status'] == 'Processing' ? 'bg-blue-100 text-blue-700' : '' ?>
                                        <?= $order['status'] == 'Shipped' ? 'bg-purple-100 text-purple-700' : '' ?>
                                        <?= $order['status'] == 'Delivered' ? 'bg-green-100 text-green-700' : '' ?>
                                        <?= $order['status'] == 'Cancelled' ? 'bg-red-100 text-red-700' : '' ?>
                                    ">
                                        <?= $order['status'] ?>
                                    </span>
                                </div>
                            </div>
                            <div class="p-6">
                                <?php if(isset($orderItems[$order['id']])): ?>
                                    <ul class="space-y-4">
                                        <?php foreach($orderItems[$order['id']] as $item): ?>
                                            <li class="flex items-center gap-4">
                                                <?php 
                                                    $imgSrc = $item['image_url'];
                                                    if ($imgSrc && !preg_match('#^https?://#i', $imgSrc)) {
                                                        $imgSrc = '../' . ltrim($imgSrc, '/');
                                                    }
                                                ?>
                                                <img src="<?= htmlspecialchars($imgSrc) ?>" class="w-16 h-16 object-cover rounded bg-gray-100 border border-gray-200">
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-medium"><?= htmlspecialchars($item['name']) ?></h4>
                                                    <p class="text-xs text-gray-500 mt-1">Số lượng: <?= $item['quantity'] ?> x <?= number_format($item['price'], 0, ',', '.') ?>đ</p>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php elseif ($_SESSION['role_id'] == 1 && in_array($user['role_id'], [1, 2])): ?>
        <!-- Edit Form for Root to edit Staff -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h2 class="font-serif text-xl font-bold mb-6">Cập Nhật Thông Tin Nhân Viên</h2>
            
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

            <form method="POST" action="user_detail.php?id=<?= $id ?>" class="space-y-4">
                <input type="hidden" name="action" value="edit_staff">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên *</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-brand-gold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại *</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-brand-gold">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-brand-gold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giới tính</label>
                        <select name="gender" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-brand-gold">
                            <option value="">-- Chọn giới tính --</option>
                            <option value="Nam" <?= $user['gender'] == 'Nam' ? 'selected' : '' ?>>Nam</option>
                            <option value="Nữ" <?= $user['gender'] == 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                            <option value="Khác" <?= $user['gender'] == 'Khác' ? 'selected' : '' ?>>Khác</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày sinh</label>
                        <input type="date" name="birth_date" value="<?= htmlspecialchars($user['birth_date']) ?>" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-brand-gold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới (Để trống nếu không đổi)</label>
                        <input type="password" name="password" minlength="3" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-brand-gold">
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-brand-black text-white px-6 py-2 uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors font-medium rounded">
                        Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
