<?php
require_once 'includes/header.php';

// Clear logs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_logs' && $_SESSION['role_id'] == 1) {
    $conn->exec("TRUNCATE TABLE contacts");
    $conn->exec("TRUNCATE TABLE newsletter_subscribers");
    $conn->exec("TRUNCATE TABLE admin_logs");
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

// --- Thống kê biểu đồ (Sản phẩm bán chạy) ---
$chartStmt = $conn->prepare("
    SELECT p.id, p.name, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10
");
$chartStmt->execute();
$chartData = $chartStmt->fetchAll();

$labels = [];
$data = [];
$productIds = [];
foreach ($chartData as $row) {
    $labels[] = $row['name'];
    $data[] = (int)$row['total_sold'];
    $productIds[] = $row['id'];
}

// --- Thống kê danh sách Liên hệ ---
$contactStmt = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
$contacts = $contactStmt->fetchAll();

// --- Thống kê Đăng ký bản tin ---
$newsStmt = $conn->query("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC LIMIT 5");
$newsletters = $newsStmt->fetchAll();
?>

<div class="mb-8 flex justify-between items-start">
    <div>
        <h1 class="font-serif text-3xl font-bold">Tổng Quan Hệ Thống</h1>
        <p class="text-gray-500 mt-1">Báo cáo hoạt động và dữ liệu khách hàng.</p>
    </div>
    <?php if ($_SESSION['role_id'] == 1): ?>
        <form method="POST" action="index.php" onsubmit="return confirm('Bạn có chắc chắn muốn xóa toàn bộ Liên Hệ, Đăng Ký Bản Tin và Lịch sử Hoạt Động (Logs)?');">
            <input type="hidden" name="action" value="clear_logs">
            <button type="submit" class="bg-red-50 text-red-600 px-4 py-2 rounded border border-red-200 hover:bg-red-100 transition-colors text-sm font-medium">Xóa test log</button>
        </form>
    <?php else: ?>
        <button type="button" disabled class="bg-gray-100 text-gray-400 px-4 py-2 rounded border border-gray-200 cursor-not-allowed text-sm font-medium" title="Chỉ Root mới được dùng tính năng này">Xóa test log</button>
    <?php endif; ?>
</div>

<!-- Chart Section -->
<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-8">
    <h2 class="font-serif text-xl font-bold mb-4">Sản Phẩm Bán Chạy Nhất</h2>
    <div class="w-full h-80 cursor-pointer" title="Nhấn vào cột để xem chi tiết người mua">
        <canvas id="salesChart"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Contacts Table -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
        <h2 class="font-serif text-xl font-bold mb-4">Tin Nhắn Mới (Liên Hệ)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 text-sm uppercase tracking-widest text-gray-500">
                        <th class="pb-3 font-medium">Tên</th>
                        <th class="pb-3 font-medium">Email</th>
                        <th class="pb-3 font-medium">Ngày Gửi</th>
                        <th class="pb-3 font-medium">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (empty($contacts)): ?>
                        <tr><td colspan="3" class="py-4 text-gray-500 text-center">Chưa có tin nhắn nào.</td></tr>
                    <?php else: ?>
                        <?php foreach($contacts as $contact): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 font-medium"><?= htmlspecialchars($contact['name']) ?></td>
                                <td class="py-3 text-gray-600"><?= htmlspecialchars($contact['email']) ?></td>
                                <td class="py-3 text-gray-500"><?= date('d/m/Y', strtotime($contact['created_at'])) ?></td>
                                <td class="py-3">
                                    <button type="button" class="view-contact-btn text-brand-gold hover:text-yellow-600 font-medium transition-colors" data-contact="<?= htmlspecialchars(json_encode($contact)) ?>">Xem</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Newsletter Table -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
        <h2 class="font-serif text-xl font-bold mb-4">Đăng Ký Bản Tin Mới</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 text-sm uppercase tracking-widest text-gray-500">
                        <th class="pb-3 font-medium">Email</th>
                        <th class="pb-3 font-medium">Ngày Đăng Ký</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (empty($newsletters)): ?>
                        <tr><td colspan="2" class="py-4 text-gray-500 text-center">Chưa có lượt đăng ký nào.</td></tr>
                    <?php else: ?>
                        <?php foreach($newsletters as $news): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-gray-600"><?= htmlspecialchars($news['email']) ?></td>
                                <td class="py-3 text-gray-500"><?= date('d/m/Y H:i', strtotime($news['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
        </div>
    </div>
</div>

<!-- Contact Modal -->
<div id="contact-modal" class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-lg w-full m-4 transform scale-95 transition-transform duration-300" id="contact-modal-content">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-serif text-2xl font-bold">Chi Tiết Tin Nhắn</h3>
            <button id="close-modal-btn" class="text-gray-400 hover:text-gray-800 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-widest mb-1">Người gửi</label>
                <div id="modal-name" class="text-gray-900 font-medium"></div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-widest mb-1">Email</label>
                <div id="modal-email" class="text-gray-900"></div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-widest mb-1">Ngày gửi</label>
                <div id="modal-date" class="text-gray-900 text-sm"></div>
            </div>
            <div class="pt-4 border-t border-gray-100">
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-widest mb-2">Nội dung</label>
                <div id="modal-message" class="text-gray-800 whitespace-pre-wrap bg-gray-50 p-4 rounded border border-gray-100"></div>
            </div>
        </div>
        <div class="mt-8 text-right">
            <button id="close-modal-bottom-btn" class="bg-gray-100 text-gray-800 px-6 py-2 rounded hover:bg-gray-200 transition-colors">Đóng</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartEl = document.getElementById('salesChart');
    if (chartEl) {
        const ctx = chartEl.getContext('2d');
        const labels = <?= json_encode($labels) ?>;
        const data = <?= json_encode($data) ?>;
        const productIds = <?= json_encode($productIds) ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Số lượng đã bán',
                    data: data,
                    backgroundColor: '#D4AF37', // brand-gold
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: (e, activeElements) => {
                    if (activeElements.length > 0) {
                        const dataIndex = activeElements[0].index;
                        const productId = productIds[dataIndex];
                        window.location.href = 'product_buyers.php?product_id=' + productId;
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // Contact Modal Logic
    const modal = document.getElementById('contact-modal');
    const modalContent = document.getElementById('contact-modal-content');
    const closeBtns = [document.getElementById('close-modal-btn'), document.getElementById('close-modal-bottom-btn')];
    
    document.querySelectorAll('.view-contact-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.getAttribute('data-contact'));
            document.getElementById('modal-name').textContent = data.name;
            document.getElementById('modal-email').textContent = data.email;
            document.getElementById('modal-message').textContent = data.message;
            document.getElementById('modal-date').textContent = new Date(data.created_at).toLocaleString('vi-VN');
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalContent.classList.remove('scale-95');
        });
    });

    function closeModal() {
        modal.classList.add('opacity-0', 'pointer-events-none');
        modalContent.classList.add('scale-95');
    }

    closeBtns.forEach(btn => btn.addEventListener('click', closeModal));
    modal.addEventListener('click', function(e) {
        if(e.target === modal) closeModal();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
