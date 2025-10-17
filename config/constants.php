
<?php
// ============================================
// FILE: config/constants.php
// ============================================

// Đường dẫn gốc
define('BASE_URL', 'http://localhost/flight_booking_admin/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Vai trò
define('ROLE_SUPER_ADMIN', 1);
define('ROLE_ADMIN', 2);
define('ROLE_STAFF', 3);

// Trạng thái
define('STATUS_ACTIVE', 'active');
define('STATUS_LOCKED', 'locked');

// Payment status
define('PAYMENT_PAID', 'paid');
define('PAYMENT_UNPAID', 'unpaid');
define('PAYMENT_REFUNDED', 'refunded');

// Booking status
define('BOOKING_CONFIRMED', 'confirmed');
define('BOOKING_CANCELLED', 'cancelled');
define('BOOKING_PENDING', 'pending');

// Transaction types
define('TRANS_DEPOSIT', 'deposit');
define('TRANS_PAYMENT', 'payment');
define('TRANS_REFUND', 'refund');
define('TRANS_ADMIN_ADJUST', 'admin_adjust');

// Phân trang
define('RECORDS_PER_PAGE', 20);

// Hàm helper
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

function formatDate($date) {
    return date('d-m-Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d-m-Y H:i', strtotime($datetime));
}

function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge bg-success">Hoạt động</span>',
        'locked' => '<span class="badge bg-danger">Bị khóa</span>',
        'paid' => '<span class="badge bg-success">Đã thanh toán</span>',
        'unpaid' => '<span class="badge bg-warning">Chưa thanh toán</span>',
        'refunded' => '<span class="badge bg-info">Đã hoàn tiền</span>',
        'confirmed' => '<span class="badge bg-success">Đã xác nhận</span>',
        'cancelled' => '<span class="badge bg-danger">Đã hủy</span>',
        'pending' => '<span class="badge bg-warning">Chờ xử lý</span>',
    ];
    return $badges[$status] ?? $status;
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        redirect('auth/login.php');
    }
}

function hasPermission($required_roles = []) {
    if (empty($required_roles)) {
        return true;
    }
    return in_array($_SESSION['role_id'], $required_roles);
}
?>