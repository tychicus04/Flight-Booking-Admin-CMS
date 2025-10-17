-- ===================================
-- DATABASE SCHEMA FOR FLIGHT BOOKING ADMIN CMS
-- ===================================

CREATE DATABASE IF NOT EXISTS flight_booking_admin 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE flight_booking_admin;

-- Bảng Vai trò (Roles)
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng Users (Admin)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    avatar VARCHAR(255) DEFAULT NULL,
    role_id INT NOT NULL,
    status ENUM('active', 'locked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Bảng Khách hàng
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    id_number VARCHAR(20),
    date_of_birth DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng Ví (Wallets)
CREATE TABLE wallets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL UNIQUE,
    balance DECIMAL(15,2) DEFAULT 0.00,
    last_transaction_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Bảng Lịch sử giao dịch
CREATE TABLE wallet_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    wallet_id INT NOT NULL,
    transaction_type ENUM('deposit', 'payment', 'refund', 'admin_adjust') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    balance_before DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,
    description TEXT,
    reference_id INT NULL COMMENT 'ID của booking nếu liên quan',
    created_by INT NULL COMMENT 'ID admin thực hiện (nếu là admin_adjust)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Bảng Bookings (Đơn đặt vé)
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_code VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    payment_status ENUM('paid', 'unpaid', 'refunded') DEFAULT 'unpaid',
    booking_status ENUM('confirmed', 'cancelled', 'pending') DEFAULT 'pending',
    created_by INT NOT NULL,
    booking_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Bảng Chi tiết chuyến bay trong booking
CREATE TABLE booking_flights (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    flight_number VARCHAR(20) NOT NULL,
    departure_city VARCHAR(100) NOT NULL,
    arrival_city VARCHAR(100) NOT NULL,
    departure_date DATE NOT NULL,
    departure_time TIME NOT NULL,
    arrival_date DATE NOT NULL,
    arrival_time TIME NOT NULL,
    airline VARCHAR(100),
    seat_class ENUM('economy', 'business', 'first') DEFAULT 'economy',
    price DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Insert dữ liệu mẫu
INSERT INTO roles (name, description) VALUES 
('Super Admin', 'Quản trị viên cấp cao nhất'),
('Admin', 'Quản trị viên'),
('Staff', 'Nhân viên');

-- Mật khẩu: admin123 (đã được hash bằng password_hash)
INSERT INTO users (username, password, full_name, email, role_id, status) VALUES 
('admin', '$2a$12$rQplaO5v83GsLMNWNdn.vuEtS/mh8t4D0D8LtuG/mURhLZzuxB4f6', 'Administrator', 'admin@example.com', 1, 'active');

-- Dữ liệu mẫu khách hàng
INSERT INTO customers (full_name, email, phone, id_number) VALUES 
('Nguyễn Văn A', 'nguyenvana@example.com', '0901234567', '123456789'),
('Trần Thị B', 'tranthib@example.com', '0912345678', '987654321');

-- Tạo ví cho khách hàng
INSERT INTO wallets (customer_id, balance) VALUES 
(1, 5000000.00),
(2, 3000000.00);