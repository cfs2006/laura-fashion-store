function addToCart(productId, quantity = 1) {
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.redirect) {
            // Chưa login, chuyển hướng
            window.location.href = data.redirect;
            return;
        }
        
        if (data.status === 'success') {
            showToast(data.message, 'success');
            // Cập nhật badge
            updateCartBadge(data.cart_count);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Đã có lỗi xảy ra. Vui lòng thử lại.', 'error');
    });
}

function updateCartBadge(count) {
    const badge = document.getElementById('cart-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
}

function showToast(message, type) {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    const icon = toast.querySelector('i');
    
    toastMessage.textContent = message;
    
    if (type === 'success') {
        toast.classList.replace('text-red-500', 'text-brand-gold');
        icon.className = 'fas fa-check-circle';
    } else {
        toast.classList.add('text-red-500');
        toast.classList.remove('text-brand-gold');
        icon.className = 'fas fa-exclamation-circle';
    }
    
    // Hiện toast
    toast.classList.remove('translate-y-20', 'opacity-0');
    
    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3000);
}

function handleNewsletterSignup() {
    const form = document.getElementById('newsletter-form');
    const emailInput = document.getElementById('newsletter-email');
    const submitButton = document.getElementById('newsletter-submit');
    const cooldownKey = 'newsletterCooldownUntil';

    if (!form || !emailInput || !submitButton) {
        return;
    }

    function clearCooldown() {
        submitButton.classList.remove('hidden');
        submitButton.disabled = false;
    }

    function startCooldown(durationSeconds = 10) {
        const cooldownUntil = Date.now() + (durationSeconds * 1000);
        localStorage.setItem(cooldownKey, String(cooldownUntil));
        submitButton.classList.add('hidden');
        submitButton.disabled = true;

        setTimeout(() => {
            const savedCooldownUntil = parseInt(localStorage.getItem(cooldownKey) || '0', 10);
            if (savedCooldownUntil <= Date.now()) {
                localStorage.removeItem(cooldownKey);
                clearCooldown();
            }
        }, durationSeconds * 1000);
    }

    const savedCooldownUntil = parseInt(localStorage.getItem(cooldownKey) || '0', 10);
    if (savedCooldownUntil > Date.now()) {
        submitButton.classList.add('hidden');
        submitButton.disabled = true;
        setTimeout(() => {
            const currentCooldownUntil = parseInt(localStorage.getItem(cooldownKey) || '0', 10);
            if (currentCooldownUntil <= Date.now()) {
                localStorage.removeItem(cooldownKey);
                clearCooldown();
            }
        }, savedCooldownUntil - Date.now());
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const email = emailInput.value.trim();
        if (!email) {
            showToast('Vui lòng nhập email.', 'error');
            return;
        }

        const cooldownUntil = parseInt(localStorage.getItem(cooldownKey) || '0', 10);
        if (cooldownUntil > Date.now()) {
            showToast('Vui lòng chờ 10 giây trước khi đăng ký lại.', 'error');
            return;
        }

        form.reset();
        showToast('Đăng ký thành công. Vui lòng kiểm tra cả hộp thư đến và thư rác.', 'success');
        startCooldown(10);

        void fetch('ajax/subscribe_newsletter.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                console.error('Newsletter error:', data.message || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Newsletter error:', error);
        });
    });
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function handleSiteSearch() {
    const widget = document.getElementById('search-widget');
    const toggleBtn = document.getElementById('search-toggle-btn');
    const panel = document.getElementById('search-panel');
    const closeBtn = document.getElementById('search-close-btn');
    const input = document.getElementById('search-input');
    const results = document.getElementById('search-results');
    const status = document.getElementById('search-status');

    if (!widget || !toggleBtn || !panel || !closeBtn || !input || !results || !status) {
        return;
    }

    let searchTimer = null;

    function renderProducts(products) {
        if (!products || products.length === 0) {
            results.innerHTML = '';
            status.textContent = 'Không tìm thấy sản phẩm phù hợp.';
            return;
        }

        status.textContent = input.value.trim() ? 'Kết quả tìm kiếm' : 'Sản phẩm mới nhất';
        results.innerHTML = products.map(product => {
            const name = escapeHtml(product.name || '');
            const imageUrl = escapeHtml(product.image_url || '');
            const id = encodeURIComponent(product.id);

            return `
                <a href="product_detail.php?id=${id}" data-product-id="${id}" class="search-result-item group flex items-center gap-3 bg-white border border-gray-100 rounded-2xl p-2 shadow-sm hover:shadow-lg transition-all">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 shrink-0 bg-gray-100 overflow-hidden rounded-xl">
                        <img
                            src="${imageUrl}"
                            alt="${name}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1584917865442-de89df76afd3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1035&q=80';"
                        >
                    </div>
                    <div class="min-w-0 flex-1 pr-2">
                        <h3 class="text-sm sm:text-[15px] font-medium text-brand-black leading-snug truncate">${name}</h3>
                    </div>
                </a>
            `;
        }).join('');
    }

    function fetchProducts(query = '') {
        status.textContent = query.trim() ? 'Đang tìm kiếm...' : 'Đang tải sản phẩm...';

        fetch(`ajax/search_products.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderProducts(data.products || []);
                } else {
                    results.innerHTML = '';
                    status.textContent = data.message || 'Không thể tìm kiếm sản phẩm.';
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                results.innerHTML = '';
                status.textContent = 'Không thể tìm kiếm sản phẩm.';
            });
    }

    function openPanel() {
        panel.classList.remove('hidden');
        setTimeout(() => input.focus(), 50);
    }

    function closePanel() {
        panel.classList.add('hidden');
        input.value = '';
    }

    toggleBtn.addEventListener('click', function () {
        if (panel.classList.contains('hidden')) {
            openPanel();
            fetchProducts('');
        } else {
            closePanel();
        }
    });

    closeBtn.addEventListener('click', closePanel);

    input.addEventListener('input', function () {
        if (searchTimer) {
            clearTimeout(searchTimer);
        }

        const query = input.value.trim();
        searchTimer = setTimeout(() => {
            fetchProducts(query);
        }, 200);
    });

    results.addEventListener('click', function (event) {
        const item = event.target.closest('.search-result-item');
        if (!item) {
            return;
        }

        const productId = item.dataset.productId;
        if (!productId) {
            return;
        }

        event.preventDefault();
        window.location.href = `product_detail.php?id=${encodeURIComponent(productId)}`;
    });

    document.addEventListener('click', function (event) {
        if (panel.classList.contains('hidden')) {
            return;
        }

        if (!widget.contains(event.target)) {
            closePanel();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !panel.classList.contains('hidden')) {
            closePanel();
        }
    });
}

// Confirm before removing item from cart
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a.cart-remove').forEach(function(el) {
        el.addEventListener('click', function(e) {
            const ok = confirm('Bạn có chắc muốn xóa mặt hàng này khỏi giỏ hàng không?');
            if (!ok) {
                e.preventDefault();
            }
        });
    });

    handleNewsletterSignup();
    handleSiteSearch();
});
