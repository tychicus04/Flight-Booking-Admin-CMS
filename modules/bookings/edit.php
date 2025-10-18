<?php
// ============================================
// FILE: modules/bookings/edit.php
// ============================================
$page_title = "Chỉnh sửa Booking";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    setFlashMessage('ID không hợp lệ!', 'danger');
    redirect('modules/bookings/index.php');
}

// Get booking data
$query = "SELECT b.*, c.id as customer_id, c.full_name as customer_name, 
          c.email, c.phone, c.id_number 
          FROM bookings b 
          JOIN customers c ON b.customer_id = c.id 
          WHERE b.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlashMessage('Booking không tồn tại!', 'danger');
    redirect('modules/bookings/index.php');
}

// Get flight details
$flightQuery = "SELECT * FROM booking_flights WHERE booking_id = ? ORDER BY id";
$stmt = $db->prepare($flightQuery);
$stmt->execute([$id]);
$flights = $stmt->fetchAll();

// Get customers for dropdown
$customers = $db->query("SELECT id, full_name, email FROM customers ORDER BY full_name")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_code = trim($_POST['booking_code'] ?? '');
    $booking_date = $_POST['booking_date'] ?? '';
    $payment_status = $_POST['payment_status'] ?? 'unpaid';
    $booking_status = $_POST['booking_status'] ?? 'pending';
    $customer_id = $_POST['customer_id'] ?? null;
    $total_amount = $_POST['total_amount'] ?? 0;

    // Validation
    if (empty($booking_code)) {
        $errors['booking_code'] = 'Mã đặt chỗ không được để trống!';
    }

    if (empty($booking_date)) {
        $errors['booking_date'] = 'Ngày đặt không được để trống!';
    }

    // Check if booking code exists (except current booking)
    $checkQuery = "SELECT id FROM bookings WHERE booking_code = ? AND id != ?";
    $stmt = $db->prepare($checkQuery);
    $stmt->execute([$booking_code, $id]);
    if ($stmt->fetch()) {
        $errors['booking_code'] = 'Mã đặt chỗ đã tồn tại!';
    }

    // Validate flights
    $postFlights = $_POST['flights'] ?? [];
    if (empty($postFlights)) {
        $errors['flights'] = 'Phải có ít nhất một chuyến bay!';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Update booking
            $updateBooking = "UPDATE bookings 
                             SET booking_code = ?, 
                                 customer_id = ?, 
                                 total_amount = ?, 
                                 payment_status = ?, 
                                 booking_status = ?, 
                                 booking_date = ? 
                             WHERE id = ?";
            $stmt = $db->prepare($updateBooking);
            $stmt->execute([
                $booking_code,
                $customer_id,
                $total_amount,
                $payment_status,
                $booking_status,
                $booking_date,
                $id
            ]);
            
            // Delete old flights
            $deleteFlights = "DELETE FROM booking_flights WHERE booking_id = ?";
            $stmt = $db->prepare($deleteFlights);
            $stmt->execute([$id]);
            
            // Insert new flights
            $insertFlight = "INSERT INTO booking_flights 
                            (booking_id, flight_number, departure_city, arrival_city, departure_date, 
                             departure_time, arrival_date, arrival_time, airline, seat_class, price) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            foreach ($postFlights as $flight) {
                $stmt = $db->prepare($insertFlight);
                $stmt->execute([
                    $id,
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
            setFlashMessage('Cập nhật booking thành công!', 'success');
            redirect('modules/bookings/detail.php?id=' . $id);
            
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
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Chỉnh sửa Booking: <?= $booking['booking_code'] ?></h5>
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
                                       value="<?= $_POST['booking_code'] ?? $booking['booking_code'] ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Ngày đặt</label>
                                <input type="date" name="booking_date" class="form-control" 
                                       value="<?= $_POST['booking_date'] ?? $booking['booking_date'] ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Trạng thái thanh toán</label>
                                <select name="payment_status" class="form-select" required>
                                    <option value="unpaid" <?= (isset($_POST['payment_status']) ? $_POST['payment_status'] : $booking['payment_status']) == 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
                                    <option value="paid" <?= (isset($_POST['payment_status']) ? $_POST['payment_status'] : $booking['payment_status']) == 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                                    <option value="refunded" <?= (isset($_POST['payment_status']) ? $_POST['payment_status'] : $booking['payment_status']) == 'refunded' ? 'selected' : '' ?>>Đã hoàn tiền</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Trạng thái booking</label>
                                <select name="booking_status" class="form-select" required>
                                    <option value="pending" <?= (isset($_POST['booking_status']) ? $_POST['booking_status'] : $booking['booking_status']) == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                    <option value="confirmed" <?= (isset($_POST['booking_status']) ? $_POST['booking_status'] : $booking['booking_status']) == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                    <option value="cancelled" <?= (isset($_POST['booking_status']) ? $_POST['booking_status'] : $booking['booking_status']) == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Customer Info -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2 mb-3">2. Thông tin khách hàng</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label required">Khách hàng</label>
                                <select name="customer_id" class="form-select" required>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>" 
                                            <?= (isset($_POST['customer_id']) ? $_POST['customer_id'] : $booking['customer_id']) == $customer['id'] ? 'selected' : '' ?>>
                                            <?= $customer['full_name'] ?> - <?= $customer['email'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Khách hàng hiện tại: <strong><?= $booking['customer_name'] ?></strong>
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
                        <?php 
                        $flightsToShow = !empty($_POST['flights']) ? $_POST['flights'] : $flights;
                        foreach ($flightsToShow as $index => $flight): 
                        ?>
                            <div class="flight-row border rounded p-3 mb-3">
                                <?php if ($index > 0): ?>
                                    <button type="button" class="btn btn-sm btn-danger float-end" onclick="this.parentElement.remove(); calculateTotal()">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label required">Số hiệu chuyến bay</label>
                                            <input type="text" name="flights[<?= $index ?>][flight_number]" class="form-control" 
                                                   value="<?= is_array($flight) ? ($flight['flight_number'] ?? '') : $flight['flight_number'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label required">Hãng bay</label>
                                            <input type="text" name="flights[<?= $index ?>][airline]" class="form-control" 
                                                   value="<?= is_array($flight) ? ($flight['airline'] ?? '') : $flight['airline'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label required">Nơi đi</label>
                                            <input type="text" name="flights[<?= $index ?>][departure_city]" class="form-control" 
                                                   value="<?= is_array($flight) ? ($flight['departure_city'] ?? '') : $flight['departure_city'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label required">Nơi đến</label>
                                            <input type="text" name="flights[<?= $index ?>][arrival_city]" class="form-control" 
                                                   value="<?= is_array($flight) ? ($flight['arrival_city'] ?? '') : $flight['arrival_city'] ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label required">Ngày đi</label>
                                            <input type="date" name="flights[<?= $index ?>][departure_date]" class="form-control" 
                                                   value="<?= is_array($flight) ? ($flight['departure_date'] ?? '') : $flight['departure_date'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label required">Giờ đi</label>
                                            <input type="time" name="flights[<?= $index ?>][departure_time]" class="form-control" 
                                                   value="<?= is_array($flight) ? ($flight['departure_time'] ?? '') : $flight['departure_time'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label required">Ngày đến</label>
                                            <input type="date" name="flights[<?= $index ?>][arrival_date]" class="form-control" 
                                                   value="<?= is_array($flight) ? ($flight['arrival_date'] ?? '') : $flight['arrival_date'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label required">Giờ đến</label>
                                            <input type="time" name="flights[<?= $index ?>][arrival_time]" class="form-control" 
                                                   value="<?= is_array($flight) ? ($flight['arrival_time'] ?? '') : $flight['arrival_time'] ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required">Hạng ghế</label>
                                            <select name="flights[<?= $index ?>][seat_class]" class="form-select" required>
                                                <option value="economy" <?= (is_array($flight) ? ($flight['seat_class'] ?? '') : $flight['seat_class']) == 'economy' ? 'selected' : '' ?>>Phổ thông</option>
                                                <option value="business" <?= (is_array($flight) ? ($flight['seat_class'] ?? '') : $flight['seat_class']) == 'business' ? 'selected' : '' ?>>Thương gia</option>
                                                <option value="first" <?= (is_array($flight) ? ($flight['seat_class'] ?? '') : $flight['seat_class']) == 'first' ? 'selected' : '' ?>>Hạng nhất</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label required">Giá vé (VNĐ)</label>
                                            <input type="number" name="flights[<?= $index ?>][price]" class="form-control flight-price" 
                                                   min="0" step="1000" 
                                                   value="<?= is_array($flight) ? ($flight['price'] ?? '') : $flight['price'] ?>" 
                                                   required onchange="calculateTotal()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="mb-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Tổng tiền: <span id="totalAmount" class="text-success">0 ₫</span></h5>
                            <input type="hidden" name="total_amount" id="totalAmountInput" value="<?= $booking['total_amount'] ?>">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Hủy
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Cập nhật Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let flightIndex = <?= count($flights) ?>;

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

// Calculate total on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>

<?php include '../../includes/footer.php'; ?>