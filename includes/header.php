<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';
$search_products = [];
if (isset($conn) && $conn instanceof PDO) {
    try {
        $stmt = $conn->prepare("
            SELECT id, name, image_url, price
            FROM products
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $search_products = $stmt->fetchAll();
    } catch (Throwable $e) {
        $search_products = [];
    }
}
// Calculate total cart items
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi" class="scroll-smooth scroll-pt-28">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>L'AURA | Maison De Luxe</title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' fill='%230a0a0a'/%3E%3Ctext x='50' y='55' font-family='serif' font-size='64' font-weight='bold' fill='%23D4AF37' text-anchor='middle' dominant-baseline='middle'%3EL%3C/text%3E%3C/svg%3E" type="image/svg+xml">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            black: '#0a0a0a',
                            white: '#ffffff',
                            beige: '#F5F5F0',
                            gold: '#D4AF37',
                        }
                    },
                    fontFamily: {
                        logo: ['"Playfair Display"', 'serif'],
                        serif: ['Roboto', 'sans-serif'],
                        sans: ['Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Security Script -->
    <script src="js/security.js"></script>
</head>
<body class="bg-brand-beige text-brand-black font-sans antialiased overflow-x-hidden">

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-5 right-5 bg-brand-black text-brand-gold px-6 py-3 rounded shadow-lg transform translate-y-20 opacity-0 transition-all duration-300 z-50 flex items-center gap-3">
        <i class="fas fa-check-circle"></i>
        <span id="toast-message">Thêm vào giỏ hàng thành công</span>
    </div>

    <!-- 1. Header & Navigation -->
    <!-- Thêm id header để JS có thể bắt sự kiện đổi màu nền khi scroll. Ở trang chủ là transparent, ở trang con có thể là bg-brand-black -->
    <header id="header" class="fixed top-0 left-0 w-full z-50 transition-all duration-500 text-white py-4 px-5 md:py-6 md:px-8 <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'bg-transparent' : 'bg-brand-black' ?>">
        <div class="w-full flex flex-col md:flex-row items-center relative gap-3 md:gap-0">
            <!-- Logo -->
            <div class="logo order-1 md:order-none font-logo text-3xl font-bold tracking-widest cursor-pointer md:absolute md:left-1/2 md:transform md:-translate-x-1/2">
                <a href="index.php">L'AURA</a>
            </div>
            
            <!-- Menu and Icons Wrapper -->
            <div class="order-2 md:order-none w-full flex-1 flex justify-between items-center">
                <!-- Hamburger Menu -->
                <div class="flex items-center space-x-6">
                    <button id="menu-btn" class="text-2xl hover:text-brand-gold transition-colors focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <!-- Right Icons -->
                <div class="flex space-x-4 md:space-x-6 text-lg items-center">
            <a href="support.php" class="hidden md:inline-block border border-current px-4 py-1.5 rounded-full text-sm font-medium tracking-wide hover:text-brand-gold hover:border-brand-gold transition-colors" title="Liên hệ chúng tôi">
                Liên hệ với chúng tôi
            </a>
            <a href="support.php" class="md:hidden hover:text-brand-gold transition-colors" title="Liên hệ chúng tôi">
                <i class="far fa-envelope"></i>
            </a>
            <div id="search-widget" class="relative">
                <button id="search-toggle-btn" type="button" class="hover:text-brand-gold transition-colors focus:outline-none" title="Tìm kiếm" aria-label="Tìm kiếm">
                    <i class="fas fa-search"></i>
                </button>
                <div id="search-panel" class="fixed left-4 right-4 top-20 sm:absolute sm:right-0 sm:left-auto sm:top-full sm:mt-4 z-[70] hidden sm:w-[26rem]">
                    <div class="bg-white border border-black/10 shadow-2xl rounded-3xl overflow-hidden max-h-[80vh] flex flex-col">
                        <form action="shop.php" method="GET" class="p-4 sm:p-5 border-b border-gray-100 flex items-center gap-3 bg-white">
                            <i class="fas fa-search text-gray-600"></i>
                            <input
                                type="text"
                                name="search"
                                id="search-input"
                                placeholder="Nhập tên sản phẩm (Nhấn Enter)..."
                                autocomplete="off"
                                class="w-full bg-transparent outline-none text-brand-black font-medium placeholder-gray-500 text-sm sm:text-base"
                            >
                            <button id="search-close-btn" type="button" class="text-gray-600 hover:text-brand-gold transition-colors focus:outline-none">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        <div class="max-h-[24rem] overflow-y-auto p-3 sm:p-4">
                            <p id="search-status" class="text-sm text-gray-500 mb-4">Nhập tên sản phẩm để tìm nhanh.</p>
                            <div id="search-results" class="space-y-3">
                                <?php foreach ($search_products as $search_product): ?>
                                    <a href="product_detail.php?id=<?= $search_product['id'] ?>" data-product-id="<?= $search_product['id'] ?>" class="search-result-item group flex items-center gap-3 bg-white border border-gray-100 rounded-2xl p-2 shadow-sm hover:shadow-lg transition-all">
                                        <div class="w-14 h-14 sm:w-16 sm:h-16 shrink-0 bg-gray-100 overflow-hidden rounded-xl">
                                            <img
                                                src="<?= htmlspecialchars(asset_image_url($search_product['image_url'])) ?>"
                                                alt="<?= htmlspecialchars($search_product['name']) ?>"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                                onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1584917865442-de89df76afd3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1035&q=80';"
                                            >
                                        </div>
                                        <div class="min-w-0 flex-1 pr-2">
                                            <h3 class="text-sm sm:text-[15px] font-medium text-brand-black leading-snug truncate"><?= htmlspecialchars($search_product['name']) ?></h3>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="relative group">
                    <a href="#" class="hover:text-brand-gold transition-colors inline-flex items-center gap-2">
                        <img
                            src="<?= htmlspecialchars(user_avatar_url($_SESSION['user_avatar'] ?? '', $_SESSION['user_name'] ?? 'User')) ?>"
                            alt="<?= htmlspecialchars($_SESSION['user_name']) ?>"
                            class="w-7 h-7 rounded-full object-cover border border-white/20 bg-brand-black"
                        >
                        <span class="hidden md:inline text-xs font-sans"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </a>
                    <div class="absolute right-0 mt-2 w-48 bg-brand-black text-white rounded shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 py-2">
                        <?php if(isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [1, 2])): ?>
                            <a href="admin/index.php" class="block px-4 py-2 text-sm font-bold text-brand-gold hover:bg-gray-800">Vào Trang Quản Trị</a>
                            <div class="border-b border-gray-800 my-1"></div>
                        <?php endif; ?>
                        <a href="profile.php" class="block px-4 py-2 text-sm hover:text-brand-gold">Hồ sơ của tôi</a>
                        <a href="orders.php" class="block px-4 py-2 text-sm hover:text-brand-gold">Đơn hàng của tôi</a>
                        <a href="auth.php?action=logout" class="block px-4 py-2 text-sm hover:text-brand-gold text-red-400 hover:text-red-300">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="auth.php" class="hover:text-brand-gold transition-colors" title="Đăng nhập"><i class="far fa-user"></i></a>
            <?php endif; ?>

            <a href="cart.php" class="relative hover:text-brand-gold transition-colors" title="Giỏ hàng">
                <i class="fas fa-shopping-bag"></i>
                <span class="absolute -top-2 -right-2 bg-brand-gold text-brand-black text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center <?= $cart_count > 0 ? '' : 'hidden' ?>" id="cart-badge">
                    <?= $cart_count ?>
                </span>
            </a>
        </div>
        </div>
    </header>

    <!-- Sidebar Menu -->
    <div id="sidebar" class="fixed inset-y-0 left-0 w-full sm:w-96 bg-brand-black text-white transform -translate-x-full transition-transform duration-500 z-[60] flex flex-col pt-24 pb-12 px-12 shadow-2xl border-r border-gray-800">
        <button id="close-menu-btn" class="absolute top-8 right-8 text-2xl hover:text-brand-gold transition-colors focus:outline-none">
            <i class="fas fa-times"></i>
        </button>
        <div class="flex flex-col space-y-8 flex-grow">
            <a href="index.php" class="font-logo text-3xl hover:text-brand-gold transition-colors w-fit">Trang Chủ</a>
            <a href="shop.php" class="font-logo text-3xl hover:text-brand-gold transition-colors w-fit">Khám Phá</a>
            <a href="support.php" class="font-logo text-3xl hover:text-brand-gold transition-colors w-fit">Hỗ Trợ</a>
            
            <div class="w-12 h-px bg-gray-600 my-4"></div>
            
            <a href="shop.php?category=1" class="text-sm uppercase tracking-widest hover:text-brand-gold transition-colors w-fit">Thời Trang Nam</a>
            <a href="shop.php?category=2" class="text-sm uppercase tracking-widest hover:text-brand-gold transition-colors w-fit">Thời Trang Nữ</a>
            <a href="shop.php?category=3" class="text-sm uppercase tracking-widest hover:text-brand-gold transition-colors w-fit">Phụ Kiện</a>
        </div>
        <div class="flex space-x-6 text-gray-400 mt-12">
            <a href="#" class="hover:text-white transition-colors"><i class="fab fa-instagram text-xl"></i></a>
            <a href="#" class="hover:text-white transition-colors"><i class="fab fa-facebook-f text-xl"></i></a>
            <a href="#" class="hover:text-white transition-colors"><i class="fab fa-twitter text-xl"></i></a>
        </div>
    </div>
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[55] opacity-0 pointer-events-none transition-opacity duration-500"></div>

    <!-- Main Content Wrapper (Add padding for pages other than index to avoid header overlap) -->
    <main class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? '' : 'pt-24' ?> min-h-screen">
