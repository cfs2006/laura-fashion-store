<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

$host = 'localhost';
$db_name = 'laura_db';
$username = 'root'; 
$password = ''; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->exec("SET time_zone = '+07:00'");
    
    require_once __DIR__ . '/includes/helpers.php';
    ensure_user_profile_schema($conn);
} catch(PDOException $e) {
    // Không hiện chi tiết lỗi DB ra ngoài ở môi trường production để bảo mật
    error_log("Connection failed: " . $e->getMessage()); // Ghi log thay vì hiển thị
    die("Lỗi kết nối cơ sở dữ liệu. Vui lòng kiểm tra lại cấu hình hoặc thử lại sau.");
}
?>
