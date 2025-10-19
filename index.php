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
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Dashboard</h2>
                    <p class="text-muted mb-0">Chào mừng trở lại, <strong><?= $_SESSION['full_name'] ?></strong></p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>modules/bookings/add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tạo Booking mới
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Tổng Users</div>
                        <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
                        <div class="stat-desc">Users đang hoạt động</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-success">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Tổng Bookings</div>
                        <div class="stat-value"><?= number_format($stats['total_bookings']) ?></div>
                        <div class="stat-desc">Tổng số vé đã đặt</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-info">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Booking tháng này</div>
                        <div class="stat-value"><?= number_format($stats['bookings_this_month']) ?></div>
                        <div class="stat-desc">So với tháng trước</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Tổng doanh thu</div>
                        <div class="stat-value"><?= formatCurrency($stats['total_revenue']) ?></div>
                        <div class="stat-desc">Đã thanh toán</div>
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
/* Modern Stat Cards */
.stat-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.stat-card-body {
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    flex-shrink: 0;
}

.stat-card-primary .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-card-success .stat-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.stat-card-info .stat-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.stat-card-warning .stat-icon {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 13px;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #212529;
    line-height: 1;
    margin-bottom: 4px;
}

.stat-desc {
    font-size: 12px;
    color: #9ca3af;
}

/* Card Improvements */
.card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.card-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 20px 24px;
}

.card-header h6 {
    font-size: 16px;
    font-weight: 600;
    color: #212529;
}

.card-body {
    padding: 24px;
}

/* Table Modern Style */
.table {
    margin-bottom: 0;
}

.table thead th {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    font-size: 13px;
    color: #374151;
    padding: 12px 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: background 0.2s ease;
}

.table tbody tr:hover {
    background: #f9fafb;
}

.table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    font-size: 14px;
}

/* Alert Improvements */
.alert {
    border: none;
    border-radius: 10px;
    padding: 16px 20px;
}

.alert-warning {
    background: #fff8e1;
    color: #f59e0b;
}

.alert-success {
    background: #f0fdf4;
    color: #10b981;
}

/* List Group */
.list-group-item {
    border: none;
    border-bottom: 1px solid #e5e7eb;
    padding: 14px 0;
    font-size: 14px;
}

.list-group-item:last-child {
    border-bottom: none;
}

.badge {
    padding: 6px 12px;
    font-weight: 600;
    font-size: 12px;
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
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                hoverBackgroundColor: ['#059669', '#d97706', '#dc2626'],
                borderWidth: 0,
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    borderWidth: 0,
                    cornerRadius: 8
                }
            },
            cutout: '75%',
        },
    });
}
</script>

<?php include 'includes/footer.php'; ?>
