<?php
// ============================================
// FILE: modules/bookings/add.php
// ============================================
$page_title = "Tạo Booking Mới";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get customers
$customers = $db->query("SELECT id, full_name, email FROM customers ORDER BY full_name")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'process.php';
    if (empty($errors)) {
        redirect('modules/bookings/index.php');
    }
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Tạo Booking Mới</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <!-- Step Indicator -->
                <div class="form-steps mb-4">
                    <div class="form-step active">
                        <div class="form-step-number">1</div>
                        <div class="form-step-label">Thông tin chung</div>
                    </div>
                    <div class="form-step">
                        <div class="form-step-number">2</div>
                        <div class="form-step-label">Thông tin khách</div>
                    </div>
                    <div class="form-step">
                        <div class="form-step-number">3</div>
                        <div class="form-step-label">Chuyến bay</div>
                    </div>
                </div>

                <!-- Section 1: General Info -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2 mb-3">1. Thông tin chung</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Mã đặt chỗ</label>
                                <input type="text" name="booking_code" class="form-control" 
                                       value="<?= $_POST['booking_code'] ?? strtoupper(uniqid('BK')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Ngày đặt</label>
                                <input type="date" name="booking_date" class="form-control" 
                                       value="<?= $_POST['booking_date'] ?? date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Trạng thái thanh toán</label>
                                <select name="payment_status" class="form-select" required>
                                    <option value="unpaid" selected>Chưa thanh toán</option>
                                    <option value="paid">Đã thanh toán</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Trạng thái booking</label>
                                <select name="booking_status" class="form-select" required>
                                    <option value="pending">Chờ xử lý</option>
                                    <option value="confirmed" selected>Đã xác nhận</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Customer Info -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2 mb-3">2. Thông tin khách hàng</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Chọn khách hàng có sẵn</label>
                                <select name="customer_id" class="form-select" id="customerSelect">
                                    <option value="">-- Hoặc nhập thông tin mới --</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>">
                                            <?= $customer['full_name'] ?> - <?= $customer['email'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="newCustomerFields">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Họ và tên</label>
                                    <input type="text" name="customer_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="customer_email" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="tel" name="customer_phone" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số CMND/CCCD</label>
                                    <input type="text" name="customer_id_number" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Flight Details -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2 mb-3">
                        3. Thông tin chuyến bay
                        <button type="button" class="btn btn-sm btn-outline-primary float-end" onclick="addFlight()">
                            <i class="bi bi-plus-lg"></i> Thêm chặng
                        </button>
                    </h5>
                    <div id="flightsContainer">
                        <div class="flight-row border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label required">Số hiệu chuyến bay</label>
                                        <input type="text" name="flights[0][flight_number]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label required">Hãng bay</label>
                                        <input type="text" name="flights[0][airline]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label required">Nơi đi</label>
                                        <input type="text" name="flights[0][departure_city]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label required">Nơi đến</label>
                                        <input type="text" name="flights[0][arrival_city]" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label required">Ngày đi</label>
                                        <input type="date" name="flights[0][departure_date]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label required">Giờ đi</label>
                                        <input type="time" name="flights[0][departure_time]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label required">Ngày đến</label>
                                        <input type="date" name="flights[0][arrival_date]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label required">Giờ đến</label>
                                        <input type="time" name="flights[0][arrival_time]" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">Hạng ghế</label>
                                        <select name="flights[0][seat_class]" class="form-select" required>
                                            <option value="economy">Phổ thông</option>
                                            <option value="business">Thương gia</option>
                                            <option value="first">Hạng nhất</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">Giá vé (VNĐ)</label>
                                        <input type="number" name="flights[0][price]" class="form-control flight-price" 
                                               min="0" step="1000" required onchange="calculateTotal()">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="mb-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Tổng tiền: <span id="totalAmount" class="text-success">0 ₫</span></h5>
                            <input type="hidden" name="total_amount" id="totalAmountInput" value="0">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Hủy
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Tạo Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let flightIndex = 1;

function addFlight() {
    const container = document.getElementById('flightsContainer');
    const flightRow = `
        <div class="flight-row border rounded p-3 mb-3">
            <button type="button" class="btn btn-sm btn-danger float-end" onclick="this.parentElement.remove(); calculateTotal()">
                <i class="bi bi-trash"></i>
            </button>
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label required">Số hiệu chuyến bay</label>
                        <input type="text" name="flights[${flightIndex}][flight_number]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label required">Hãng bay</label>
                        <input type="text" name="flights[${flightIndex}][airline]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label required">Nơi đi</label>
                        <input type="text" name="flights[${flightIndex}][departure_city]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label required">Nơi đến</label>
                        <input type="text" name="flights[${flightIndex}][arrival_city]" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label required">Ngày đi</label>
                        <input type="date" name="flights[${flightIndex}][departure_date]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label required">Giờ đi</label>
                        <input type="time" name="flights[${flightIndex}][departure_time]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label required">Ngày đến</label>
                        <input type="date" name="flights[${flightIndex}][arrival_date]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label required">Giờ đến</label>
                        <input type="time" name="flights[${flightIndex}][arrival_time]" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label required">Hạng ghế</label>
                        <select name="flights[${flightIndex}][seat_class]" class="form-select" required>
                            <option value="economy">Phổ thông</option>
                            <option value="business">Thương gia</option>
                            <option value="first">Hạng nhất</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label required">Giá vé (VNĐ)</label>
                        <input type="number" name="flights[${flightIndex}][price]" class="form-control flight-price" 
                               min="0" step="1000" required onchange="calculateTotal()">
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', flightRow);
    flightIndex++;
}

function calculateTotal() {
    const prices = document.querySelectorAll('.flight-price');
    let total = 0;
    prices.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('totalAmount').textContent = total.toLocaleString('vi-VN') + ' ₫';
    document.getElementById('totalAmountInput').value = total;
}

// Toggle customer fields
document.getElementById('customerSelect')?.addEventListener('change', function() {
    const newCustomerFields = document.getElementById('newCustomerFields');
    if (this.value) {
        newCustomerFields.style.display = 'none';
        newCustomerFields.querySelectorAll('input').forEach(input => input.required = false);
    } else {
        newCustomerFields.style.display = 'block';
        document.querySelector('[name="customer_name"]').required = true;
    }
});
</script>

<?php include '../../includes/footer.php'; ?>

<?php
// ============================================
// FILE: modules/bookings/process.php
// ============================================

$errors = [];

// Validate
$booking_code = trim($_POST['booking_code'] ?? '');
$booking_date = $_POST['booking_date'] ?? '';
$payment_status = $_POST['payment_status'] ?? 'unpaid';
$booking_status = $_POST['booking_status'] ?? 'pending';
$total_amount = $_POST['total_amount'] ?? 0;

if (empty($booking_code)) {
    $errors['booking_code'] = 'Mã đặt chỗ không được để trống!';
}

if (empty($booking_date)) {
    $errors['booking_date'] = 'Ngày đặt không được để trống!';
}

// Check if booking code exists
$checkQuery = "SELECT id FROM bookings WHERE booking_code = ?";
$stmt = $db->prepare($checkQuery);
$stmt->execute([$booking_code]);
if ($stmt->fetch()) {
    $errors['booking_code'] = 'Mã đặt chỗ đã tồn tại!';
}

// Handle customer
$customer_id = $_POST['customer_id'] ?? null;

if (!$customer_id) {
    // Create new customer
    $customer_name = trim($_POST['customer_name'] ?? '');
    if (empty($customer_name)) {
        $errors['customer_name'] = 'Tên khách hàng không được để trống!';
    } else {
        $insertCustomer = "INSERT INTO customers (full_name, email, phone, id_number) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($insertCustomer);
        $stmt->execute([
            $customer_name,
            $_POST['customer_email'] ?? null,
            $_POST['customer_phone'] ?? null,
            $_POST['customer_id_number'] ?? null
        ]);
        $customer_id = $db->lastInsertId();
        
        // Create wallet for new customer
        $insertWallet = "INSERT INTO wallets (customer_id, balance) VALUES (?, 0)";
        $stmt = $db->prepare($insertWallet);
        $stmt->execute([$customer_id]);
    }
}

// Validate flights
$flights = $_POST['flights'] ?? [];
if (empty($flights)) {
    $errors['flights'] = 'Phải có ít nhất một chuyến bay!';
}

if (empty($errors)) {
    try {
        $db->beginTransaction();
        
        // Insert booking
        $insertBooking = "INSERT INTO bookings 
                         (booking_code, customer_id, total_amount, payment_status, booking_status, created_by, booking_date) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insertBooking);
        $stmt->execute([
            $booking_code,
            $customer_id,
            $total_amount,
            $payment_status,
            $booking_status,
            $_SESSION['user_id'],
            $booking_date
        ]);
        
        $booking_id = $db->lastInsertId();
        
        // Insert flights
        $insertFlight = "INSERT INTO booking_flights 
                        (booking_id, flight_number, departure_city, arrival_city, departure_date, 
                         departure_time, arrival_date, arrival_time, airline, seat_class, price) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        foreach ($flights as $flight) {
            $stmt = $db->prepare($insertFlight);
            $stmt->execute([
                $booking_id,
                $flight['flight_number'],
                $flight['departure_city'],
                $flight['arrival_city'],
                $flight['departure_date'],
                $flight['departure_time'],
                $flight['arrival_date'],
                $flight['arrival_time'],
                $flight['airline'],
                $flight['seat_class'],
                $flight['price']
            ]);
        }
        
        $db->commit();
        setFlashMessage('Tạo booking thành công!', 'success');
        
    } catch (Exception $e) {
        $db->rollBack();
        $errors['database'] = 'Lỗi: ' . $e->getMessage();
    }
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        setFlashMessage($error, 'danger');
    }
}

?>

<?php
// ============================================
// FILE: modules/bookings/cancel.php
// ============================================
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    setFlashMessage('ID không hợp lệ!', 'danger');
    redirect('modules/bookings/index.php');
}

$database = new Database();
$db = $database->getConnection();

try {
    $updateQuery = "UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?";
    $stmt = $db->prepare($updateQuery);
    $stmt->execute([$id]);
    
    setFlashMessage('Hủy booking thành công!', 'success');
} catch (PDOException $e) {
    setFlashMessage('Lỗi: ' . $e->getMessage(), 'danger');
}

redirect('modules/bookings/index.php');
?>