<?php
require_once 'db_connect.php';
require_once 'includes/helpers.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: shop.php");
    exit();
}

$product_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = :id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: shop.php");
    exit();
}

$category_id = isset($product['category_id']) ? (int)$product['category_id'] : 0;
$category_name = isset($product['category_name']) && $product['category_name'] !== ''
    ? $product['category_name']
    : 'Danh mục';
$description = isset($product['description']) && trim((string)$product['description']) !== ''
    ? $product['description']
    : 'Chưa có mô tả cho sản phẩm này.';

require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto py-12 px-6 min-h-[70vh]">
    <div class="flex flex-col md:flex-row gap-12">
        <!-- Product Image -->
        <div class="w-full md:w-1/2">
            <div class="w-full bg-gray-100 relative h-[600px] overflow-hidden">
                <img src="<?= htmlspecialchars(asset_image_url($product['image_url'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1584917865442-de89df76afd3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1035&q=80';"
                     class="w-full h-full object-cover">
            </div>
        </div>

        <!-- Product Info -->
        <div class="w-full md:w-1/2 flex flex-col justify-center">
            <div class="mb-4">
                <a href="<?= $category_id > 0 ? 'shop.php?category=' . $category_id : 'shop.php' ?>" class="uppercase tracking-widest text-sm text-gray-500 hover:text-brand-gold transition-colors">
                    <?= htmlspecialchars($category_name) ?>
                </a>
            </div>
            <h1 class="font-serif text-4xl font-bold mb-4"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="text-2xl text-brand-black mb-8"><?= number_format($product['price'], 0, ',', '.') ?>đ</p>
            
            <div class="mb-8">
                <h3 class="font-semibold mb-2">Mô tả</h3>
                <p class="text-gray-600 font-light leading-relaxed">
                    <?= nl2br(htmlspecialchars($description)) ?>
                </p>
            </div>

            <!-- Quantity & Add to Cart -->
            <div class="flex flex-col sm:flex-row items-center gap-6 mt-auto border-t border-gray-200 pt-8">
                <div class="flex items-center border border-gray-300">
                    <button type="button" onclick="updateQty(-1)" class="w-12 h-12 flex items-center justify-center text-gray-500 hover:text-brand-black hover:bg-gray-50 transition-colors focus:outline-none">
                        <i class="fas fa-minus text-xs"></i>
                    </button>
                    <input type="number" id="qty-input" value="1" min="1" class="w-16 h-12 text-center border-x border-gray-300 focus:outline-none focus:border-brand-gold bg-transparent appearance-none">
                    <button type="button" onclick="updateQty(1)" class="w-12 h-12 flex items-center justify-center text-gray-500 hover:text-brand-black hover:bg-gray-50 transition-colors focus:outline-none">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                </div>
                
                <button onclick="addToCartDetail(<?= $product['id'] ?>)" class="w-full sm:w-auto flex-grow bg-brand-black text-white h-12 px-8 uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors flex items-center justify-center gap-3">
                    <span>Thêm vào giỏ hàng</span>
                    <i class="fas fa-shopping-bag"></i>
                </button>
            </div>

            <div class="mt-8 text-sm text-gray-500 space-y-2">
                <p><i class="fas fa-truck mr-2"></i> Miễn phí giao hàng cho đơn từ 2.000.000đ</p>
                <p><i class="fas fa-undo mr-2"></i> Đổi trả miễn phí trong 30 ngày</p>
            </div>
        </div>
    </div>
</div>

<script>
function updateQty(change) {
    const input = document.getElementById('qty-input');
    let currentVal = parseInt(input.value) || 1;
    let newVal = currentVal + change;
    if (newVal < 1) newVal = 1;
    input.value = newVal;
}

function addToCartDetail(productId) {
    const qty = parseInt(document.getElementById('qty-input').value) || 1;
    addToCart(productId, qty);
}
</script>

<?php require_once 'includes/footer.php'; ?>
