<?php
require_once 'includes/header.php';

if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    echo "<div class='p-8'><p class='text-red-500'>Không tìm thấy sản phẩm.</p></div>";
    require_once 'includes/footer.php';
    exit();
}

$product_id = (int)$_GET['product_id'];

// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='p-8'><p class='text-red-500'>Sản phẩm không tồn tại.</p></div>";
    require_once 'includes/footer.php';
    exit();
}

// Lấy danh sách những người đã mua sản phẩm này
$stmt = $conn->prepare("
    SELECT oi.quantity, o.created_at as order_date, u.id as user_id, u.full_name, u.phone, u.avatar_url
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN users u ON o.user_id = u.id
    WHERE oi.product_id = :product_id
    ORDER BY o.created_at DESC
");
$stmt->execute(['product_id' => $product_id]);
$buyers = $stmt->fetchAll();
?>

<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="font-serif text-3xl font-bold">Lịch Sử Mua Hàng</h1>
        <p class="text-gray-500 mt-1">Danh sách khách hàng đã mua: <span class="font-bold text-brand-black"><?= htmlspecialchars($product['name']) ?></span></p>
    </div>
    <a href="index.php" class="bg-gray-100 text-brand-black px-6 py-2 rounded hover:bg-gray-200 transition-colors text-sm uppercase tracking-widest font-medium">
        Quay Lại
    </a>
</div>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
    <div class="flex items-center gap-6 mb-6 pb-6 border-b border-gray-100">
        <?php 
            $imgSrc = $product['image_url'];
            if ($imgSrc && !preg_match('#^https?://#i', $imgSrc)) {
                $imgSrc = '../' . ltrim($imgSrc, '/');
            }
        ?>
        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-24 h-24 object-cover rounded bg-gray-100 border border-gray-200">
        <div>
            <h2 class="font-serif text-2xl font-bold"><?= htmlspecialchars($product['name']) ?></h2>
            <p class="text-brand-gold font-bold mt-1"><?= number_format($product['price'], 0, ',', '.') ?>đ</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm uppercase tracking-widest text-gray-500">
                    <th class="p-4 font-medium">Khách Hàng</th>
                    <th class="p-4 font-medium">SĐT</th>
                    <th class="p-4 font-medium">Số Lượng</th>
                    <th class="p-4 font-medium">Ngày Mua</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if(empty($buyers)): ?>
                    <tr><td colspan="4" class="p-6 text-center text-gray-500">Chưa có ai mua sản phẩm này.</td></tr>
                <?php else: ?>
                    <?php foreach($buyers as $b): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors" onclick="window.location.href='user_detail.php?id=<?= $b['user_id'] ?>'">
                            <td class="p-4 font-medium flex items-center gap-3">
                                <img src="<?= htmlspecialchars(user_avatar_url($b['avatar_url'], $b['full_name'])) ?>" class="w-8 h-8 rounded-full border border-gray-200 object-cover bg-gray-200">
                                <span class="text-blue-500 hover:underline"><?= htmlspecialchars($b['full_name']) ?></span>
                            </td>
                            <td class="p-4 text-gray-600"><?= htmlspecialchars($b['phone']) ?></td>
                            <td class="p-4 text-brand-black font-bold">x<?= $b['quantity'] ?></td>
                            <td class="p-4 text-gray-500"><?= date('d/m/Y H:i', strtotime($b['order_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
