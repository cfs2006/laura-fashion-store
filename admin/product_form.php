<?php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = [
    'name' => '',
    'description' => '',
    'price' => '',
    'category_id' => '',
    'image_url' => ''
];

$error = '';
$success = '';

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $fetched = $stmt->fetch();
    if ($fetched) {
        $product = $fetched;
    } else {
        $id = 0; // Not found, switch to Add mode
    }
}

// Fetch categories for select
$catStmt = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim(str_replace('.', '', $_POST['price'] ?? ''));
    $category_id = trim($_POST['category_id'] ?? '');
    $imageUrl = $product['image_url']; // keep old by default

    if (empty($name) || empty($price) || empty($category_id)) {
        $error = "Vui lòng nhập Tên sản phẩm, Giá và Chọn danh mục.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Giá sản phẩm không hợp lệ.";
    } else {
        // Handle Image Upload
        if (!empty($_FILES['image']['name'])) {
            $img = $_FILES['image'];
            if ($img['error'] !== UPLOAD_ERR_OK) {
                $error = 'Không thể tải ảnh lên.';
            } else {
                $imageInfo = @getimagesize($img['tmp_name']);
                $mimeType = $imageInfo['mime'] ?? '';
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                
                if (!isset($allowed[$mimeType])) {
                    $error = 'Ảnh phải là định dạng JPG, PNG, GIF hoặc WEBP.';
                } else {
                    $uploadDir = __DIR__ . '/../assets/products';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
                    
                    $newFileName = 'prod_' . time() . '_' . bin2hex(random_bytes(2)) . '.' . $allowed[$mimeType];
                    if (move_uploaded_file($img['tmp_name'], $uploadDir . '/' . $newFileName)) {
                        $imageUrl = 'assets/products/' . $newFileName;
                        
                        // Xóa ảnh cũ nếu có
                        if (!empty($product['image_url']) && is_file(__DIR__ . '/../' . $product['image_url'])) {
                            @unlink(__DIR__ . '/../' . $product['image_url']);
                        }
                    } else {
                        $error = 'Lỗi khi lưu ảnh.';
                    }
                }
            }
        }

        if (empty($error)) {
            if ($id > 0) {
                // Update
                $stmt = $conn->prepare("UPDATE products SET name=:n, description=:d, price=:p, category_id=:c, image_url=:i WHERE id=:id");
                $stmt->execute([
                    'n' => $name, 'd' => $description, 'p' => $price, 'c' => $category_id, 'i' => $imageUrl, 'id' => $id
                ]);
                log_admin_action($conn, $_SESSION['user_id'], 'Update Product', "Cập nhật sản phẩm ID $id ($name)");
                $success = "Đã cập nhật sản phẩm thành công.";
                // Update local array for display
                $product['name'] = $name; $product['description'] = $description; $product['price'] = $price; $product['category_id'] = $category_id; $product['image_url'] = $imageUrl;
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, image_url) VALUES (:n, :d, :p, :c, :i)");
                $stmt->execute([
                    'n' => $name, 'd' => $description, 'p' => $price, 'c' => $category_id, 'i' => $imageUrl
                ]);
                $id = $conn->lastInsertId();
                log_admin_action($conn, $_SESSION['user_id'], 'Add Product', "Thêm sản phẩm mới ID $id ($name)");
                $success = "Đã thêm sản phẩm mới thành công.";
                $product['name'] = $name; $product['description'] = $description; $product['price'] = $price; $product['category_id'] = $category_id; $product['image_url'] = $imageUrl;
            }
        }
    }
}
?>

<div class="mb-8 flex items-center gap-4">
    <a href="products.php" class="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-500 hover:text-brand-black hover:border-brand-black transition-colors">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="font-serif text-3xl font-bold"><?= $id > 0 ? 'Sửa Sản Phẩm' : 'Thêm Sản Phẩm Mới' ?></h1>
    </div>
</div>

<?php if($success): ?>
    <div class="bg-green-100 text-green-700 p-4 rounded mb-6 text-sm flex items-center gap-3">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>
<?php if($error): ?>
    <div class="bg-red-100 text-red-700 p-4 rounded mb-6 text-sm flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 max-w-4xl">
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tên sản phẩm *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:border-brand-gold">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Danh mục *</label>
                <select name="category_id" required class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:border-brand-gold bg-white">
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Giá (VNĐ) *</label>
                <input type="text" name="price" id="priceInput" value="<?= $product['price'] ? number_format($product['price'], 0, ',', '.') : '' ?>" required oninput="formatPrice(this)" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:border-brand-gold">
            </div>

            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả *</label>
                <textarea name="description" rows="5" required class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:border-brand-gold"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hình ảnh sản phẩm</label>
                <div class="flex items-start gap-6">
                    <div id="imagePlaceholder" class="<?= !empty($product['image_url']) ? 'hidden' : 'flex' ?> w-32 h-40 bg-gray-100 rounded border border-dashed border-gray-300 items-center justify-center text-gray-400">
                        <i class="fas fa-image text-3xl"></i>
                    </div>
                    <?php 
                        $imgSrc = '';
                        if(!empty($product['image_url'])) {
                            $imgSrc = $product['image_url'];
                            if ($imgSrc && !preg_match('#^https?://#i', $imgSrc)) {
                                $imgSrc = '../' . ltrim($imgSrc, '/');
                            }
                        }
                    ?>
                    <img id="imagePreview" src="<?= htmlspecialchars($imgSrc) ?>" class="<?= !empty($product['image_url']) ? 'block' : 'hidden' ?> w-32 h-40 object-cover rounded border border-gray-200">
                    
                    <div class="flex-1">
                        <input type="file" id="imageInput" name="image" accept="image/*" <?= $id == 0 ? 'required' : '' ?> class="w-full border border-gray-300 rounded p-2 text-sm">
                        <p class="text-xs text-gray-500 mt-2">Bỏ trống nếu không muốn thay đổi ảnh. Hỗ trợ JPG, PNG, GIF, WEBP.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-200 flex justify-end">
            <button type="submit" class="bg-brand-black text-white px-8 py-3 uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors font-medium rounded">
                <i class="fas fa-save mr-2"></i> Lưu Sản Phẩm
            </button>
        </div>
    </form>
</div>

<script>
// Format giá tiền có dấu chấm
function formatPrice(input) {
    let value = input.value.replace(/\./g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    // Lọc ký tự không phải số
    value = value.replace(/\D/g, '');
    if (value === '') return;
    
    // Thêm dấu chấm
    input.value = new Intl.NumberFormat('de-DE').format(value);
}

// Preview ảnh
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('imagePlaceholder');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            preview.classList.add('block');
            placeholder.classList.add('hidden');
            placeholder.classList.remove('flex');
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
