# 🛫 Flight Booking Admin CMS

Hệ thống quản trị (Admin CMS) hoàn chỉnh để quản lý đặt vé máy bay, được xây dựng bằng PHP thuần, MySQL, Bootstrap 5.

## ✨ Tính năng chính

### 1. 👥 Quản lý Users
- ✅ CRUD đầy đủ cho tài khoản admin
- ✅ Phân quyền theo vai trò (Super Admin, Admin, Staff)
- ✅ Upload và quản lý avatar
- ✅ Khóa/Mở khóa tài khoản
- ✅ Đổi mật khẩu
- ✅ Tìm kiếm và lọc nâng cao

### 2. 🎫 Quản lý Bookings
- ✅ Tạo booking với nhiều chặng bay
- ✅ Quản lý thông tin khách hàng
- ✅ Theo dõi trạng thái thanh toán
- ✅ Theo dõi trạng thái booking
- ✅ In vé PDF
- ✅ Hủy booking
- ✅ Tìm kiếm và lọc theo nhiều tiêu chí

### 3. 💰 Quản lý Ví điện tử
- ✅ Xem danh sách ví của khách hàng
- ✅ Kiểm tra số dư hiện tại
- ✅ Lịch sử giao dịch chi tiết
- ✅ Admin điều chỉnh số dư (cộng/trừ tiền)
- ✅ Thống kê tổng tiền vào/ra
- ✅ Lọc giao dịch theo loại và thời gian

### 4. 📊 Dashboard
- ✅ Thống kê tổng quan hệ thống
- ✅ Biểu đồ trạng thái booking
- ✅ Danh sách booking gần đây
- ✅ Cảnh báo booking chờ xử lý

### 5. 🎨 Giao diện
- ✅ Sidebar có thể thu gọn
- ✅ Responsive trên mọi thiết bị
- ✅ **Tùy chỉnh ẩn/hiện cột trong bảng** (Column Visibility Toggle)
  - Ẩn/hiện từng cột riêng biệt
  - Lưu cấu hình vào localStorage
  - Nút "Hiện tất cả" và "Đặt lại"
  - Áp dụng cho bảng Users và Bookings
- ✅ Bộ lọc mạnh mẽ
- ✅ Phân trang thông minh
- ✅ Thông báo flash message

## 📋 Yêu cầu hệ thống

- **PHP**: >= 7.4
- **MySQL**: >= 5.7
- **Apache/Nginx** với mod_rewrite
- **XAMPP/WAMP/LAMP** (khuyến nghị cho local development)

## 🚀 Hướng dẫn Cài đặt

### Bước 1: Clone/Download dự án

```bash
# Tải về và giải nén vào thư mục htdocs (XAMPP) hoặc www (WAMP)
# Ví dụ: C:\xampp\htdocs\flight_booking_admin
```

### Bước 2: Tạo Database

1. Mở **phpMyAdmin** (http://localhost/phpmyadmin)
2. Tạo database mới tên `flight_booking_admin`
3. Import file `database.sql` vào database vừa tạo

**Hoặc** chạy trực tiếp SQL:

```sql
-- Xem nội dung trong artifact "Database Schema"
-- Copy và chạy toàn bộ SQL
```

### Bước 3: Cấu hình kết nối Database

Mở file `config/database.php` và chỉnh sửa thông tin kết nối:

```php
private $host = "localhost";
private $db_name = "flight_booking_admin";
private $username = "root";
private $password = ""; // Để trống nếu dùng XAMPP
```

### Bước 4: Cấu hình BASE_URL

Mở file `config/constants.php` và chỉnh sửa:

```php
define('BASE_URL', 'http://localhost/flight_booking_admin/');
```

### Bước 5: Tạo thư mục uploads

```bash
# Tạo thư mục và phân quyền
mkdir uploads
mkdir uploads/avatars
chmod 755 uploads -R
```

### Bước 6: Khởi động XAMPP

1. Mở **XAMPP Control Panel**
2. Start **Apache** và **MySQL**
3. Truy cập: `http://localhost/flight_booking_admin`

## 🔑 Đăng nhập

**Tài khoản mặc định:**
- Username: `admin`
- Password: `admin123`

⚠️ **Khuyến nghị**: Đổi mật khẩu ngay sau lần đăng nhập đầu tiên!

## 📁 Cấu trúc thư mục

```
flight_booking_admin/
│
├── config/                 # Cấu hình
│   ├── database.php       # Kết nối DB
│   └── constants.php      # Hằng số & helpers
│
├── includes/              # Layout chung
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
│
├── assets/               # Tài nguyên tĩnh
│   ├── css/             # CSS files
│   ├── js/              # JavaScript files
│   └── images/          # Hình ảnh
│
├── modules/             # Các module chức năng
│   ├── users/          # Quản lý users
│   ├── bookings/       # Quản lý bookings
│   └── wallet/         # Quản lý ví
│
├── auth/               # Xác thực
│   ├── login.php
│   ├── logout.php
│   └── check_auth.php
│
├── uploads/            # File uploads
│   └── avatars/       # Avatar users
│
├── index.php          # Dashboard
└── database.sql       # SQL schema
```

## 🎯 Hướng dẫn Sử dụng

### Quản lý Users

1. **Thêm User mới:**
   - Vào menu "Quản lý User"
   - Click "Thêm User mới"
   - Điền đầy đủ thông tin
   - Upload avatar (tùy chọn)
   - Click "Lưu"

2. **Chỉnh sửa User:**
   - Click vào menu 3 chấm ở cột "Hành động"
   - Chọn "Chỉnh sửa"
   - Cập nhật thông tin
   - Click "Cập nhật"

3. **Đổi mật khẩu:**
   - Click vào menu 3 chấm
   - Chọn "Đổi mật khẩu"
   - Nhập mật khẩu mới
   - Xác nhận

### Quản lý Bookings

1. **Tạo Booking mới:**
   - Vào menu "Quản lý Booking"
   - Click "Tạo Booking mới"
   - **Bước 1**: Điền thông tin chung (mã booking, ngày đặt, trạng thái)
   - **Bước 2**: Chọn khách hàng có sẵn hoặc nhập thông tin mới
   - **Bước 3**: Thêm chi tiết chuyến bay (có thể thêm nhiều chặng)
   - Kiểm tra tổng tiền
   - Click "Tạo Booking"

2. **Xem chi tiết Booking:**
   - Click vào mã booking trong danh sách
   - Xem đầy đủ thông tin booking và chuyến bay
   - Click "In vé" để xuất PDF

3. **Hủy Booking:**
   - Click menu 3 chấm
   - Chọn "Hủy booking"
   - Xác nhận

### Quản lý Ví

1. **Xem danh sách ví:**
   - Vào menu "Quản lý Ví"
   - Xem số dư của tất cả khách hàng
   - Tìm kiếm theo tên hoặc khoảng số dư

2. **Xem lịch sử giao dịch:**
   - Click icon lịch sử (đồng hồ)
   - Xem tất cả giao dịch của khách hàng
   - Lọc theo loại giao dịch và thời gian

3. **Điều chỉnh số dư:**
   - Click icon điều chỉnh (+/-)
   - Chọn loại: Cộng tiền hoặc Trừ tiền
   - Nhập số tiền
   - Nhập lý do điều chỉnh (bắt buộc)
   - Xác nhận

## 🔧 Tùy chỉnh

### Thay đổi số lượng bản ghi mỗi trang

Mở file `config/constants.php`:

```php
define('RECORDS_PER_PAGE', 20); // Thay đổi số này
```

### Thay đổi màu sắc giao diện

Mở file `assets/css/style.css`:

```css
:root {
    --primary-color: #4e73df;    /* Màu chính */
    --secondary-color: #858796;  /* Màu phụ */
    --success-color: #1cc88a;    /* Màu thành công */
    --danger-color: #e74a3b;     /* Màu nguy hiểm */
    --warning-color: #f6c23e;    /* Màu cảnh báo */
}
```

### Thêm vai trò mới

1. Thêm vào bảng `roles` trong database
2. Cập nhật constants trong `config/constants.php`

## 🐛 Xử lý lỗi thường gặp

### Lỗi kết nối database
```
Connection Error: SQLSTATE[HY000] [1045] Access denied
```
**Giải pháp**: Kiểm tra lại username/password trong `config/database.php`

### Lỗi upload file
```
Warning: move_uploaded_file(): failed to open stream
```
**Giải pháp**: 
- Tạo thư mục `uploads/avatars/`
- Phân quyền 755 cho thư mục uploads

### Lỗi session
```
Warning: session_start(): Cannot send session cookie
```
**Giải pháp**: Đảm bảo `session_start()` được gọi trước bất kỳ output nào

### Lỗi CSS/JS không load
**Giải pháp**: Kiểm tra lại `BASE_URL` trong `config/constants.php`

## 📝 Database Schema

### Bảng chính

1. **users** - Tài khoản admin
2. **roles** - Vai trò
3. **customers** - Khách hàng
4. **wallets** - Ví điện tử
5. **wallet_transactions** - Giao dịch ví
6. **bookings** - Đơn đặt vé
7. **booking_flights** - Chi tiết chuyến bay

## 🔐 Bảo mật

- ✅ Password được hash bằng `password_hash()`
- ✅ Prepared statements để tránh SQL Injection
- ✅ Session-based authentication
- ✅ CSRF protection (cần implement thêm cho production)
- ✅ Input validation và sanitization
- ✅ File upload validation

## 🚧 TODO (Cải tiến trong tương lai)

- [ ] Thêm CSRF token cho forms
- [ ] Export dữ liệu ra Excel/PDF
- [ ] Email notifications
- [ ] Activity logs
- [ ] 2FA authentication
- [ ] API RESTful
- [ ] Dark mode
- [ ] Multi-language support

## 📞 Hỗ trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra lại hướng dẫn cài đặt
2. Xem phần xử lý lỗi thường gặp
3. Kiểm tra error logs trong PHP

## 📄 License

MIT License - Tự do sử dụng cho mục đích cá nhân và thương mại.

## 👨‍💻 Tác giả

Được phát triển với ❤️ bởi Tychicus Nguyen

---

**Lưu ý**: Đây là phiên bản development. Trước khi deploy lên production, cần:
- Bật error reporting thành production mode
- Thêm HTTPS
- Cấu hình database credentials an toàn hơn
- Thêm backup tự động
- Implement rate limiting
- Optimize performance