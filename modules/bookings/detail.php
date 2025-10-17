<?php
// ============================================
// FILE: modules/bookings/detail.php
// ============================================
$page_title = "Chi tiết Booking";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    setFlashMessage('ID không hợp lệ!', 'danger');
    redirect('modules/bookings/index.php');
}

// Get booking details
$query = "SELECT b.*, c.*, u.full_name as creator_name,
          c.full_name as customer_name, c.email as customer_email, 
          c.phone as customer_phone
          FROM bookings b 
          JOIN customers c ON b.customer_id = c.id
          JOIN users u ON b.created_by = u.id
          WHERE b.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlashMessage('Booking không tồn tại!', 'danger');
    redirect('modules/bookings/index.php');
}

// Get flight details
$flightQuery = "SELECT * FROM booking_flights WHERE booking_id = ? ORDER BY departure_date, departure_time";
$stmt = $db->prepare($flightQuery);
$stmt->execute([$id]);
$flights = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <button onclick="printContent('bookingDetail')" class="btn btn-outline-primary">
                <i class="bi bi-printer"></i> In vé
            </button>
            <?php if ($booking['booking_status'] != 'cancelled'): ?>
                <a href="edit.php?id=<?= $booking['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Chỉnh sửa
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="bookingDetail">
        <!-- Booking Info -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-ticket-perforated"></i> 
                            Thông tin Booking: <?= $booking['booking_code'] ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Mã đặt chỗ:</strong></p>
                                <h4 class="text-primary"><?= $booking['booking_code'] ?></h4>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Ngày đặt:</strong></p>
                                <h5><?= formatDate($booking['booking_date']) ?></h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Trạng thái thanh toán:</strong></p>
                                <?= getStatusBadge($booking['payment_status']) ?>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Trạng thái booking:</strong></p>
                                <?= getStatusBadge($booking['booking_status']) ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Tổng tiền:</strong></p>
                                <h4 class="text-success"><?= formatCurrency($booking['total_amount']) ?></h4>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Người tạo:</strong></p>
                                <p><?= $booking['creator_name'] ?></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Ngày tạo:</strong></p>
                                <p><?= formatDateTime($booking['created_at']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Cập nhật lần cuối:</strong></p>
                                <p><?= formatDateTime($booking['updated_at']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person"></i> Thông tin Khách hàng
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Họ tên:</strong></p>
                        <p><?= $booking['customer_name'] ?></p>

                        <p class="mb-2 mt-3"><strong>Email:</strong></p>
                        <p><?= $booking['customer_email'] ?? 'Chưa có' ?></p>

                        <p class="mb-2 mt-3"><strong>Số điện thoại:</strong></p>
                        <p><?= $booking['customer_phone'] ?? 'Chưa có' ?></p>

                        <p class="mb-2 mt-3"><strong>Số CMND/CCCD:</strong></p>
                        <p><?= $booking['id_number'] ?? 'Chưa có' ?></p>

                        <p class="mb-2 mt-3"><strong>Ngày sinh:</strong></p>
                        <p><?= $booking['date_of_birth'] ? formatDate($booking['date_of_birth']) : 'Chưa có' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flight Details -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-airplane"></i> Chi tiết Chuyến bay
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($flights)): ?>
                            <p class="text-muted text-center">Không có thông tin chuyến bay</p>
                        <?php else: ?>
                            <?php foreach ($flights as $index => $flight): ?>
                                <div class="flight-item mb-4 pb-4 <?= $index < count($flights) - 1 ? 'border-bottom' : '' ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-1 text-center">
                                            <div class="flight-badge">
                                                <i class="bi bi-airplane-fill fs-3 text-primary"></i>
                                                <p class="small mb-0 mt-1">Chặng <?= $index + 1 ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <h5 class="mb-1"><?= $flight['departure_city'] ?></h5>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-calendar"></i> 
                                                <?= formatDate($flight['departure_date']) ?>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-clock"></i> 
                                                <?= date('H:i', strtotime($flight['departure_time'])) ?>
                                            </p>
                                        </div>

                                        <div class="col-md-2 text-center">
                                            <div class="flight-arrow">
                                                <i class="bi bi-arrow-right-circle fs-2 text-success"></i>
                                                <p class="small text-muted mt-1"><?= $flight['flight_number'] ?></p>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <h5 class="mb-1"><?= $flight['arrival_city'] ?></h5>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-calendar"></i> 
                                                <?= formatDate($flight['arrival_date']) ?>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-clock"></i> 
                                                <?= date('H:i', strtotime($flight['arrival_time'])) ?>
                                            </p>
                                        </div>

                                        <div class="col-md-3">
                                            <p class="mb-1"><strong>Hãng:</strong> <?= $flight['airline'] ?></p>
                                            <p class="mb-1">
                                                <strong>Hạng ghế:</strong> 
                                                <span class="badge bg-secondary">
                                                    <?php
                                                    $seatClass = [
                                                        'economy' => 'Phổ thông',
                                                        'business' => 'Thương gia',
                                                        'first' => 'Hạng nhất'
                                                    ];
                                                    echo $seatClass[$flight['seat_class']];
                                                    ?>
                                                </span>
                                            </p>
                                            <p class="mb-0">
                                                <strong>Giá vé:</strong> 
                                                <span class="text-success fw-bold">
                                                    <?= formatCurrency($flight['price']) ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.flight-item {
    background: #f8f9fc;
    padding: 1.5rem;
    border-radius: 0.5rem;
}
.flight-badge {
    text-align: center;
}
.flight-arrow {
    position: relative;
}
</style>

<?php include '../../includes/footer.php'; ?>