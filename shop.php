<?php
require_once 'db_connect.php';
require_once 'includes/helpers.php';

// Fetch Categories
$stmt = $conn->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

// Get current category or search from URL
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch Products
if ($search_query !== '') {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE :q1 OR name LIKE :q2 OR name LIKE :q3 OR name = :q4 ORDER BY created_at DESC");
    $stmt->execute([
        'q1' => $search_query . ' %',
        'q2' => '% ' . $search_query . ' %',
        'q3' => '% ' . $search_query,
        'q4' => $search_query
    ]);
} elseif ($category_id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = :cat_id ORDER BY created_at DESC");
    $stmt->execute(['cat_id' => $category_id]);
} else {
    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
}
$products = $stmt->fetchAll();

$page_title = "Cửa Hàng";
if ($search_query !== '') {
    $page_title = 'Kết quả: "' . $search_query . '"';
} elseif ($category_id) {
    foreach($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $page_title = $cat['name'];
            break;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto py-12 px-6">
    <div class="flex flex-col md:flex-row justify-between md:items-baseline mb-12 border-b border-gray-200 pb-4 gap-4">
        <h1 class="font-serif text-4xl font-bold shrink-0"><?= htmlspecialchars($page_title) ?></h1>
        <div class="w-full md:w-auto flex flex-wrap gap-x-6 gap-y-3 pb-2">
            <a href="shop.php" class="text-sm uppercase tracking-widest <?= !$category_id ? 'font-bold text-brand-gold' : 'text-gray-500 hover:text-brand-black' ?>">Tất cả</a>
            <?php foreach($categories as $cat): ?>
                <a href="shop.php?category=<?= $cat['id'] ?>" class="text-sm uppercase tracking-widest <?= $category_id == $cat['id'] ? 'font-bold text-brand-gold' : 'text-gray-500 hover:text-brand-black' ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if(count($products) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php foreach($products as $product): ?>
                <div class="product-card group relative flex flex-col">
                    <div class="w-full h-[400px] overflow-hidden relative bg-gray-100">
                        <a href="product_detail.php?id=<?= $product['id'] ?>">
                            <!-- Fallback image logic via onerror -->
                            <img src="<?= htmlspecialchars(asset_image_url($product['image_url'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1584917865442-de89df76afd3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1035&q=80';"
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </a>
                        
                        <!-- Hover Cart Icon -->
                        <button onclick="addToCart(<?= $product['id'] ?>)" class="absolute bottom-4 right-4 w-10 h-10 bg-white text-brand-black rounded-full flex items-center justify-center shadow-lg opacity-0 transform translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 hover:bg-brand-gold hover:text-white" title="Thêm vào giỏ hàng">
                            <i class="fas fa-shopping-bag"></i>
                        </button>
                    </div>
                    <div class="mt-4 flex flex-col flex-grow">
                        <a href="product_detail.php?id=<?= $product['id'] ?>" class="font-serif text-lg hover:text-brand-gold transition-colors">
                            <?= htmlspecialchars($product['name']) ?>
                        </a>
                        <p class="text-gray-500 text-sm mt-1"><?= number_format($product['price'], 0, ',', '.') ?>đ</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-20 text-gray-500">
            <p>Không có sản phẩm nào trong danh mục này.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
