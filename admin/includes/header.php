<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Middleware: Must be Admin or Staff
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: ../auth.php");
    exit();
}
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'AURA Admin Panel</title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' fill='%230a0a0a'/%3E%3Ctext x='50' y='55' font-family='serif' font-size='64' font-weight='bold' fill='%23D4AF37' text-anchor='middle' dominant-baseline='middle'%3EL%3C/text%3E%3C/svg%3E" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            black: '#1a1a1a',
                            gold: '#D4AF37',
                            beige: '#F5F5DC',
                            white: '#FAFAFA'
                        }
                    },
                    fontFamily: {
                        logo: ['"Playfair Display"', 'serif'],
                        serif: ['Roboto', 'sans-serif'],
                        sans: ['Roboto', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/security.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased flex h-screen overflow-hidden">
<?php require_once 'sidebar.php'; ?>
<!-- Main Content Container -->
<div class="flex-1 flex flex-col overflow-y-auto">
    <!-- Top Header -->
    <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 md:px-8 z-10 sticky top-0">
        <div class="flex items-center gap-4">
            <button id="admin-menu-btn" class="md:hidden text-gray-500 hover:text-brand-gold focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="font-serif text-lg md:text-xl font-bold text-gray-700 hidden sm:block">Trang Quản Trị</h2>
        </div>
        <div class="flex items-center gap-3 md:gap-4">
            <a href="../index.php" class="text-sm text-gray-500 hover:text-brand-gold hidden sm:flex items-center gap-2" target="_blank"><i class="fas fa-external-link-alt"></i> Xem Website</a>
            <a href="../index.php" class="sm:hidden text-gray-500 hover:text-brand-gold" target="_blank" title="Xem Website"><i class="fas fa-external-link-alt"></i></a>
            <div class="w-px h-6 bg-gray-200"></div>
            <a href="../profile.php" class="flex items-center gap-2 hover:text-brand-gold transition-colors">
                <img src="<?= htmlspecialchars(user_avatar_url($_SESSION['user_avatar'] ?? '', $_SESSION['user_name'] ?? 'Admin')) ?>" alt="Admin" class="w-8 h-8 rounded-full border border-gray-200 object-cover bg-brand-black">
                <span class="text-sm font-medium hidden sm:block"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            </a>
            <a href="../auth.php?action=logout" class="text-gray-500 hover:text-red-500 ml-2" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>
    <main class="p-4 md:p-8">
