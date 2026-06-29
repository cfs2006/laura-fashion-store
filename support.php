<?php
require_once 'db_connect.php';
require_once 'includes/header.php';
?>

    <!-- Support Hero -->
    <section class="relative pt-40 pb-20 px-8 w-full flex flex-col items-center justify-center bg-brand-white text-center border-b border-gray-200">
        <h1 class="font-serif text-5xl md:text-6xl font-bold mb-6">Dịch Vụ Khách Hàng</h1>
        <p class="font-sans text-lg text-gray-500 max-w-xl mx-auto leading-relaxed">
            Chúng tôi luôn ở đây để lắng nghe và hỗ trợ bạn trong mọi trải nghiệm mua sắm tại L'AURA.
        </p>
    </section>

    <!-- Support Content Tabs -->
    <section class="py-12 px-8 w-full bg-brand-white min-h-[60vh]">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-12">
            
            <!-- Sidebar Navigation -->
            <div class="w-full md:w-1/4">
                <div class="bg-gray-50 p-6 rounded-lg sticky top-32">
                    <ul class="space-y-2">
                        <li>
                            <button class="support-tab-btn w-full text-left px-4 py-3 rounded text-sm font-medium transition-colors bg-brand-black text-white" data-target="tab-contact">Thông Tin Liên Hệ</button>
                        </li>
                        <li>
                            <button class="support-tab-btn w-full text-left px-4 py-3 rounded text-sm font-medium transition-colors text-gray-600 hover:bg-gray-100" data-target="tab-form">Gửi Tin Nhắn</button>
                        </li>
                        <li>
                            <button class="support-tab-btn w-full text-left px-4 py-3 rounded text-sm font-medium transition-colors text-gray-600 hover:bg-gray-100" data-target="tab-returns">Chính Sách Đổi Trả</button>
                        </li>
                        <li>
                            <button class="support-tab-btn w-full text-left px-4 py-3 rounded text-sm font-medium transition-colors text-gray-600 hover:bg-gray-100" data-target="tab-faq">Câu Hỏi Thường Gặp</button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Content Area -->
            <div class="w-full md:w-3/4">
                <!-- Tab: Thông tin liên hệ -->
                <div id="tab-contact" class="support-tab-content block animate-[fadeIn_0.3s_ease-out]">
                    <h2 class="font-serif text-3xl mb-6">Liên Hệ Với L'AURA</h2>
                    <p class="text-gray-600 font-light mb-8 leading-relaxed">
                        Đội ngũ Chăm Sóc Khách Hàng của chúng tôi hoạt động từ Thứ 2 đến Thứ 7 (9:00 - 18:00). Bạn có thể liên hệ thông qua các kênh chính thức sau:
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded border border-gray-100">
                            <i class="fas fa-phone text-3xl text-brand-gold mb-4 block"></i>
                            <h3 class="font-bold mb-2">Đường dây nóng</h3>
                            <p class="text-gray-500 text-sm tracking-wide">+84 (0) 123 456 789</p>
                        </div>
                        <div class="bg-gray-50 p-6 rounded border border-gray-100">
                            <i class="fas fa-envelope text-3xl text-brand-gold mb-4 block"></i>
                            <h3 class="font-bold mb-2">Email Hỗ trợ</h3>
                            <p class="text-gray-500 text-sm tracking-wide">customercare@laura.com</p>
                        </div>
                    </div>
                </div>

                <!-- Tab: Gửi form -->
                <div id="tab-form" class="support-tab-content hidden animate-[fadeIn_0.3s_ease-out]">
                    <h2 class="font-serif text-3xl mb-6">Gửi tin nhắn cho chúng tôi</h2>
                    <p class="text-gray-600 font-light mb-8 leading-relaxed">
                        Mọi ý kiến đóng góp, khiếu nại hoặc thắc mắc đều được chúng tôi trân trọng và sẽ phản hồi trong vòng 24h làm việc.
                    </p>
                    
                    <div id="form-message" class="hidden p-4 rounded mb-6 text-sm flex items-center gap-3"></div>

                    <form id="contactForm" class="bg-gray-50 p-8 rounded-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium mb-2 text-gray-700">Họ và Tên *</label>
                                <input type="text" name="name" id="c_name" required class="w-full border border-gray-300 py-3 px-4 focus:outline-none focus:border-brand-gold transition-colors bg-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2 text-gray-700">Email liên hệ *</label>
                                <input type="email" name="email" id="c_email" required class="w-full border border-gray-300 py-3 px-4 focus:outline-none focus:border-brand-gold transition-colors bg-white">
                            </div>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-gray-700">Nội dung *</label>
                            <textarea name="message" id="c_message" rows="5" required class="w-full border border-gray-300 py-3 px-4 focus:outline-none focus:border-brand-gold transition-colors bg-white"></textarea>
                        </div>
                        <button type="submit" id="btn-submit" class="bg-brand-black text-white px-8 py-3 uppercase tracking-widest text-sm hover:bg-brand-gold transition-colors inline-flex items-center justify-center min-w-[200px]">
                            <span>Gửi Tin Nhắn</span>
                        </button>
                    </form>
                </div>

                <!-- Tab: Đổi trả -->
                <div id="tab-returns" class="support-tab-content hidden animate-[fadeIn_0.3s_ease-out]">
                    <h2 class="font-serif text-3xl mb-6">Chính Sách Đổi Trả</h2>
                    <div class="prose prose-sm max-w-none text-gray-600 font-light leading-relaxed space-y-4">
                        <p>Chúng tôi muốn bạn hoàn toàn hài lòng với trải nghiệm mua sắm tại L'AURA. Nếu bạn đổi ý hoặc sản phẩm không như mong đợi, bạn có thể hoàn trả dễ dàng.</p>
                        <h4 class="text-black font-medium mt-6 mb-2">1. Điều kiện áp dụng</h4>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Khách hàng có thể yêu cầu đổi trả sản phẩm trong vòng 30 ngày kể từ ngày nhận hàng.</li>
                            <li>Sản phẩm phải còn nguyên trạng, chưa qua sử dụng, chưa giặt ủi, còn nguyên tem mác và hóa đơn mua hàng gốc.</li>
                            <li>Các sản phẩm nước hoa, mỹ phẩm (nếu có) phải chưa được mở niêm phong.</li>
                        </ul>
                        <h4 class="text-black font-medium mt-6 mb-2">2. Quy trình đổi trả</h4>
                        <p>Vui lòng điền vào biểu mẫu "Gửi tin nhắn" kèm theo Mã đơn hàng của bạn. Đội ngũ CSKH sẽ liên hệ và cung cấp mã vận chuyển để bạn gửi trả hàng về kho miễn phí.</p>
                        <h4 class="text-black font-medium mt-6 mb-2">3. Hoàn tiền</h4>
                        <p>Số tiền sẽ được hoàn lại trực tiếp vào tài khoản ngân hàng hoặc thẻ tín dụng/ghi nợ mà bạn đã sử dụng khi thanh toán trong vòng 5-7 ngày làm việc sau khi chúng tôi nhận và kiểm tra hàng hoàn trả thành công.</p>
                    </div>
                </div>

                <!-- Tab: FAQ -->
                <div id="tab-faq" class="support-tab-content hidden animate-[fadeIn_0.3s_ease-out]">
                    <h2 class="font-serif text-3xl mb-8">Câu Hỏi Thường Gặp</h2>
                    <div class="space-y-6">
                        <div class="border-b border-gray-200 pb-4">
                            <h4 class="font-serif text-xl mb-2 text-brand-black">Làm sao để theo dõi đơn hàng?</h4>
                            <p class="text-gray-500 text-sm font-light leading-relaxed">Bạn có thể theo dõi đơn hàng bằng cách đăng nhập vào tài khoản trên website và vào phần "Đơn hàng của tôi". Một email chứa mã vận đơn cũng sẽ được gửi đến bạn khi đơn hàng bắt đầu giao.</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <h4 class="font-serif text-xl mb-2 text-brand-black">Thời gian giao hàng là bao lâu?</h4>
                            <p class="text-gray-500 text-sm font-light leading-relaxed">Thời gian giao hàng tiêu chuẩn là 2-4 ngày làm việc đối với khu vực trung tâm và 5-7 ngày làm việc đối với khu vực xa hơn.</p>
                        </div>
                        <div class="border-b border-gray-200 pb-4">
                            <h4 class="font-serif text-xl mb-2 text-brand-black">Tôi có thể bảo hành sản phẩm ở đâu?</h4>
                            <p class="text-gray-500 text-sm font-light leading-relaxed">Các sản phẩm đồ da và trang sức của L'AURA được bảo hành trọn đời tại bất kỳ cửa hàng chính thức nào của chúng tôi trên toàn cầu.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching logic
    const tabBtns = document.querySelectorAll('.support-tab-btn');
    const tabContents = document.querySelectorAll('.support-tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Reset buttons
            tabBtns.forEach(b => {
                b.classList.remove('bg-brand-black', 'text-white');
                b.classList.add('text-gray-600', 'hover:bg-gray-100');
            });
            // Activate clicked button
            this.classList.remove('text-gray-600', 'hover:bg-gray-100');
            this.classList.add('bg-brand-black', 'text-white');

            // Hide all tabs
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('block');
            });
            // Show target tab
            const targetId = this.getAttribute('data-target');
            const targetTab = document.getElementById(targetId);
            targetTab.classList.remove('hidden');
            targetTab.classList.add('block');
        });
    });

    // Handle AJAX Contact Form
    const contactForm = document.getElementById('contactForm');
    const msgBox = document.getElementById('form-message');
    const btnSubmit = document.getElementById('btn-submit');
    const btnText = btnSubmit.querySelector('span');

    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // UI Loading
        btnSubmit.disabled = true;
        btnText.textContent = 'Đang gửi...';
        msgBox.classList.add('hidden');
        msgBox.className = 'p-4 rounded mb-6 text-sm flex items-center gap-3 hidden'; // Reset classes

        const data = {
            name: document.getElementById('c_name').value,
            email: document.getElementById('c_email').value,
            message: document.getElementById('c_message').value
        };

        fetch('ajax/process_contact.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            msgBox.classList.remove('hidden');
            if (result.status === 'success') {
                msgBox.classList.add('bg-green-100', 'text-green-700');
                msgBox.innerHTML = '<i class="fas fa-check-circle"></i> ' + result.message;
                contactForm.reset();
            } else {
                msgBox.classList.add('bg-red-100', 'text-red-700');
                msgBox.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + result.message;
            }
        })
        .catch(error => {
            msgBox.classList.remove('hidden');
            msgBox.classList.add('bg-red-100', 'text-red-700');
            msgBox.innerHTML = '<i class="fas fa-exclamation-circle"></i> Có lỗi xảy ra, vui lòng thử lại.';
        })
        .finally(() => {
            btnSubmit.disabled = false;
            btnText.textContent = 'Gửi Tin Nhắn';
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
