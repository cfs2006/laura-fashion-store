<?php
require_once 'db_connect.php';
require_once 'includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'cart.php';
    header("Location: auth.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header("Location: cart.php");
    exit();
}


require_once 'includes/header.php';

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_amount = 0;

$cart_product_ids = array_keys($cart_items);
$cart_products = [];

if (!empty($cart_product_ids)) {
    $placeholders = implode(',', array_fill(0, count($cart_product_ids), '?'));
    $stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)");
    $stmt->execute($cart_product_ids);
    foreach ($stmt->fetchAll() as $product) {
        $cart_products[(int)$product['id']] = $product;
    }
}

?>

<div class="max-w-7xl mx-auto py-12 px-6 min-h-[70vh]">
    <h1 class="font-serif text-4xl font-bold mb-10 border-b border-gray-200 pb-4">Giỏ Hàng Của Bạn</h1>
    
    <?php if(empty($cart_items)): ?>
        <div class="text-center py-20">
            <div class="text-6xl text-gray-300 mb-6"><i class="fas fa-shopping-bag"></i></div>
            <p class="text-gray-500 mb-6 text-lg">Giỏ hàng của bạn đang trống.</p>
            <a href="shop.php" class="inline-block bg-brand-black text-white px-8 py-3 uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="flex flex-col gap-12">
            <!-- Danh sách sản phẩm -->
            <div class="w-full">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-200 text-sm uppercase tracking-widest text-gray-500">
                            <th class="pb-4 font-normal">Sản phẩm</th>
                            <th class="pb-4 font-normal text-center">Số lượng</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cart_items as $item): ?>
                        <?php 
                            $current_product = $cart_products[(int)$item['id']] ?? $item;
                            $item_price = isset($current_product['price']) ? $current_product['price'] : $item['price'];
                            $item_name = isset($current_product['name']) ? $current_product['name'] : $item['name'];
                            $item_image = isset($current_product['image_url']) ? $current_product['image_url'] : $item['image_url'];
                            $item_total = $item_price * $item['quantity'];
                            $total_amount += $item_total;
                        ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-4 sm:py-6">
                                <div class="flex items-center gap-3 sm:gap-6">
                                    <img src="<?= htmlspecialchars(asset_image_url($item_image)) ?>" alt="<?= htmlspecialchars($item_name) ?>" 
                                          onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1584917865442-de89df76afd3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1035&q=80';"
                                          class="w-16 h-24 sm:w-24 sm:h-32 object-cover bg-gray-100 flex-shrink-0">
                                    <div class="min-w-0">
                                        <h3 class="font-serif text-sm sm:text-lg mb-1 sm:mb-2 break-words"><?= htmlspecialchars($item_name) ?></h3>
                                        <p class="text-gray-500 text-xs sm:text-sm"><?= number_format($item_price, 0, ',', '.') ?>đ</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 sm:py-6 text-center">
                                <div class="inline-flex items-center border border-gray-200 rounded-sm">
                                    <button type="button" onclick="updateCartQuantity(<?= $item['id'] ?>, -1)" class="px-2 sm:px-3 py-1 text-gray-500 hover:text-brand-black hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-minus text-[10px] sm:text-xs"></i>
                                    </button>
                                    <span id="qty-<?= $item['id'] ?>" class="w-8 sm:w-10 text-center py-1 text-sm sm:text-base bg-gray-50 border-x border-gray-200 font-medium"><?= $item['quantity'] ?></span>
                                    <button type="button" onclick="updateCartQuantity(<?= $item['id'] ?>, 1)" class="px-2 sm:px-3 py-1 text-gray-500 hover:text-brand-black hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-plus text-[10px] sm:text-xs"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="py-4 sm:py-6 text-right">
                                <a href="cart.php?action=remove&id=<?= $item['id'] ?>" class="cart-remove text-gray-400 hover:text-red-500 transition-colors" title="Xóa">
                                    <i class="fas fa-times"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tổng giỏ hàng -->
            <div class="w-full lg:w-1/2 ml-auto">
                <div class="bg-gray-50 p-8">
                    <h2 class="font-serif text-2xl font-bold mb-6 border-b border-gray-200 pb-4">Tổng Đơn Hàng</h2>
                    
                    <div class="flex justify-between mb-4 text-sm">
                        <span class="text-gray-600">Tạm tính:</span>
                        <span id="cart-subtotal"><?= number_format($total_amount, 0, ',', '.') ?>đ</span>
                    </div>
                    <div class="flex justify-between mb-6 text-sm">
                        <span class="text-gray-600">Phí giao hàng:</span>
                        <span>Miễn phí</span>
                    </div>
                    
                    <div class="flex justify-between mb-8 border-t border-gray-200 pt-4">
                        <span class="font-bold uppercase tracking-widest text-sm">Tổng cộng:</span>
                        <span class="font-bold text-xl" id="cart-total"><?= number_format($total_amount, 0, ',', '.') ?>đ</span>
                    </div>

                    <a href="checkout.php" class="block w-full bg-brand-black text-white text-center py-4 uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors">
                        Tiến Hành Thanh Toán
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function formatPrice(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + 'đ';
}

function updateCartQuantity(productId, change) {
    const currentQtyText = document.getElementById('qty-' + productId).innerText;
    const currentQty = parseInt(currentQtyText);
    
    if (change === -1 && currentQty <= 1) {
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            return;
        }
    }
    
    fetch('ajax/update_cart_quantity.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, qty_change: change })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (data.cart_empty || data.new_qty === 0) {
                location.reload();
            } else {
                document.getElementById('qty-' + productId).innerText = data.new_qty;
                document.getElementById('cart-subtotal').innerText = formatPrice(data.total_amount);
                document.getElementById('cart-total').innerText = formatPrice(data.total_amount);
                
                const headerCartBadge = document.getElementById('cart-badge');
                if (headerCartBadge) {
                    headerCartBadge.textContent = data.cart_count;
                    if (data.cart_count > 0) {
                        headerCartBadge.classList.remove('hidden');
                    } else {
                        headerCartBadge.classList.add('hidden');
                    }
                }
            }
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Đã có lỗi xảy ra.');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
