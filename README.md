# L'AURA - Cửa Hàng Thời Trang Trực Tuyến

L'AURA là một dự án đồ án website thương mại điện tử chuyên cung cấp các mặt hàng thời trang cao cấp. Website được phát triển trên nền tảng **PHP thuần (Vanilla PHP), MySQL, HTML/CSS (Tailwind CSS) và JavaScript**.

## 🌟 Tính Năng Nổi Bật

### 1. Dành cho Khách hàng (Frontend)
- **Giao diện hiện đại & Chuẩn Mobile Responsive:** Được thiết kế tối giản, sang trọng với Tailwind CSS, tương thích hoàn toàn trên cả Desktop và Mobile.
- **Trải nghiệm mua sắm:** Xem danh sách sản phẩm, chi tiết sản phẩm, lọc theo danh mục, thêm vào giỏ hàng và đặt hàng.
- **Quản lý Tài khoản:** Đăng ký, Đăng nhập, Quên mật khẩu (tự động reset không cần email), xem lịch sử đơn hàng, cập nhật hồ sơ và ảnh đại diện.
- **AI Chatbot Tích hợp:** Trợ lý ảo thông minh "Laura" tư vấn khách hàng trực tiếp, được tích hợp qua Google Gemini API.

### 2. Dành cho Quản trị viên (Backend)
- **Hệ thống Phân quyền Chặt chẽ:** Chia làm 3 cấp độ `Root` (Cao nhất), `Staff` (Nhân viên), và `Customer` (Khách hàng).
- **Quản lý Đơn hàng:** Xem, duyệt, hủy và cập nhật trạng thái đơn hàng.
- **Quản lý Người dùng:** Thêm, sửa, xóa tài khoản (Staff không có quyền xóa các tài khoản Root hoặc Staff khác). Đổi mật khẩu định kỳ cho tài khoản.
- **Quản lý Sản phẩm:** Thêm, sửa, xóa sản phẩm và danh mục một cách trực quan.

### 3. Tăng cường Bảo mật (Security)
- **Mã hóa Mật khẩu:** Tất cả mật khẩu đều được băm bằng thuật toán `Bcrypt` của PHP.
- **Chống SQL Injection:** 100% truy vấn CSDL đều sử dụng `PDO Prepared Statements`.
- **Chống Đánh cắp Dữ liệu:** Tích hợp file `.htaccess` chặn truy cập trực tiếp vào các file cấu hình (`.sql`, `.env`, `.json`) và chặn Directory Listing.
- **Bảo vệ Giao diện:** Javascript vô hiệu hóa Phím tắt F12, Ctrl+U, Click chuột phải để hạn chế việc soi mã nguồn.

---

## 🚀 Hướng Dẫn Cài Đặt

### 1. Yêu cầu Hệ thống
- PHP >= 8.0
- MySQL hoặc MariaDB
- XAMPP / MAMP / WAMP (nếu chạy trên Localhost)

### 2. Các bước triển khai

**Bước 1: Clone dự án**
```bash
git clone https://github.com/your-username/laura-fashion-store.git
```
Chuyển toàn bộ mã nguồn vào thư mục `htdocs` (nếu dùng XAMPP).

**Bước 2: Cài đặt Database**
1. Mở `phpMyAdmin`.
2. Import file `database.sql` đi kèm trong thư mục gốc. Hệ thống sẽ tự động tạo Database tên `laura_db` (nếu chưa có) cùng toàn bộ các bảng và dữ liệu mẫu.
3. **Tài khoản quản trị mặc định:**
   - **Tài khoản (SĐT):** `root`
   - **Mật khẩu:** `123`

**Bước 3: Cấu hình kết nối**
Mở file `db_connect.php` và thay đổi các thông số kết nối cho phù hợp với môi trường của bạn (nếu chạy local XAMPP thì đã được thiết lập sẵn là `localhost` - `root`).

**Bước 4: Cấu hình API Key (AI Chatbot)**
Để Chatbot hoạt động được, bạn cần cung cấp một khóa API của Google Gemini:
1. Mở file `chatbot_api.php`.
2. Tìm biến `$api_key = "YOUR_GEMINI_API_KEY_HERE";`
3. Thay thế bằng API Key thật của bạn (nhận miễn phí tại Google AI Studio).

**Bước 5: Cấu hình Email (Nếu dùng tính năng Đăng ký nhận bản tin)**
1. Mở file `config/newsletter_mail.php`.
2. Thay thế `your_email@gmail.com` và `your_app_password` bằng thông tin App Password Gmail thực tế của bạn.

---

## 🎨 Tác giả
Dự án được xây dựng phục vụ cho đồ án môn học Phát triển Ứng dụng Web. Mọi đóng góp, báo lỗi vui lòng tạo [Issues](https://github.com/) trong repository.
