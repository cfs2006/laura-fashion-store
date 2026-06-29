// Đăng ký ScrollTrigger với GSAP
gsap.registerPlugin(ScrollTrigger);

// 1. Header scroll effect
const header = document.getElementById('header');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        header.classList.add('header-scrolled');
    } else {
        header.classList.remove('header-scrolled');
    }
});

// Sidebar Menu Logic
const menuBtn = document.getElementById('menu-btn');
const closeMenuBtn = document.getElementById('close-menu-btn');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');

if (menuBtn && closeMenuBtn && sidebar && sidebarOverlay) {
    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        sidebarOverlay.classList.remove('opacity-0', 'pointer-events-none');
        document.body.style.overflow = 'hidden'; // Ngăn cuộn trang khi mở menu
    }
    
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    }

    menuBtn.addEventListener('click', openSidebar);
    closeMenuBtn.addEventListener('click', closeSidebar);
    sidebarOverlay.addEventListener('click', closeSidebar);
}

// 2. Hero Section Animations
const heroTimeline = gsap.timeline();

// Hiệu ứng Parallax cho background hero khi cuộn
gsap.to('.hero-bg', {
    yPercent: 30,
    ease: 'none',
    scrollTrigger: {
        trigger: '.hero',
        start: 'top top',
        end: 'bottom top',
        scrub: true
    }
});

// Text reveal animation khi load trang
heroTimeline.to('.hero-title span', {
    y: 0,
    opacity: 1,
    duration: 1.2,
    stagger: 0.2,
    ease: 'power4.out',
    delay: 0.2
})
.to('.hero-subtitle', {
    opacity: 1,
    y: -10,
    duration: 1,
    ease: 'power2.out'
}, "-=0.8")
.to('.hero-btn', {
    opacity: 1,
    duration: 1,
    ease: 'power2.out'
}, "-=0.6");

// 3. Brand Story Animation (Scroll trượt lên và hiện rõ dần)
gsap.to('.story-text', {
    y: 0,
    opacity: 1,
    duration: 1.5,
    ease: 'power3.out',
    scrollTrigger: {
        trigger: '.brand-story',
        start: 'top 70%', // Bắt đầu khi thẻ div chạm đến 70% chiều cao màn hình
    }
});

gsap.to('.story-line', {
    scaleY: 1,
    duration: 1,
    ease: 'power3.inOut',
    scrollTrigger: {
        trigger: '.brand-story',
        start: 'top 50%',
    }
});

// 4. Categories Navigation 3D Tilt Effect
// Sử dụng thư viện VanillaTilt cho hiệu ứng nghiêng 3D mượt mà
VanillaTilt.init(document.querySelectorAll(".category-card"), {
    max: 5,        // Góc nghiêng tối đa
    speed: 400,    // Tốc độ hồi phục
    glare: true,   // Hiệu ứng ánh sáng bóng
    "max-glare": 0.2, // Độ chói tối đa
});

// 5. Featured Collection - Swiper Carousel
const swiper = new Swiper('.featured-swiper', {
    loop: true, // Vòng lặp vô tận
    slidesPerView: 'auto', // Tự động căn chỉnh kích thước theo nội dung
    spaceBetween: 40, // Khoảng cách giữa các sản phẩm (tương đương gap-10 = 40px)
    grabCursor: true, // Đổi con trỏ chuột thành hình bàn tay để kéo thả dễ dàng
    navigation: {
        nextEl: '#next-btn',
        prevEl: '#prev-btn',
    },
    speed: 800, // Tốc độ cuộn mượt mà (0.8 giây)
});

// 5.5 Accessories Collection - Swiper Carousel
const accSwiper = new Swiper('.accessory-swiper', {
    loop: true,
    slidesPerView: 'auto',
    spaceBetween: 40,
    grabCursor: true,
    navigation: {
        nextEl: '#next-acc-btn',
        prevEl: '#prev-acc-btn',
    },
    speed: 800,
});

// 6. Parallax Banner Image & Text
gsap.to('.banner-bg', {
    yPercent: 20, // Kéo hình nền xuống khi cuộn
    ease: 'none',
    scrollTrigger: {
        trigger: '.parallax-banner',
        start: 'top bottom',
        end: 'bottom top',
        scrub: true
    }
});

gsap.to('.parallax-text', {
    yPercent: -50, // Kéo chữ lên trên tạo độ sâu 3D ngược với hình nền
    ease: 'none',
    scrollTrigger: {
        trigger: '.parallax-banner',
        start: 'top bottom',
        end: 'bottom top',
        scrub: true
    }
});
