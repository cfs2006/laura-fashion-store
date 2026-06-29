<?php
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    
    // Attempt to delete image file first (optional, but good practice)
    $stmtImg = $conn->prepare("SELECT image_url FROM products WHERE id = :id");
    $stmtImg->execute(['id' => $del_id]);
    $prod = $stmtImg->fetch();
    
    try {
        $stmtDel = $conn->prepare("DELETE FROM products WHERE id = :id");
        if ($stmtDel->execute(['id' => $del_id])) {
            if ($prod && !empty($prod['image_url'])) {
                $imgPath = __DIR__ . '/../../' . $prod['image_url'];
                if (is_file($imgPath)) {
                    @unlink($imgPath);
                }
            }
            log_admin_action($conn, $_SESSION['user_id'], 'Delete Product', "Xóa sản phẩm ID $del_id");
            $success = "Đã xóa sản phẩm thành công.";
        } else {
            $error = "Không thể xóa sản phẩm.";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $error = "Không thể xóa sản phẩm này vì đã có khách hàng đặt mua (dữ liệu đang tồn tại trong Lịch sử đơn hàng).";
        } else {
            $error = "Đã xảy ra lỗi: " . $e->getMessage();
        }
    }
}

// Fetch Products
$stmt = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="font-serif text-3xl font-bold">Quản Lý Sản Phẩm</h1>
        <p class="text-gray-500 mt-1">Thêm, sửa, xóa các sản phẩm trong cửa hàng.</p>
    </div>
    <?php if ($_SESSION['role_id'] == 1): ?>
    <a href="product_form.php" class="bg-brand-black text-white px-6 py-3 rounded hover:bg-brand-gold transition-colors text-sm uppercase tracking-widest font-medium">
        + Thêm Sản Phẩm Mới
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

<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm uppercase tracking-widest text-gray-500">
                    <th class="p-4 font-medium">ID</th>
                    <th class="p-4 font-medium">Hình Ảnh</th>
                    <th class="p-4 font-medium">Tên Sản Phẩm</th>
                    <th class="p-4 font-medium">Danh Mục</th>
                    <th class="p-4 font-medium">Giá</th>
                    <?php if ($_SESSION['role_id'] == 1): ?>
                    <th class="p-4 font-medium text-right">Hành Động</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if(empty($products)): ?>
                    <tr><td colspan="6" class="p-6 text-center text-gray-500">Chưa có sản phẩm nào.</td></tr>
                <?php else: ?>
                    <?php foreach($products as $p): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4 text-gray-500">#<?= $p['id'] ?></td>
                            <td class="p-4">
                                <?php 
                                    $imgSrc = $p['image_url'];
                                    if ($imgSrc && !preg_match('#^https?://#i', $imgSrc)) {
                                        $imgSrc = '../' . ltrim($imgSrc, '/');
                                    }
                                ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Img" 
                                     onerror="this.src='https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=100';"
                                     class="w-16 h-16 object-cover rounded bg-gray-100 border border-gray-200">
                            </td>
                            <td class="p-4 font-medium text-brand-black"><?= htmlspecialchars($p['name']) ?></td>
                            <td class="p-4"><span class="bg-brand-beige px-3 py-1 rounded-full text-xs text-gray-700"><?= htmlspecialchars($p['category_name']) ?></span></td>
                            <td class="p-4 text-brand-gold font-medium"><?= number_format($p['price'], 0, ',', '.') ?>đ</td>
                            <?php if ($_SESSION['role_id'] == 1): ?>
                            <td class="p-4 text-right space-x-2">
                                <a href="product_form.php?id=<?= $p['id'] ?>" class="text-blue-500 hover:text-blue-700" title="Sửa"><i class="fas fa-edit"></i></a>
                                <a href="products.php?delete_id=<?= $p['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');" class="text-red-500 hover:text-red-700" title="Xóa"><i class="fas fa-trash-alt"></i></a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
