<?php
require_once 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: auth.php");
    exit();
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if(empty($cart_items)){
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT address FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user_data = $stmt->fetch();

if (empty($user_data['address'])) {
    $_SESSION['profile_error'] = 'Bạn cần cập nhật địa chỉ giao hàng trước khi thanh toán.';
    $_SESSION['redirect_after_profile'] = 'checkout.php';
    header("Location: profile.php");
    exit();
}

$total_amount = 0;
foreach($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

require_once 'includes/header.php';
?>

<div class="max-w-3xl mx-auto py-12 px-6 min-h-[70vh]">
    <h1 class="font-serif text-4xl font-bold mb-10 border-b border-gray-200 pb-4 text-center">Thanh Toán</h1>
    
    <!-- Địa chỉ giao hàng -->
    <div id="address-container" class="bg-white shadow-lg p-8 rounded-sm border border-gray-100 mb-8">
        <div class="flex justify-between items-start mb-4">
            <h2 class="font-serif text-2xl">Địa chỉ giao hàng</h2>
            <button type="button" id="btn-edit-address" class="text-brand-gold text-sm font-medium hover:underline">Sửa</button>
        </div>
        
        <!-- View mode -->
        <div id="address-view" class="text-gray-700">
            <p id="display-address" class="bg-gray-50 p-4 rounded border border-gray-200"><?= htmlspecialchars($user_data['address']) ?></p>
        </div>
        
        <!-- Edit mode (Hidden by default) -->
        <div id="address-edit" class="hidden">
            <textarea id="input-address" class="w-full border border-gray-300 rounded p-3 focus:outline-none focus:border-brand-gold mb-3 h-24"><?= htmlspecialchars($user_data['address']) ?></textarea>
            <div class="flex gap-2 justify-end">
                <button type="button" id="btn-cancel-address" class="px-4 py-2 text-sm text-gray-500 hover:text-brand-black transition-colors">Hủy</button>
                <button type="button" id="btn-save-address" class="px-4 py-2 text-sm bg-brand-black text-white hover:bg-brand-gold rounded transition-colors">Lưu địa chỉ</button>
            </div>
            <p id="address-msg" class="mt-2 text-sm hidden"></p>
        </div>
    </div>

    <!-- Form chọn phương thức -->
    <div id="payment-method-container" class="bg-white shadow-lg p-8 rounded-sm border border-gray-100">
        <h2 class="font-serif text-2xl mb-6">Chọn phương thức thanh toán</h2>
        
        <form id="payment-form">
            <div class="space-y-4 mb-8">
                <label class="flex items-center p-4 border border-gray-200 rounded cursor-pointer hover:border-brand-gold transition-colors">
                    <input type="radio" name="payment_method" value="VietQR" class="w-5 h-5 text-brand-gold accent-brand-gold" checked>
                    <span class="ml-4 font-medium flex-grow">Thanh toán qua VietQR</span>
                    <i class="fas fa-qrcode text-gray-400 text-xl"></i>
                </label>
                
                <label class="flex items-center p-4 border border-gray-200 rounded cursor-pointer hover:border-brand-gold transition-colors">
                    <input type="radio" name="payment_method" value="Momo" class="w-5 h-5 text-brand-gold accent-brand-gold">
                    <span class="ml-4 font-medium flex-grow">Ví điện tử Momo</span>
                    <i class="fas fa-wallet text-gray-400 text-xl"></i>
                </label>
                
                <label class="flex items-center p-4 border border-gray-200 rounded cursor-pointer hover:border-brand-gold transition-colors">
                    <input type="radio" name="payment_method" value="Bank Transfer" class="w-5 h-5 text-brand-gold accent-brand-gold">
                    <span class="ml-4 font-medium flex-grow">Chuyển khoản ngân hàng</span>
                    <i class="fas fa-university text-gray-400 text-xl"></i>
                </label>
            </div>

            <p class="text-sm italic text-gray-500 mb-8 text-center px-4">
                * Đây là website mô phỏng phục vụ đồ án học tập. Chức năng thanh toán chỉ mang tính minh họa, không xử lý giao dịch thực tế.
            </p>
            
            <div class="flex justify-between items-center border-t border-gray-200 pt-6 mb-6">
                <span class="text-gray-600">Tổng thanh toán:</span>
                <span class="font-bold text-xl text-brand-gold" id="checkout-total" data-total="<?= $total_amount ?>"><?= number_format($total_amount, 0, ',', '.') ?>đ</span>
            </div>

            <!-- Voucher Box -->
            <div class="mb-6 border border-gray-200 p-4 rounded bg-gray-50">
                <label class="block text-sm font-medium text-gray-700 mb-2">Thêm mã giảm giá</label>
                <div class="flex gap-2">
                    <input type="text" id="voucher-code" class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-brand-gold uppercase" placeholder="Nhập mã...">
                    <button type="button" id="btn-apply-voucher" class="bg-brand-black text-white px-4 py-2 rounded text-sm hover:bg-brand-gold transition-colors whitespace-nowrap">Áp dụng</button>
                </div>
                <p id="voucher-msg" class="mt-2 text-sm hidden"></p>
            </div>

            <button type="button" id="btn-confirm-method" class="w-full bg-brand-black text-white py-4 uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors">
                Xác Nhận Phương Thức
            </button>
        </form>
    </div>

    <!-- QR Code Container (Ẩn mặc định) -->
    <div id="qr-container" class="hidden bg-white shadow-lg p-8 rounded-sm border border-gray-100 text-center transform scale-95 opacity-0 transition-all duration-500">
        <h2 class="font-serif text-2xl mb-2">Quét Mã QR Để Thanh Toán</h2>
        <p class="text-gray-500 mb-6 text-sm">Sử dụng ứng dụng ngân hàng hoặc ví điện tử để quét mã.</p>
        
        <div class="bg-gray-50 p-6 inline-block rounded-lg mb-8 border border-gray-200">
            <?php
                // Khuyến nghị đặt file QR vào thư mục assets/images thay vì dùng đường dẫn tuyệt đối
                $paymentQrPath = __DIR__ . '/assets/images/payment_qr.png';
                $paymentQrSrc = '';
                if (is_file($paymentQrPath)) {
                    $paymentQrMime = mime_content_type($paymentQrPath) ?: 'image/png';
                    $paymentQrData = base64_encode(file_get_contents($paymentQrPath));
                    $paymentQrSrc = "data:{$paymentQrMime};base64,{$paymentQrData}";
                }
            ?>
            <?php if ($paymentQrSrc !== ''): ?>
                <img src="<?= htmlspecialchars($paymentQrSrc, ENT_QUOTES, 'UTF-8') ?>" alt="QR Code" class="w-64 h-64 mx-auto mix-blend-multiply">
            <?php else: ?>
                <div class="w-64 h-64 mx-auto flex items-center justify-center border border-dashed border-gray-300 text-gray-500 text-sm">
                    Không tìm thấy file QR trên máy.
                </div>
            <?php endif; ?>
            <p class="mt-4 font-bold text-lg text-brand-black" id="qr-total"><?= number_format($total_amount, 0, ',', '.') ?>đ</p>
        </div>
        <div id="payment-action-area">
            <button type="button" id="btn-confirm-payment" class="w-full bg-brand-gold text-white py-4 uppercase tracking-widest text-sm hover:bg-yellow-600 transition-colors flex items-center justify-center gap-3">
                <i class="fas fa-check-circle"></i> Tôi đã thanh toán thành công
            </button>

            <!-- Spinner (ẩn mặc định) -->
            <div id="payment-spinner" class="hidden w-full py-4 flex flex-col items-center justify-center">
                <div class="w-16 h-16 mx-auto rounded-full border-4 border-gray-200 border-t-brand-gold animate-spin"></div>
                <p class="text-gray-500 text-sm mt-3">Đang kiểm tra giao dịch...</p>
            </div>
        </div>
        <button type="button" id="btn-cancel-payment" class="w-full bg-transparent text-gray-500 py-4 uppercase tracking-widest text-xs hover:text-brand-black mt-2 transition-colors">
            Quay Lại
        </button>
    </div>

    <!-- Success Container (Ẩn mặc định) -->
    <div id="success-container" class="hidden text-center py-20 transform scale-95 opacity-0 transition-all duration-700">
        <!-- Hiệu ứng SVG Checkmark -->
        <div class="success-animation mb-8">
            <svg class="checkmark w-24 h-24 mx-auto block stroke-brand-gold stroke-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark__circle stroke-brand-gold fill-none" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark__check fill-none stroke-brand-gold" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
        </div>
        
        <h1 class="font-serif text-4xl md:text-5xl font-bold mb-4 text-brand-black">Thanh Toán Thành Công</h1>
        <p class="text-gray-600 text-lg mb-10 font-light">Cảm ơn bạn đã tin tưởng chúng tôi, đơn hàng sẽ được giao trong thời gian sớm nhất.</p>
        
        <a href="orders.php" class="inline-block border border-brand-black text-brand-black px-10 py-3 uppercase tracking-widest text-sm hover:bg-brand-black hover:text-white transition-colors">
            Xem Đơn Hàng
        </a>
    </div>

</div>

<style>
/* Animation cho tick xanh */
.checkmark__circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #D4AF37;
    fill: none;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #fff;
    stroke-miterlimit: 10;
    margin: 10% auto;
    box-shadow: inset 0px 0px 0px #D4AF37;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
}

.checkmark__check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% { stroke-dashoffset: 0; }
}
@keyframes scale {
    0%, 100% { transform: none; }
    50% { transform: scale3d(1.1, 1.1, 1); }
}
@keyframes fill {
    100% { box-shadow: inset 0px 0px 0px 40px rgba(212, 175, 55, 0.1); }
}
</style>

<script>
    let selectedMethod = '';

    // Handle Address Edit
    const btnEditAddress = document.getElementById('btn-edit-address');
    const btnCancelAddress = document.getElementById('btn-cancel-address');
    const btnSaveAddress = document.getElementById('btn-save-address');
    const addressView = document.getElementById('address-view');
    const addressEdit = document.getElementById('address-edit');
    const displayAddress = document.getElementById('display-address');
    const inputAddress = document.getElementById('input-address');
    const addressMsg = document.getElementById('address-msg');

    if(btnEditAddress) {
        btnEditAddress.addEventListener('click', () => {
            addressView.classList.add('hidden');
            addressEdit.classList.remove('hidden');
            btnEditAddress.classList.add('hidden');
        });
    }

    if(btnCancelAddress) {
        btnCancelAddress.addEventListener('click', () => {
            addressEdit.classList.add('hidden');
            addressView.classList.remove('hidden');
            btnEditAddress.classList.remove('hidden');
            // reset input to original
            inputAddress.value = displayAddress.innerText;
            addressMsg.classList.add('hidden');
        });
    }

    if(btnSaveAddress) {
        btnSaveAddress.addEventListener('click', () => {
            const newAddress = inputAddress.value.trim();
            if(!newAddress) {
                addressMsg.innerText = 'Địa chỉ không được để trống.';
                addressMsg.className = 'mt-2 text-sm text-red-600';
                addressMsg.classList.remove('hidden');
                return;
            }
            
            btnSaveAddress.disabled = true;
            btnSaveAddress.innerText = 'Đang lưu...';

            fetch('ajax/update_address.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ address: newAddress })
            })
            .then(res => res.json())
            .then(data => {
                btnSaveAddress.disabled = false;
                btnSaveAddress.innerText = 'Lưu địa chỉ';
                if(data.status === 'success') {
                    displayAddress.innerText = newAddress;
                    addressEdit.classList.add('hidden');
                    addressView.classList.remove('hidden');
                    btnEditAddress.classList.remove('hidden');
                    addressMsg.classList.add('hidden');
                } else {
                    addressMsg.innerText = data.message || 'Lỗi hệ thống';
                    addressMsg.className = 'mt-2 text-sm text-red-600';
                    addressMsg.classList.remove('hidden');
                }
            })
            .catch(err => {
                btnSaveAddress.disabled = false;
                btnSaveAddress.innerText = 'Lưu địa chỉ';
                console.error(err);
                alert('Đã có lỗi xảy ra.');
            });
        });
    }
    document.getElementById('btn-apply-voucher').addEventListener('click', function() {
        const code = document.getElementById('voucher-code').value.trim();
        const msgEl = document.getElementById('voucher-msg');
        if (!code) return;

        fetch('ajax/apply_voucher.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ code: code })
        })
        .then(res => res.json())
        .then(data => {
            msgEl.classList.remove('hidden');
            if (data.status === 'success') {
                msgEl.className = 'mt-2 text-sm text-green-600 font-medium';
                msgEl.innerText = `Chúc mừng, bạn nhận được mã giảm ${data.discount_percent}% ở đơn hàng này. Vui lòng sử dụng trong thời gian sớm nhất.`;
                
                // Update total
                const totalEl = document.getElementById('checkout-total');
                const originalTotal = parseInt(totalEl.getAttribute('data-total'));
                const discount = originalTotal * (data.discount_percent / 100);
                const finalTotal = originalTotal - discount;
                
                const formatter = new Intl.NumberFormat('vi-VN');
                totalEl.innerHTML = `<div class="flex flex-col items-end"><span class="line-through text-gray-400 text-sm">${formatter.format(originalTotal)}đ</span><span>${formatter.format(finalTotal)}đ</span></div>`;
                document.getElementById('qr-total').innerText = formatter.format(finalTotal) + 'đ';
            } else {
                msgEl.className = 'mt-2 text-sm text-red-600 font-medium';
                msgEl.innerText = data.message;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Đã có lỗi xảy ra khi áp dụng mã.');
        });
    });

    document.getElementById('btn-confirm-method').addEventListener('click', function() {
        const methods = document.getElementsByName('payment_method');
        for (let i = 0; i < methods.length; i++) {
            if (methods[i].checked) {
                selectedMethod = methods[i].value;
                break;
            }
        }

        const methodContainer = document.getElementById('payment-method-container');
        const qrContainer = document.getElementById('qr-container');

        // Hide method
        methodContainer.classList.add('hidden');
        
        // Show QR
        qrContainer.classList.remove('hidden');
        setTimeout(() => {
            qrContainer.classList.remove('scale-95', 'opacity-0');
            qrContainer.classList.add('scale-100', 'opacity-100');
        }, 50);
    });

    document.getElementById('btn-cancel-payment').addEventListener('click', function() {
        const methodContainer = document.getElementById('payment-method-container');
        const qrContainer = document.getElementById('qr-container');

        qrContainer.classList.remove('scale-100', 'opacity-100');
        qrContainer.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            qrContainer.classList.add('hidden');
            methodContainer.classList.remove('hidden');
        }, 500);
    });

    document.getElementById('btn-confirm-payment').addEventListener('click', function() {
        const btn = this;
        const spinner = document.getElementById('payment-spinner');
        const qrContainer = document.getElementById('qr-container');
        const actionArea = document.getElementById('payment-action-area');
        // Show spinner for ~2s to simulate checking transaction
        btn.classList.add('hidden');
        spinner.classList.remove('hidden');
        btn.disabled = true;
        actionArea.classList.add('pointer-events-none');

        setTimeout(() => {
            // After simulated delay, call AJAX to save order
            fetch('ajax/process_checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    payment_method: selectedMethod
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const successContainer = document.getElementById('success-container');
                    const headerCartBadge = document.getElementById('cart-badge');

                    if (headerCartBadge) {
                        headerCartBadge.classList.add('hidden');
                        headerCartBadge.textContent = '0';
                    }

                    // Hide spinner
                    spinner.classList.add('hidden');

                    // Hide QR
                    qrContainer.classList.remove('scale-100', 'opacity-100');
                    qrContainer.classList.add('scale-95', 'opacity-0');
                    
                    setTimeout(() => {
                        qrContainer.classList.add('hidden');
                        // Show success
                        successContainer.classList.remove('hidden');
                        setTimeout(() => {
                            successContainer.classList.remove('scale-95', 'opacity-0');
                            successContainer.classList.add('scale-100', 'opacity-100');
                        }, 50);
                    }, 500);
                } else {
                    spinner.classList.add('hidden');
                    btn.classList.remove('hidden');
                    btn.disabled = false;
                    actionArea.classList.remove('pointer-events-none');
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                spinner.classList.add('hidden');
                btn.classList.remove('hidden');
                btn.disabled = false;
                actionArea.classList.remove('pointer-events-none');
                alert('Đã có lỗi xảy ra khi thanh toán.');
            });
        }, 2000);
    });
</script>

<?php require_once 'includes/footer.php'; ?>
