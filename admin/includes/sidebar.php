<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Overlay -->
<div id="admin-sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

<!-- Sidebar -->
<aside id="admin-sidebar" class="w-64 bg-brand-black text-gray-300 flex flex-col transition-transform duration-300 fixed md:sticky top-0 h-screen z-50 transform -translate-x-full md:translate-x-0">
    <button id="admin-close-menu-btn" class="md:hidden absolute top-4 right-4 text-gray-400 hover:text-white">
        <i class="fas fa-times text-xl"></i>
    </button>
    <div class="h-16 flex items-center justify-center border-b border-gray-800 mt-2 md:mt-0">
        <a href="index.php" class="font-logo text-2xl text-white tracking-widest font-bold">L'AURA <span class="text-brand-gold font-sans">ADMIN</span></a>
    </div>
    
    <nav class="flex-1 py-8 px-4 space-y-2">
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $current_page == 'index.php' ? 'bg-brand-gold text-white' : 'hover:bg-gray-800 hover:text-white' ?>">
            <i class="fas fa-chart-pie w-5"></i>
            <span class="text-sm uppercase tracking-widest font-medium">Tổng Quan</span>
        </a>
        <?php
            $stmtPendingBadge = $conn->query("SELECT COUNT(id) FROM orders WHERE status = 'Chờ xác nhận'");
            $pendingCount = $stmtPendingBadge->fetchColumn();
        ?>
        <a href="new_orders.php" class="flex items-center justify-between px-4 py-3 rounded-lg transition-colors <?= $current_page == 'new_orders.php' ? 'bg-brand-gold text-white' : 'hover:bg-gray-800 hover:text-white' ?>">
            <div class="flex items-center gap-3">
                <i class="fas fa-shopping-bag w-5"></i>
                <span class="text-sm uppercase tracking-widest font-medium">Đơn Hàng Mới</span>
            </div>
            <?php if($pendingCount > 0): ?>
                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>
        <a href="products.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $current_page == 'products.php' || $current_page == 'product_form.php' ? 'bg-brand-gold text-white' : 'hover:bg-gray-800 hover:text-white' ?>">
            <i class="fas fa-box w-5"></i>
            <span class="text-sm uppercase tracking-widest font-medium">Sản Phẩm</span>
        </a>
        <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $current_page == 'users.php' || $current_page == 'user_detail.php' || $current_page == 'user_form.php' ? 'bg-brand-gold text-white' : 'hover:bg-gray-800 hover:text-white' ?>">
            <i class="fas fa-users w-5"></i>
            <span class="text-sm uppercase tracking-widest font-medium">Người Dùng</span>
        </a>
        <a href="vouchers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $current_page == 'vouchers.php' ? 'bg-brand-gold text-white' : 'hover:bg-gray-800 hover:text-white' ?>">
            <i class="fas fa-ticket-alt w-5"></i>
            <span class="text-sm uppercase tracking-widest font-medium">Tạo mã giảm giá</span>
        </a>
        <a href="logs.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $current_page == 'logs.php' ? 'bg-brand-gold text-white' : 'hover:bg-gray-800 hover:text-white' ?>">
            <i class="fas fa-history w-5"></i>
            <span class="text-sm uppercase tracking-widest font-medium">Nhật Ký (Logs)</span>
        </a>
    </nav>

    <div class="p-4 border-t border-gray-800 text-xs text-center text-gray-500">
        &copy; 2026 L'AURA
    </div>
</aside>
