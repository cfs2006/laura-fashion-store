<?php
require_once 'db_connect.php';
require_once 'includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'orders.php';
    header('Location: auth.php');
    exit();
}

$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare('
    SELECT id, total_amount, payment_method, status, created_at
    FROM orders
    WHERE user_id = :user_id
    ORDER BY created_at DESC, id DESC
');
$stmt->execute(['user_id' => $userId]);
$orders = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto py-12 px-6 min-h-[70vh]">
    <div class="mb-10">
        <p class="text-sm uppercase tracking-[0.3em] text-gray-500 mb-3">Tài khoản</p>
        <h1 class="font-serif text-4xl md:text-5xl font-bold">Đơn hàng của tôi</h1>
    </div>

    <?php if (empty($orders)): ?>
        <div class="bg-white border border-gray-200 shadow-sm rounded-3xl p-10 text-center">
            <div class="text-5xl text-gray-300 mb-4"><i class="fas fa-receipt"></i></div>
            <p class="text-gray-600 mb-6">Bạn chưa có đơn hàng nào.</p>
            <a href="shop.php" class="inline-flex items-center justify-center rounded-full bg-brand-black px-8 py-3 text-sm uppercase tracking-[0.25em] text-white hover:bg-brand-gold transition-colors">
                Mua sắm ngay
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($orders as $order): ?>
                <?php
                    $itemsStmt = $conn->prepare('
                        SELECT oi.quantity, oi.price, p.name, p.image_url
                        FROM order_items oi
                        INNER JOIN products p ON p.id = oi.product_id
                        WHERE oi.order_id = :order_id
                    ');
                    $itemsStmt->execute(['order_id' => (int) $order['id']]);
                    $items = $itemsStmt->fetchAll();
                ?>
                <div class="bg-white border border-gray-200 shadow-sm rounded-3xl overflow-hidden">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between p-6 border-b border-gray-100">
                        <div>
                            <p class="text-sm uppercase tracking-[0.25em] text-gray-400 mb-2">Mã đơn #<?= (int) $order['id'] ?></p>
                            <h2 class="font-serif text-2xl font-bold"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</h2>
                        </div>
                        <div class="flex flex-wrap gap-3 text-sm items-center">
                            <span class="rounded-full bg-gray-100 px-4 py-2 text-gray-700"><?= htmlspecialchars($order['payment_method']) ?></span>
                            <span id="status-badge-<?= (int)$order['id'] ?>" class="rounded-full px-4 py-2 text-gray-700 <?= $order['status'] === 'Chờ xác nhận' ? 'bg-brand-beige' : ($order['status'] === 'Cancelled' ? 'bg-red-100 text-red-600' : 'bg-gray-100') ?>"><?= htmlspecialchars($order['status']) ?></span>
                            <span class="rounded-full bg-gray-100 px-4 py-2 text-gray-700"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                            
                            <?php if ($order['status'] === 'Chờ xác nhận'): ?>
                                <button type="button" onclick="cancelOrder(<?= (int)$order['id'] ?>)" id="btn-cancel-<?= (int)$order['id'] ?>" class="ml-2 px-4 py-2 text-red-600 bg-red-50 hover:bg-red-100 rounded-full transition-colors font-medium">
                                    Hủy đơn
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Phím thông báo hủy đơn thành công -->
                    <div id="cancel-msg-<?= (int)$order['id'] ?>" class="hidden px-6 py-4 bg-green-50 text-green-700 border-b border-green-100 font-medium"></div>
                    <div class="p-6">
                        <div class="grid gap-4">
                            <?php foreach ($items as $item): ?>
                                <div class="flex gap-4 rounded-2xl border border-gray-100 p-4 hover:border-gray-300 transition-colors">
                                    <img
                                        src="<?= htmlspecialchars(asset_image_url($item['image_url'])) ?>"
                                        alt="<?= htmlspecialchars($item['name']) ?>"
                                        class="w-20 h-24 sm:w-24 sm:h-28 object-cover rounded-xl bg-gray-100 shrink-0"
                                    >
                                    <div class="min-w-0 flex-1 flex flex-col">
                                        <h3 class="font-serif text-base sm:text-lg font-bold mb-1 leading-snug"><?= htmlspecialchars($item['name']) ?></h3>
                                        <p class="text-xs sm:text-sm text-gray-500 mb-1 font-sans">Số lượng: <?= (int) $item['quantity'] ?></p>
                                        <p class="text-xs sm:text-sm text-gray-500 font-sans">Đơn giá: <?= number_format($item['price'], 0, ',', '.') ?>đ</p>
                                        <div class="mt-auto pt-2 text-right font-bold text-brand-gold text-base sm:text-lg font-sans">
                                            <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function cancelOrder(orderId) {
    if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này? Việc này không thể hoàn tác.')) {
        return;
    }

    const btn = document.getElementById('btn-cancel-' + orderId);
    if (btn) {
        btn.disabled = true;
        btn.innerText = 'Đang hủy...';
    }

    fetch('ajax/cancel_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const msgEl = document.getElementById('cancel-msg-' + orderId);
            msgEl.innerText = data.message;
            msgEl.classList.remove('hidden');

            const badge = document.getElementById('status-badge-' + orderId);
            badge.innerText = 'Cancelled';
            badge.className = 'rounded-full px-4 py-2 text-sm bg-red-100 text-red-600';
            
            if (btn) btn.remove();
        } else {
            alert(data.message || 'Có lỗi xảy ra');
            if (btn) {
                btn.disabled = false;
                btn.innerText = 'Hủy đơn';
            }
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối. Vui lòng thử lại sau.');
        if (btn) {
            btn.disabled = false;
            btn.innerText = 'Hủy đơn';
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
