<?php
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    if ($_SESSION['role_id'] != 1) {
        $error = "Chỉ Root mới có quyền xóa mã.";
    } else {
        $stmtDel = $conn->prepare("DELETE FROM vouchers WHERE id = :id");
        if ($stmtDel->execute(['id' => (int)$_GET['delete_id']])) {
            $success = "Đã xóa mã giảm giá thành công.";
        }
    }
}

// Handle Toggle
if (isset($_GET['toggle_id'])) {
    if ($_SESSION['role_id'] != 1) {
        $error = "Chỉ Root mới có quyền sửa đổi.";
    } else {
        $stmtTog = $conn->prepare("UPDATE vouchers SET is_active = NOT is_active WHERE id = :id");
        if ($stmtTog->execute(['id' => (int)$_GET['toggle_id']])) {
            $success = "Đã thay đổi trạng thái mã.";
        }
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_SESSION['role_id'] != 1) {
        $error = "Chỉ Root mới có quyền thao tác.";
    } else {
        $name = trim($_POST['name']);
        $code = strtoupper(trim($_POST['code']));
        $discount_percent = (int)$_POST['discount_percent'];
        
        $max_uses_type = $_POST['max_uses_type'] ?? 'unlimited';
        $max_uses = null;
        if ($max_uses_type === 'specific' && isset($_POST['max_uses']) && (int)$_POST['max_uses'] > 0) {
            $max_uses = (int)$_POST['max_uses'];
        }

        if (strlen($code) !== 6) {
            $error = "Mã giảm giá phải có đúng 6 ký tự.";
        } elseif ($discount_percent < 1 || $discount_percent > 100) {
            $error = "Phần trăm giảm giá phải từ 1 đến 100.";
        } elseif (empty($name)) {
            $error = "Vui lòng nhập tên mã giảm giá.";
        } else {
            try {
                if ($_POST['action'] === 'create_voucher') {
                    $stmt = $conn->prepare("INSERT INTO vouchers (code, name, discount_percent, max_uses, is_active) VALUES (:code, :name, :dp, :mu, 1)");
                    $stmt->execute(['code' => $code, 'name' => $name, 'dp' => $discount_percent, 'mu' => $max_uses]);
                    log_admin_action($conn, $_SESSION['user_id'], 'Create Voucher', "Tạo mã: $code ($discount_percent%)");
                    $success = "Tạo mã giảm giá thành công.";
                } elseif ($_POST['action'] === 'edit_voucher' && isset($_POST['edit_id'])) {
                    $stmt = $conn->prepare("UPDATE vouchers SET code = :code, name = :name, discount_percent = :dp, max_uses = :mu WHERE id = :id");
                    $stmt->execute(['code' => $code, 'name' => $name, 'dp' => $discount_percent, 'mu' => $max_uses, 'id' => (int)$_POST['edit_id']]);
                    
                    $stmtCheck = $conn->prepare("SELECT current_uses, is_active FROM vouchers WHERE id = :id");
                    $stmtCheck->execute(['id' => (int)$_POST['edit_id']]);
                    $vData = $stmtCheck->fetch();
                    if ($vData && $vData['is_active'] == 0 && ($max_uses === null || $max_uses > $vData['current_uses'])) {
                        $stmtRe = $conn->prepare("UPDATE vouchers SET is_active = 1 WHERE id = :id");
                        $stmtRe->execute(['id' => (int)$_POST['edit_id']]);
                    }
                    
                    $success = "Cập nhật mã giảm giá thành công.";
                }
            } catch(PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Mã giảm giá '$code' đã tồn tại. Vui lòng chọn mã khác.";
                } else {
                    $error = "Lỗi hệ thống: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch edit voucher if any
$edit_voucher = null;
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("SELECT * FROM vouchers WHERE id = :id");
    $stmt->execute(['id' => (int)$_GET['edit_id']]);
    $edit_voucher = $stmt->fetch();
}

// Fetch existing vouchers
$stmt = $conn->query("SELECT * FROM vouchers ORDER BY created_at DESC");
$vouchers = $stmt->fetchAll();

// Fetch voucher usage history
$stmt = $conn->query("
    SELECT vu.id, vu.used_at, v.code, v.discount_percent, u.full_name, u.phone 
    FROM voucher_usage vu
    JOIN vouchers v ON vu.voucher_id = v.id
    JOIN users u ON vu.user_id = u.id
    ORDER BY vu.used_at DESC
");
$usage_history = $stmt->fetchAll();
?>

<div class="mb-8">
    <h1 class="font-serif text-3xl font-bold">Quản Lý Mã Giảm Giá</h1>
    <p class="text-gray-500 mt-1">Tạo, chỉnh sửa mã và theo dõi lịch sử sử dụng.</p>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1 space-y-8">
        <?php if ($_SESSION['role_id'] == 1): ?>
        <!-- Form -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
                <h2 class="font-serif text-xl font-bold"><?= $edit_voucher ? 'Sửa Mã Giảm Giá' : 'Tạo Mã Mới' ?></h2>
                <?php if($edit_voucher): ?>
                    <a href="vouchers.php" class="text-sm text-blue-500 hover:text-blue-700">Hủy sửa</a>
                <?php endif; ?>
            </div>
            <form method="POST" action="vouchers.php" class="space-y-4">
                <input type="hidden" name="action" value="<?= $edit_voucher ? 'edit_voucher' : 'create_voucher' ?>">
                <?php if($edit_voucher): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_voucher['id'] ?>">
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên Voucher</label>
                    <input type="text" name="name" value="<?= $edit_voucher ? htmlspecialchars($edit_voucher['name']) : '' ?>" required class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-brand-gold">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã (6 ký tự)</label>
                    <input type="text" name="code" value="<?= $edit_voucher ? htmlspecialchars($edit_voucher['code']) : '' ?>" required minlength="6" maxlength="6" pattern="[A-Za-z0-9]{6}" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-brand-gold uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phần trăm giảm (%)</label>
                    <input type="number" name="discount_percent" value="<?= $edit_voucher ? $edit_voucher['discount_percent'] : '' ?>" required min="1" max="100" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-brand-gold">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Số lượt sử dụng</label>
                    <div class="flex items-center gap-4 mb-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="max_uses_type" value="unlimited" <?= (!$edit_voucher || $edit_voucher['max_uses'] === null) ? 'checked' : '' ?> onchange="document.getElementById('max_uses_input').style.display='none'" class="text-brand-gold accent-brand-gold"> Không giới hạn
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="max_uses_type" value="specific" <?= ($edit_voucher && $edit_voucher['max_uses'] !== null) ? 'checked' : '' ?> onchange="document.getElementById('max_uses_input').style.display='block'" class="text-brand-gold accent-brand-gold"> Cụ thể
                        </label>
                    </div>
                    <input type="number" name="max_uses" id="max_uses_input" value="<?= $edit_voucher ? $edit_voucher['max_uses'] : '' ?>" min="1" placeholder="Nhập số lượt" style="display: <?= ($edit_voucher && $edit_voucher['max_uses'] !== null) ? 'block' : 'none' ?>" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-brand-gold mt-2">
                </div>
                <button type="submit" class="w-full bg-brand-black text-white px-4 py-2 rounded hover:bg-brand-gold transition-colors text-sm uppercase tracking-widest font-medium mt-2">
                    <?= $edit_voucher ? 'Lưu Thay Đổi' : 'Tạo Mã' ?>
                </button>
            </form>
        </div>
        <?php else: ?>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
            <i class="fas fa-lock text-gray-400 text-3xl mb-3"></i>
            <p class="text-gray-500 text-sm">Chỉ Root Admin mới có quyền tạo hoặc chỉnh sửa mã giảm giá.</p>
        </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <h2 class="font-serif text-xl font-bold mb-4 border-b border-gray-100 pb-3">Danh Sách Mã</h2>
            <?php if(empty($vouchers)): ?>
                <p class="text-gray-500 text-sm">Chưa có mã giảm giá nào.</p>
            <?php else: ?>
                <ul class="space-y-4">
                    <?php foreach($vouchers as $v): ?>
                        <li class="bg-gray-50 p-4 rounded-xl border <?= $v['is_active'] ? 'border-brand-gold' : 'border-gray-200 opacity-60' ?>">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-bold text-brand-gold text-lg"><?= htmlspecialchars($v['code']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($v['name']) ?></p>
                                </div>
                                <div class="bg-brand-black text-white px-3 py-1 rounded text-sm font-bold">
                                    -<?= $v['discount_percent'] ?>%
                                </div>
                            </div>
                            <div class="flex justify-between items-end mt-3 pt-3 border-t border-gray-200 text-sm">
                                <div>
                                    <?php if ($v['max_uses'] !== null && $v['current_uses'] >= $v['max_uses']): ?>
                                        <span class="text-red-500 font-medium text-xs uppercase tracking-widest"><i class="fas fa-times-circle"></i> Hết mã</span>
                                    <?php elseif ($v['is_active'] == 1): ?>
                                        <span class="text-green-500 font-medium text-xs uppercase tracking-widest"><i class="fas fa-check-circle"></i> Đang bật</span>
                                    <?php else: ?>
                                        <span class="text-gray-400 font-medium text-xs uppercase tracking-widest"><i class="fas fa-ban"></i> Đã tắt</span>
                                    <?php endif; ?>
                                    
                                    <div class="text-xs text-gray-500 mt-1 font-medium">
                                        Đã dùng: <?= $v['current_uses'] ?> / <?= $v['max_uses'] === null ? '∞' : $v['max_uses'] ?>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <?php if ($_SESSION['role_id'] == 1): ?>
                                    <a href="vouchers.php?toggle_id=<?= $v['id'] ?>" class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200" title="<?= $v['is_active'] ? 'Khóa mã' : 'Mở khóa' ?>">
                                        <i class="fas <?= $v['is_active'] ? 'fa-lock' : 'fa-unlock' ?>"></i>
                                    </a>
                                    <a href="vouchers.php?edit_id=<?= $v['id'] ?>" class="w-8 h-8 rounded bg-blue-50 flex items-center justify-center text-blue-500 hover:bg-blue-100" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="vouchers.php?delete_id=<?= $v['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa mã giảm giá này?');" class="w-8 h-8 rounded bg-red-50 flex items-center justify-center text-red-500 hover:bg-red-100" title="Xóa">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">Chỉ xem</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 h-full">
            <h2 class="font-serif text-xl font-bold mb-4 border-b border-gray-100 pb-3">Lịch Sử Sử Dụng</h2>
            <?php if(empty($usage_history)): ?>
                <p class="text-gray-500 text-sm">Chưa có ai sử dụng mã giảm giá.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase tracking-widest text-gray-500">
                                <th class="p-3 font-medium">Thời gian</th>
                                <th class="p-3 font-medium">Mã</th>
                                <th class="p-3 font-medium">Giảm</th>
                                <th class="p-3 font-medium">Khách hàng</th>
                                <th class="p-3 font-medium">SĐT</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php foreach($usage_history as $uh): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-gray-500"><?= date('d/m/Y H:i', strtotime($uh['used_at'])) ?></td>
                                    <td class="p-3 font-bold text-brand-gold"><?= htmlspecialchars($uh['code']) ?></td>
                                    <td class="p-3 text-gray-600">-<?= $uh['discount_percent'] ?>%</td>
                                    <td class="p-3 font-medium text-brand-black"><?= htmlspecialchars($uh['full_name']) ?></td>
                                    <td class="p-3 text-gray-600"><?= htmlspecialchars($uh['phone']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
