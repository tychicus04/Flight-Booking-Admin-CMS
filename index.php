<?php
// ============================================
// FILE: index.php (Dashboard)
// ============================================
$page_title = "Dashboard";
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total users
$query = "SELECT COUNT(*) as total FROM users WHERE status = 'active'";
$stats['total_users'] = $db->query($query)->fetch()['total'];

// Total bookings
$query = "SELECT COUNT(*) as total FROM bookings";
$stats['total_bookings'] = $db->query($query)->fetch()['total'];

// Total bookings this month
$query = "SELECT COUNT(*) as total FROM bookings 
          WHERE MONTH(booking_date) = MONTH(CURRENT_DATE()) 
          AND YEAR(booking_date) = YEAR(CURRENT_DATE())";
$stats['bookings_this_month'] = $db->query($query)->fetch()['total'];

// Total revenue
$query = "SELECT SUM(total_amount) as total FROM bookings WHERE payment_status = 'paid'";
$stats['total_revenue'] = $db->query($query)->fetch()['total'] ?? 0;

// Pending bookings
$query = "SELECT COUNT(*) as total FROM bookings WHERE booking_status = 'pending'";
$stats['pending_bookings'] = $db->query($query)->fetch()['total'];

// Recent bookings
$query = "SELECT b.*, c.full_name as customer_name 
          FROM bookings b 
          JOIN customers c ON b.customer_id = c.id 
          ORDER BY b.created_at DESC 
          LIMIT 10";
$recentBookings = $db->query($query)->fetchAll();

// Booking status statistics
$query = "SELECT booking_status, COUNT(*) as count 
          FROM bookings 
          GROUP BY booking_status";
$bookingStats = $db->query($query)->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Chào mừng, <?= $_SESSION['full_name'] ?>!</h2>
            <p class="text-muted">Đây là tổng quan về hệ thống quản lý booking</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                <?= number_format($stats['total_users']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tổng Bookings
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                <?= number_format($stats['total_bookings']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-ticket-perforated fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Bookings tháng này
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                <?= number_format($stats['bookings_this_month']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tổng doanh thu
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                <?= formatCurrency($stats['total_revenue']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-currency-dollar fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Status Chart & Pending Bookings -->
    <div class="row mb-4">
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Thống kê trạng thái Booking</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="bookingStatusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="me-2">
                            <i class="bi bi-circle-fill text-success"></i> Đã xác nhận: 
                            <?= $bookingStats['confirmed'] ?? 0 ?>
                        </span>
                        <span class="me-2">
                            <i class="bi bi-circle-fill text-warning"></i> Chờ xử lý: 
                            <?= $bookingStats['pending'] ?? 0 ?>
                        </span>
                        <span class="me-2">
                            <i class="bi bi-circle-fill text-danger"></i> Đã hủy: 
                            <?= $bookingStats['cancelled'] ?? 0 ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Bookings chờ xử lý</h6>
                </div>
                <div class="card-body">
                    <?php if ($stats['pending_bookings'] > 0): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Có <strong><?= $stats['pending_bookings'] ?></strong> booking đang chờ xử lý!
                            <a href="<?= BASE_URL ?>modules/bookings/index.php?status=pending" class="alert-link">
                                Xem ngay
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            Không có booking nào đang chờ xử lý.
                        </div>
                    <?php endif; ?>
                    
                    <h6 class="mt-4">Các mục cần chú ý:</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Bookings chưa thanh toán
                            <span class="badge bg-warning rounded-pill">
                                <?php 
                                $unpaid = $db->query("SELECT COUNT(*) as total FROM bookings WHERE payment_status = 'unpaid'")->fetch()['total'];
                                echo $unpaid;
                                ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Users bị khóa
                            <span class="badge bg-danger rounded-pill">
                                <?php 
                                $locked = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'locked'")->fetch()['total'];
                                echo $locked;
                                ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Bookings gần đây</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($recentBookings)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-2">Chưa có booking nào</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã booking</th>
                                        <th>Khách hàng</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Thanh toán</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentBookings as $booking): ?>
                                        <tr>
                                            <td><?= $booking['booking_code'] ?></td>
                                            <td><?= $booking['customer_name'] ?></td>
                                            <td><?= formatDate($booking['booking_date']) ?></td>
                                            <td><?= formatCurrency($booking['total_amount']) ?></td>
                                            <td><?= getStatusBadge($booking['payment_status']) ?></td>
                                            <td><?= getStatusBadge($booking['booking_status']) ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>modules/bookings/detail.php?id=<?= $booking['id'] ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>modules/bookings/index.php" class="btn btn-outline-primary">
                                Xem tất cả bookings <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Pie Chart for Booking Status
const ctx = document.getElementById('bookingStatusChart');
if (ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Đã xác nhận', 'Chờ xử lý', 'Đã hủy'],
            datasets: [{
                data: [
                    <?= $bookingStats['confirmed'] ?? 0 ?>,
                    <?= $bookingStats['pending'] ?? 0 ?>,
                    <?= $bookingStats['cancelled'] ?? 0 ?>
                ],
                backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                hoverBackgroundColor: ['#17a673', '#dda20a', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            cutout: '80%',
        },
    });
}
</script>

<?php include 'includes/footer.php'; ?>