<?php
require_once 'includes/header.php';

// Handle order actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'], $_POST['admin_note'])) {
    $order_id = (int)$_POST['order_id'];
    $admin_note = trim($_POST['admin_note']);
    $action = $_POST['action'];

    if (empty($admin_note)) {
        $error = "Vui lòng nhập ghi chú bắt buộc.";
    } else {
        try {
            $conn->beginTransaction();

            $stmtCheck = $conn->prepare("SELECT status, voucher_id, user_id FROM orders WHERE id = :id FOR UPDATE");
            $stmtCheck->execute(['id' => $order_id]);
            $order = $stmtCheck->fetch();

            if ($order && $order['status'] === 'Chờ xác nhận') {
                $new_status = '';
                if ($action === 'prepare') {
                    $new_status = 'Thành công';
                } elseif ($action === 'cancel') {
                    $new_status = 'Cancelled';
                    
                    // Handle voucher refund
                    if ($order['voucher_id']) {
                        $voucher_id = $order['voucher_id'];
                        $stmtDel = $conn->prepare("DELETE FROM voucher_usage WHERE voucher_id = :voucher_id AND user_id = :user_id LIMIT 1");
                        $stmtDel->execute(['voucher_id' => $voucher_id, 'user_id' => $order['user_id']]);
                        
                        $stmtRestore = $conn->prepare("UPDATE vouchers SET current_uses = GREATEST(0, current_uses - 1) WHERE id = :voucher_id");
                        $stmtRestore->execute(['voucher_id' => $voucher_id]);
                        
                        $stmtReEnable = $conn->prepare("UPDATE vouchers SET is_active = 1 WHERE id = :voucher_id AND is_active = 0 AND (max_uses IS NULL OR current_uses < max_uses)");
                        $stmtReEnable->execute(['voucher_id' => $voucher_id]);
                    }
                }

                if ($new_status) {
                    $stmtUpdate = $conn->prepare("UPDATE orders SET status = :status, admin_note = :note WHERE id = :id");
                    $stmtUpdate->execute(['status' => $new_status, 'note' => $admin_note, 'id' => $order_id]);
                    $success = "Đã cập nhật trạng thái đơn hàng thành công!";
                    log_admin_action($conn, $_SESSION['user_id'], 'Update Order', "Cập nhật đơn hàng #$order_id thành $new_status");
                }
            } else {
                $error = "Đơn hàng không hợp lệ hoặc đã được xử lý.";
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}

// Fetch pending orders
$stmtPending = $conn->query("
    SELECT o.*, u.full_name, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.status = 'Chờ xác nhận' 
    ORDER BY o.created_at ASC
");
$pending_orders = $stmtPending->fetchAll();

// Fetch processed orders
$stmtProcessed = $conn->query("
    SELECT o.*, u.full_name, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.status != 'Chờ xác nhận' 
    ORDER BY o.created_at DESC 
    LIMIT 50
");
$processed_orders = $stmtProcessed->fetchAll();

// Function to fetch order items for modals
function getOrderItems($conn, $order_id) {
    $stmt = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id");
    $stmt->execute(['order_id' => $order_id]);
    return $stmt->fetchAll();
}
?>

<div class="mb-8">
    <h1 class="font-serif text-3xl font-bold">Quản Lý Đơn Hàng</h1>
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Cột Trái: Đơn hàng chờ xử lý -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 h-[800px] flex flex-col">
        <h2 class="font-serif text-xl font-bold mb-4 flex items-center justify-between">
            <span>Đơn Hàng Mới (Chờ xác nhận)</span>
            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?= count($pending_orders) ?></span>
        </h2>
        
        <div class="overflow-y-auto flex-1 pr-2 space-y-4">
            <?php if(empty($pending_orders)): ?>
                <div class="text-center text-gray-500 py-10">Không có đơn hàng mới nào.</div>
            <?php else: ?>
                <?php foreach($pending_orders as $order): ?>
                    <div class="border border-gray-200 rounded p-4 hover:border-brand-gold cursor-pointer transition-colors" onclick="openOrderModal(<?= $order['id'] ?>)">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="font-bold text-lg">#<?= $order['id'] ?></span>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($order['full_name']) ?> - <?= htmlspecialchars($order['phone']) ?></p>
                            </div>
                            <span class="text-brand-gold font-bold"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</span>
                        </div>
                        <div class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                    </div>

                    <!-- Modal cho từng đơn hàng -->
                    <div id="modal-<?= $order['id'] ?>" class="hidden fixed inset-0 z-50 bg-black/50 items-center justify-center p-4">
                        <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" onclick="event.stopPropagation()">
                            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="font-serif text-2xl font-bold">Chi tiết Đơn hàng #<?= $order['id'] ?></h3>
                                <button onclick="closeOrderModal(<?= $order['id'] ?>)" class="text-gray-400 hover:text-gray-700">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            <div class="p-6 overflow-y-auto flex-1">
                                <div class="mb-6">
                                    <p class="font-bold">Khách hàng: <span class="font-normal"><?= htmlspecialchars($order['full_name']) ?> (<?= htmlspecialchars($order['phone']) ?>)</span></p>
                                    <p class="font-bold mt-2">Tổng tiền: <span class="font-normal text-brand-gold text-lg"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</span></p>
                                    <p class="font-bold mt-2">Phương thức: <span class="font-normal"><?= htmlspecialchars($order['payment_method']) ?></span></p>
                                </div>
                                
                                <h4 class="font-bold border-b pb-2 mb-4">Sản phẩm:</h4>
                                <div class="space-y-3 mb-6">
                                    <?php foreach(getOrderItems($conn, $order['id']) as $item): ?>
                                        <div class="flex justify-between text-sm">
                                            <span><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</span>
                                            <span><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <form method="POST" id="form-<?= $order['id'] ?>">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="action" id="action-<?= $order['id'] ?>" value="">
                                    <label class="block font-bold text-sm mb-2 text-gray-700">Ghi chú xử lý (Bắt buộc) *</label>
                                    <textarea name="admin_note" id="note-<?= $order['id'] ?>" required class="w-full border border-gray-300 rounded p-3 text-sm h-24 focus:outline-none focus:border-brand-gold" placeholder="Ghi chú về việc chuẩn bị hoặc lý do hủy..."></textarea>
                                </form>
                            </div>
                            <div class="p-6 border-t border-gray-200 bg-gray-50 flex gap-4">
                                <button type="button" onclick="submitOrder(<?= $order['id'] ?>, 'prepare')" class="bg-brand-black text-white px-6 py-2 text-sm font-medium rounded hover:bg-brand-gold transition-colors">Chuẩn bị đơn</button>
                                <button type="button" onclick="submitOrder(<?= $order['id'] ?>, 'cancel')" class="bg-red-50 text-red-600 px-6 py-2 text-sm font-medium rounded border border-red-200 hover:bg-red-100 transition-colors">Hủy đơn</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cột Phải: Đơn hàng đã xử lý -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 h-[800px] flex flex-col">
        <h2 class="font-serif text-xl font-bold mb-4">Đơn Hàng Đã Xử Lý</h2>
        
        <div class="overflow-y-auto flex-1 pr-2 space-y-4">
            <?php if(empty($processed_orders)): ?>
                <div class="text-center text-gray-500 py-10">Chưa có đơn hàng nào được xử lý.</div>
            <?php else: ?>
                <?php foreach($processed_orders as $order): ?>
                    <div class="border border-gray-200 bg-gray-50 rounded p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="font-bold text-lg text-gray-700">#<?= $order['id'] ?></span>
                                <span class="ml-2 text-xs px-2 py-1 rounded-full <?= $order['status'] === 'Cancelled' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' ?>"><?= htmlspecialchars($order['status']) ?></span>
                            </div>
                            <span class="text-gray-600 font-medium"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Khách: <?= htmlspecialchars($order['full_name']) ?></p>
                        <?php if($order['admin_note']): ?>
                            <p class="text-sm text-gray-500 italic bg-white p-2 border border-gray-100 rounded">Note: <?= htmlspecialchars($order['admin_note']) ?></p>
                        <?php endif; ?>
                        <div class="text-xs text-gray-400 mt-2 text-right"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openOrderModal(id) {
    const modal = document.getElementById('modal-' + id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeOrderModal(id) {
    const modal = document.getElementById('modal-' + id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function submitOrder(id, action) {
    const note = document.getElementById('note-' + id).value.trim();
    if (!note) {
        alert('Vui lòng nhập ghi chú!');
        return;
    }
    
    let msg = action === 'prepare' ? 'Xác nhận chuẩn bị đơn hàng này?' : 'Bạn chắc chắn muốn HỦY đơn hàng này?';
    if(confirm(msg)) {
        document.getElementById('action-' + id).value = action;
        document.getElementById('form-' + id).submit();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
