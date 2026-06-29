<?php
require_once 'db_connect.php';
require_once 'includes/helpers.php';

// Fetch featured products (from Nam and Nữ categories, category_id = 1 or 2)
$stmt = $conn->query("SELECT * FROM products WHERE category_id IN (1, 2) ORDER BY created_at DESC");
$featured_products = $stmt->fetchAll();

// Fetch accessory products (category_id = 3)
$stmt = $conn->query("SELECT * FROM products WHERE category_id = 3 ORDER BY created_at DESC");
$accessory_products = $stmt->fetchAll();

require_once 'includes/header.php';
?>

    <!-- 2. Hero Section -->
    <section class="hero relative h-screen w-full flex items-center justify-center overflow-hidden -mt-24">
        <!-- Background Video/Image with Parallax -->
        <div class="hero-bg absolute top-0 left-0 w-full h-[120%] bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');"></div>
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        
        <div class="relative z-10 text-center text-white mt-16">
            <h1 class="hero-title font-serif text-6xl md:text-8xl font-bold mb-6 tracking-wide leading-tight clip-text">
                <span class="inline-block transform translate-y-full opacity-0">Elegance</span><br>
                <span class="inline-block transform translate-y-full opacity-0">Redefined</span>
            </h1>
            <p class="hero-subtitle text-lg md:text-xl font-light tracking-widest mb-10 opacity-0">BỘ SƯU TẬP THU ĐÔNG 2026</p>
            <a href="shop.php" class="hero-btn inline-block border border-white text-white px-10 py-4 uppercase tracking-widest text-sm hover:bg-white hover:text-brand-black transition-all duration-500 opacity-0">
                Khám Phá Bộ Sưu Tập
            </a>
        </div>
    </section>

    <!-- 3. Brand Story -->
    <section class="brand-story py-32 px-6 bg-brand-white min-h-[80vh] flex items-center justify-center">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="story-text font-serif text-3xl md:text-5xl leading-relaxed text-brand-black opacity-0 transform translate-y-10">
                "Thời trang không chỉ là quần áo. Nó là ngôn ngữ của sự tinh tế, là di sản của kỹ năng thủ công tuyệt mỹ được lưu truyền qua nhiều thế hệ."
            </h2>
            <div class="story-line w-px h-24 bg-brand-gold mx-auto mt-16 scale-y-0 transform origin-top"></div>
        </div>
    </section>

    <!-- 4. Categories Navigation -->
    <section class="categories py-20 px-6 bg-brand-beige">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Category Item -->
            <a href="shop.php?category=1" class="category-card group relative h-[70vh] overflow-hidden cursor-pointer">
                <div class="category-img absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110" style="background-image: url('https://images.unsplash.com/photo-1617137968427-85924c800a22?ixlib=rb-4.0.3&auto=format&fit=crop&w=987&q=80');"></div>
                <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-40 transition-all duration-500"></div>
                <div class="absolute bottom-10 left-10 text-white z-10">
                    <h3 class="font-serif text-3xl mb-2">Thời Trang Nam</h3>
                    <p class="font-sans text-sm tracking-widest uppercase opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">Khám Phá</p>
                </div>
            </a>
            <!-- Category Item -->
            <a href="shop.php?category=2" class="category-card group relative h-[70vh] overflow-hidden cursor-pointer">
                <div class="category-img absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110" style="background-image: url('https://images.unsplash.com/photo-1539008835657-9e8e9680c956?ixlib=rb-4.0.3&auto=format&fit=crop&w=987&q=80');"></div>
                <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-40 transition-all duration-500"></div>
                <div class="absolute bottom-10 left-10 text-white z-10">
                    <h3 class="font-serif text-3xl mb-2">Thời Trang Nữ</h3>
                    <p class="font-sans text-sm tracking-widest uppercase opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">Khám Phá</p>
                </div>
            </a>
            <!-- Category Item -->
            <a href="shop.php?category=3" class="category-card group relative h-[70vh] overflow-hidden cursor-pointer">
                <div class="category-img absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110" style="background-image: url('https://images.unsplash.com/photo-1584917865442-de89df76afd3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1035&q=80');"></div>
                <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-40 transition-all duration-500"></div>
                <div class="absolute bottom-10 left-10 text-white z-10">
                    <h3 class="font-serif text-3xl mb-2">Phụ Kiện</h3>
                    <p class="font-sans text-sm tracking-widest uppercase opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">Khám Phá</p>
                </div>
            </a>
        </div>
    </section>

    <!-- 5. Featured Collection -->
    <section class="featured-wrapper relative bg-brand-white py-20 flex flex-col justify-center">
        <div class="px-10 mb-10 flex justify-between items-end">
            <div>
                <h2 class="font-serif text-4xl font-bold">Nổi Bật</h2>
                <p class="text-gray-500 text-sm mt-2 uppercase tracking-widest font-light">Những thiết kế sang trọng & đẳng cấp</p>
            </div>
            <a href="shop.php" class="text-sm uppercase tracking-widest text-brand-black hover:text-brand-gold border-b border-brand-black hover:border-brand-gold pb-1 transition-all">Xem tất cả</a>
        </div>
        
        <div class="relative w-full">
            <!-- Nút điều hướng Trái/Phải nằm ở 2 bên mép -->
            <button id="prev-btn" class="absolute left-4 top-1/3 transform -translate-y-1/2 z-10 w-12 h-12 flex items-center justify-center bg-white/40 backdrop-blur-md border border-white/50 hover:bg-white/70 transition-all rounded-full text-brand-black shadow-lg">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button id="next-btn" class="absolute right-4 top-1/3 transform -translate-y-1/2 z-10 w-12 h-12 flex items-center justify-center bg-white/40 backdrop-blur-md border border-white/50 hover:bg-white/70 transition-all rounded-full text-brand-black shadow-lg">
                <i class="fas fa-chevron-right"></i>
            </button>

            <!-- Container Sản Phẩm -->
            <div class="swiper featured-swiper w-full px-10 overflow-hidden cursor-grab active:cursor-grabbing">
                <div class="swiper-wrapper">
                    <?php foreach ($featured_products as $prod): ?>
                    <!-- Product -->
                    <div class="swiper-slide featured-item w-[80vw] md:w-[25vw] flex-shrink-0 relative group flex flex-col">
                        <div class="w-full h-[50vh] overflow-hidden relative">
                            <a href="product_detail.php?id=<?= $prod['id'] ?>">
                                <img src="<?= htmlspecialchars(asset_image_url($prod['image_url'])) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" 
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 bg-gray-100">
                            </a>
                            <!-- Hover Add to Cart -->
                            <button onclick="addToCart(<?= $prod['id'] ?>)" class="absolute bottom-4 right-4 w-10 h-10 bg-white text-brand-black rounded-full flex items-center justify-center shadow-lg opacity-0 transform translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 hover:bg-brand-gold hover:text-white z-20" title="Thêm vào giỏ hàng">
                                <i class="fas fa-shopping-bag"></i>
                            </button>
                        </div>
                        <div class="mt-6 flex flex-col justify-between flex-grow">
                            <a href="product_detail.php?id=<?= $prod['id'] ?>" class="font-serif text-xl hover:text-brand-gold transition-colors">
                                <?= htmlspecialchars($prod['name']) ?>
                            </a>
                            <p class="text-gray-500 text-sm mt-1"><?= number_format($prod['price'], 0, ',', '.') ?>đ</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- 5.5 Accessories Collection -->
    <section class="featured-wrapper relative bg-brand-beige py-20 flex flex-col justify-center border-t border-gray-200">
        <div class="px-10 mb-10 flex justify-between items-end">
            <div>
                <h2 class="font-serif text-4xl font-bold">Phụ Kiện</h2>
                <p class="text-gray-500 text-sm mt-2 uppercase tracking-widest font-light">Điểm nhấn hoàn hảo cho phong cách</p>
            </div>
            <a href="shop.php?category=3" class="text-sm uppercase tracking-widest text-brand-black hover:text-brand-gold border-b border-brand-black hover:border-brand-gold pb-1 transition-all">Xem tất cả</a>
        </div>
        
        <div class="relative w-full">
            <!-- Nút điều hướng Trái/Phải -->
            <button id="prev-acc-btn" class="absolute left-4 top-1/3 transform -translate-y-1/2 z-10 w-12 h-12 flex items-center justify-center bg-white/40 backdrop-blur-md border border-white/50 hover:bg-white/70 transition-all rounded-full text-brand-black shadow-lg">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button id="next-acc-btn" class="absolute right-4 top-1/3 transform -translate-y-1/2 z-10 w-12 h-12 flex items-center justify-center bg-white/40 backdrop-blur-md border border-white/50 hover:bg-white/70 transition-all rounded-full text-brand-black shadow-lg">
                <i class="fas fa-chevron-right"></i>
            </button>

            <!-- Container Sản Phẩm Phụ Kiện -->
            <div class="swiper accessory-swiper w-full px-10 overflow-hidden cursor-grab active:cursor-grabbing">
                <div class="swiper-wrapper">
                    <?php foreach ($accessory_products as $prod): ?>
                    <!-- Product -->
                    <div class="swiper-slide featured-item w-[80vw] md:w-[25vw] flex-shrink-0 relative group flex flex-col">
                        <div class="w-full h-[50vh] overflow-hidden relative">
                            <a href="product_detail.php?id=<?= $prod['id'] ?>">
                                <img src="<?= htmlspecialchars(asset_image_url($prod['image_url'])) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" 
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 bg-gray-100">
                            </a>
                            <!-- Hover Add to Cart -->
                            <button onclick="addToCart(<?= $prod['id'] ?>)" class="absolute bottom-4 right-4 w-10 h-10 bg-white text-brand-black rounded-full flex items-center justify-center shadow-lg opacity-0 transform translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 hover:bg-brand-gold hover:text-white z-20" title="Thêm vào giỏ hàng">
                                <i class="fas fa-shopping-bag"></i>
                            </button>
                        </div>
                        <div class="mt-6 flex flex-col justify-between flex-grow">
                            <a href="product_detail.php?id=<?= $prod['id'] ?>" class="font-serif text-xl hover:text-brand-gold transition-colors">
                                <?= htmlspecialchars($prod['name']) ?>
                            </a>
                            <p class="text-gray-500 text-sm mt-1"><?= number_format($prod['price'], 0, ',', '.') ?>đ</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Lookbook Section -->
    <section class="lookbook-section py-24 bg-brand-white">
        <div class="max-w-7xl mx-auto px-6 mb-16 text-center">
            <p class="text-brand-gold uppercase tracking-[0.3em] text-xs font-bold mb-3">BST Mùa Lễ Hội 2026</p>
            <h2 class="font-serif text-5xl md:text-6xl font-bold text-brand-black">Lookbook</h2>
            <div class="w-16 h-px bg-brand-black mx-auto mt-8"></div>
        </div>

        <div class="max-w-7xl mx-auto px-6">
            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-4 md:grid-rows-2 gap-4 md:h-[800px]">
                
                <!-- Ảnh 1: Lớn, chiếm 2 cột, 2 hàng -->
                <div class="md:col-span-2 md:row-span-2 relative group overflow-hidden h-[400px] md:h-auto">
                    <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Lookbook 1" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105">
                </div>

                <!-- Ảnh 2: Dọc, chiếm 1 cột, 2 hàng -->
                <div class="md:col-span-1 md:row-span-2 relative group overflow-hidden h-[400px] md:h-auto">
                    <img src="https://images.unsplash.com/photo-1512436991641-6745cdb1723f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Lookbook 2" class="w-full h-full object-cover object-top transition-transform duration-1000 group-hover:scale-105">
                </div>

                <!-- Ảnh 3: Nhỏ, hàng 1 -->
                <div class="md:col-span-1 md:row-span-1 relative group overflow-hidden h-[300px] md:h-auto">
                    <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Lookbook 3" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105">
                </div>

                <!-- Ảnh 4: Nhỏ, hàng 2 -->
                <div class="md:col-span-1 md:row-span-1 relative group overflow-hidden h-[300px] md:h-auto">
                    <img src="https://images.unsplash.com/photo-1502163140606-888448ae8cfe?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Lookbook 4" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105">
                </div>

            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
