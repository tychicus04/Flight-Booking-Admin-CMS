<?php
// ============================================
// FILE: modules/bookings/index.php
// ============================================
$page_title = "Quản lý Booking";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Filters
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(b.booking_code LIKE ? OR c.full_name LIKE ?)";
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
}

if (!empty($_GET['payment_status'])) {
    $where[] = "b.payment_status = ?";
    $params[] = $_GET['payment_status'];
}

if (!empty($_GET['booking_status'])) {
    $where[] = "b.booking_status = ?";
    $params[] = $_GET['booking_status'];
}

if (!empty($_GET['date_from'])) {
    $where[] = "b.booking_date >= ?";
    $params[] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $where[] = "b.booking_date <= ?";
    $params[] = $_GET['date_to'];
}

$whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total
$countQuery = "SELECT COUNT(*) as total 
               FROM bookings b 
               JOIN customers c ON b.customer_id = c.id 
               $whereSQL";
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Get bookings
$query = "SELECT b.*, c.full_name as customer_name, u.full_name as creator_name
          FROM bookings b 
          JOIN customers c ON b.customer_id = c.id
          JOIN users u ON b.created_by = u.id
          $whereSQL 
          ORDER BY b.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<div class="container-fluid">
    <!-- Filter Section -->
    <div class="filter-section" id="filterSection">
        <div class="filter-header" onclick="toggleFilter()">
            <h6><i class="bi bi-funnel"></i> Bộ lọc</h6>
            <i class="bi bi-chevron-up"></i>
        </div>
        <div class="filter-body">
            <form method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Mã booking, Tên khách..." 
                               value="<?= $_GET['search'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="<?= $_GET['date_from'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="<?= $_GET['date_to'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Thanh toán</label>
                        <select name="payment_status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="paid" <?= (isset($_GET['payment_status']) && $_GET['payment_status'] == 'paid') ? 'selected' : '' ?>>Đã thanh toán</option>
                            <option value="unpaid" <?= (isset($_GET['payment_status']) && $_GET['payment_status'] == 'unpaid') ? 'selected' : '' ?>>Chưa thanh toán</option>
                            <option value="refunded" <?= (isset($_GET['payment_status']) && $_GET['payment_status'] == 'refunded') ? 'selected' : '' ?>>Đã hoàn tiền</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Trạng thái</label>
                        <select name="booking_status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="confirmed" <?= (isset($_GET['booking_status']) && $_GET['booking_status'] == 'confirmed') ? 'selected' : '' ?>>Đã xác nhận</option>
                            <option value="pending" <?= (isset($_GET['booking_status']) && $_GET['booking_status'] == 'pending') ? 'selected' : '' ?>>Chờ xử lý</option>
                            <option value="cancelled" <?= (isset($_GET['booking_status']) && $_GET['booking_status'] == 'cancelled') ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i>
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Danh sách Booking</h5>
            <div class="card-actions">
                <a href="add.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tạo Booking mới
                </a>
                <button class="btn btn-outline-secondary" onclick="exportTableToCSV('bookings.csv')">
                    <i class="bi bi-file-earmark-excel"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>Không có dữ liệu</h5>
                    <p>Không tìm thấy booking nào phù hợp.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table" id="bookingsTable">
                        <thead>
                            <tr>
                                <th class="col-booking-code">Mã đặt chỗ</th>
                                <th class="col-customer">Khách hàng</th>
                                <th class="col-booking-date">Ngày đặt</th>
                                <th class="col-total-amount">Tổng tiền</th>
                                <th class="col-payment-status">TT Thanh toán</th>
                                <th class="col-booking-status">TT Booking</th>
                                <th class="col-creator">Người tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td class="col-booking-code">
                                        <a href="detail.php?id=<?= $booking['id'] ?>" class="text-primary fw-bold">
                                            <?= $booking['booking_code'] ?>
                                        </a>
                                    </td>
                                    <td class="col-customer"><?= $booking['customer_name'] ?></td>
                                    <td class="col-booking-date"><?= formatDate($booking['booking_date']) ?></td>
                                    <td class="col-total-amount"><?= formatCurrency($booking['total_amount']) ?></td>
                                    <td class="col-payment-status"><?= getStatusBadge($booking['payment_status']) ?></td>
                                    <td class="col-booking-status"><?= getStatusBadge($booking['booking_status']) ?></td>
                                    <td class="col-creator"><?= $booking['creator_name'] ?></td>
                                    <td>
                                        <div class="action-dropdown">
                                            <button class="btn btn-sm btn-link" onclick="toggleActionMenu(this)">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <div class="action-menu">
                                                <a href="detail.php?id=<?= $booking['id'] ?>">
                                                    <i class="bi bi-eye"></i> Xem chi tiết
                                                </a>
                                                <a href="edit.php?id=<?= $booking['id'] ?>">
                                                    <i class="bi bi-pencil"></i> Chỉnh sửa
                                                </a>
                                                <?php if ($booking['booking_status'] != 'cancelled'): ?>
                                                    <div class="divider"></div>
                                                    <a href="cancel.php?id=<?= $booking['id'] ?>" 
                                                       onclick="return confirmDelete('Bạn có chắc chắn muốn hủy booking này?')" 
                                                       class="text-danger">
                                                        <i class="bi bi-x-circle"></i> Hủy booking
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Initialize column visibility for bookings table
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('bookingsTable')) {
        columnVisibility = new ColumnVisibility('#bookingsTable', 'bookings_columns_visibility');
        columnVisibility.init([
            { key: 'col-booking-code', label: 'Mã đặt chỗ' },
            { key: 'col-customer', label: 'Khách hàng' },
            { key: 'col-booking-date', label: 'Ngày đặt' },
            { key: 'col-total-amount', label: 'Tổng tiền' },
            { key: 'col-payment-status', label: 'TT Thanh toán' },
            { key: 'col-booking-status', label: 'TT Booking' },
            { key: 'col-creator', label: 'Người tạo' }
        ]);
    }
});
</script>

<?php include '../../includes/footer.php'; ?>