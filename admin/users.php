<?php
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    
    // Check not deleting self
    if ($del_id == $_SESSION['user_id']) {
        $error = "Bạn không thể xóa chính tài khoản đang đăng nhập.";
    } else {
        // Fetch role of the user to be deleted
        $stmtRole = $conn->prepare("SELECT role_id FROM users WHERE id = :id");
        $stmtRole->execute(['id' => $del_id]);
        $delUserRole = $stmtRole->fetchColumn();
        
        if ($_SESSION['role_id'] != 1) {
            $error = "Chỉ có Root Admin mới có quyền xóa người dùng.";
        } else {
            $stmtDel = $conn->prepare("DELETE FROM users WHERE id = :id");
            if ($stmtDel->execute(['id' => $del_id])) {
                log_admin_action($conn, $_SESSION['user_id'], 'Delete User', "Xóa người dùng ID $del_id");
                $success = "Đã xóa người dùng thành công.";
            } else {
                $error = "Không thể xóa người dùng.";
            }
        }
    }
}

$tab = isset($_GET['tab']) && in_array($_GET['tab'], ['root', 'staff', 'user']) ? $_GET['tab'] : 'user';
if ($tab == 'root') {
    $role_id = 1;
} elseif ($tab == 'staff') {
    $role_id = 2;
} else {
    $role_id = 3;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query
$sql = "SELECT * FROM users WHERE role_id = :role_id";
$params = ['role_id' => $role_id];

if ($search !== '') {
    $sql .= " AND (full_name LIKE :search OR phone LIKE :search)";
    $params['search'] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h1 class="font-serif text-3xl font-bold">Quản Lý Người Dùng</h1>
        <p class="text-gray-500 mt-1">Quản trị viên và khách hàng.</p>
    </div>
    <?php if ($_SESSION['role_id'] == 1): ?>
    <a href="user_form.php" class="bg-brand-black text-white px-6 py-3 rounded hover:bg-brand-gold transition-colors text-sm uppercase tracking-widest font-medium">
        + Thêm Người Dùng
    </a>
    <?php endif; ?>
</div>

<?php if(isset($success)): ?>
    <div class="bg-green-100 text-green-700 p-4 rounded mb-6 text-sm flex items-center gap-3">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-4 rounded mb-6 text-sm flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<!-- Tabs & Search -->
<div class="bg-white p-4 rounded-t-2xl shadow-sm border border-gray-200 border-b-0 flex flex-col md:flex-row justify-between items-center gap-4">
    <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
        <a href="users.php?tab=user<?= $search ? '&search='.urlencode($search) : '' ?>" class="px-6 py-2 rounded-md text-sm font-medium transition-colors <?= $tab == 'user' ? 'bg-white text-brand-black shadow' : 'text-gray-500 hover:text-gray-700' ?>">Khách Hàng</a>
        <a href="users.php?tab=staff<?= $search ? '&search='.urlencode($search) : '' ?>" class="px-6 py-2 rounded-md text-sm font-medium transition-colors <?= $tab == 'staff' ? 'bg-white text-brand-black shadow' : 'text-gray-500 hover:text-gray-700' ?>">Nhân Viên</a>
        <a href="users.php?tab=root<?= $search ? '&search='.urlencode($search) : '' ?>" class="px-6 py-2 rounded-md text-sm font-medium transition-colors <?= $tab == 'root' ? 'bg-white text-brand-black shadow' : 'text-gray-500 hover:text-gray-700' ?>">Root</a>
    </div>

    <form method="GET" action="users.php" class="relative w-full md:w-72">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm theo tên hoặc SĐT..." class="w-full bg-gray-50 border border-gray-200 rounded-full pl-10 pr-4 py-2 text-sm focus:outline-none focus:border-brand-gold">
        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
    </form>
</div>

<div class="bg-white rounded-b-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm uppercase tracking-widest text-gray-500">
                    <th class="p-4 font-medium">ID</th>
                    <th class="p-4 font-medium">Họ & Tên</th>
                    <th class="p-4 font-medium">Số Điện Thoại</th>
                    <th class="p-4 font-medium">Email</th>
                    <th class="p-4 font-medium">Ngày Tạo</th>
                    <th class="p-4 font-medium text-right">Hành Động</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if(empty($users)): ?>
                    <tr><td colspan="6" class="p-6 text-center text-gray-500">Không tìm thấy người dùng nào.</td></tr>
                <?php else: ?>
                    <?php foreach($users as $u): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4 text-gray-500">#<?= $u['id'] ?></td>
                            <td class="p-4 font-medium flex items-center gap-3">
                                <img src="<?= htmlspecialchars(user_avatar_url($u['avatar_url'], $u['full_name'])) ?>" class="w-8 h-8 rounded-full border border-gray-200 object-cover bg-gray-200">
                                <span class="text-brand-black"><?= htmlspecialchars($u['full_name']) ?></span>
                            </td>
                            <td class="p-4 text-gray-600"><?= htmlspecialchars($u['phone']) ?></td>
                            <td class="p-4 text-gray-600"><?= htmlspecialchars($u['email'] ?: '-') ?></td>
                            <td class="p-4 text-gray-500"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                            <td class="p-4 text-right space-x-3">
                                <a href="user_detail.php?id=<?= $u['id'] ?>" class="text-blue-500 hover:text-blue-700 font-medium" title="Xem chi tiết">Chi tiết</a>
                                <?php 
                                $can_delete = false;
                                if ($u['id'] != $_SESSION['user_id'] && $_SESSION['role_id'] == 1) {
                                    $can_delete = true;
                                }
                                ?>
                                <?php if($can_delete): ?>
                                    <span class="text-gray-300">|</span>
                                    <a href="users.php?tab=<?= $tab ?>&delete_id=<?= $u['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này không? Hành động này sẽ xóa tất cả đơn hàng của họ.');" class="text-red-500 hover:text-red-700 font-medium" title="Xóa">Xóa</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
