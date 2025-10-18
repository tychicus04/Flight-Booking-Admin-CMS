<?php
// ============================================
// FILE: modules/refunds/index.php
// ============================================
$page_title = "Quản lý Hoàn tiền";
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

if (!empty($_GET['status'])) {
    $where[] = "r.status = ?";
    $params[] = $_GET['status'];
}

if (!empty($_GET['search'])) {
    $where[] = "(b.booking_code LIKE ? OR c.full_name LIKE ?)";
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
}

if (!empty($_GET['date_from'])) {
    $where[] = "DATE(r.created_at) >= ?";
    $params[] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $where[] = "DATE(r.created_at) <= ?";
    $params[] = $_GET['date_to'];
}

$whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total
$countQuery = "SELECT COUNT(*) as total 
               FROM refund_requests r 
               JOIN bookings b ON r.booking_id = b.id 
               JOIN customers c ON r.customer_id = c.id 
               $whereSQL";
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Get refund requests
$query = "SELECT r.*, 
          b.booking_code, 
          c.full_name as customer_name,
          u.full_name as processor_name
          FROM refund_requests r 
          JOIN bookings b ON r.booking_id = b.id
          JOIN customers c ON r.customer_id = c.id
          LEFT JOIN users u ON r.processed_by = u.id
          $whereSQL 
          ORDER BY 
            CASE r.status 
                WHEN 'pending' THEN 1 
                WHEN 'approved' THEN 2 
                WHEN 'rejected' THEN 3 
            END,
            r.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$refunds = $stmt->fetchAll();

// Get statistics
$statsQuery = "SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved_amount
               FROM refund_requests";
$stats = $db->query($statsQuery)->fetch();
?>

<div class="container-fluid">
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Tổng yêu cầu</h6>
                    <h3 class="mb-0"><?= number_format($stats['total_requests']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Chờ xử lý</h6>
                    <h3 class="mb-0 text-warning"><?= number_format($stats['pending_count']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Đã duyệt</h6>
                    <h3 class="mb-0 text-success"><?= number_format($stats['approved_count']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Tổng tiền hoàn</h6>
                    <h3 class="mb-0 text-success"><?= formatCurrency($stats['approved_amount']) ?></h3>
                </div>
            </div>
        </div>
    </div>

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
                    <div class="col-md-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                            <option value="approved" <?= ($_GET['status'] ?? '') == 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                            <option value="rejected" <?= ($_GET['status'] ?? '') == 'rejected' ? 'selected' : '' ?>>Đã từ chối</option>
                        </select>
                    </div>
                    <div class="col-md-2">
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
            <h5 class="card-title mb-0">Danh sách Yêu cầu Hoàn tiền</h5>
        </div>
        <div class="card-body">
            <?php if (empty($refunds)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>Không có dữ liệu</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã Booking</th>
                                <th>Khách hàng</th>
                                <th>Số tiền</th>
                                <th>Lý do</th>
                                <th>Ngày yêu cầu</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($refunds as $refund): ?>
                                <tr>
                                    <td><?= $refund['id'] ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>modules/bookings/detail.php?id=<?= $refund['booking_id'] ?>" 
                                           class="text-primary fw-bold">
                                            <?= $refund['booking_code'] ?>
                                        </a>
                                    </td>
                                    <td><?= $refund['customer_name'] ?></td>
                                    <td>
                                        <strong class="text-danger">
                                            <?= formatCurrency($refund['amount']) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <small><?= substr($refund['reason'], 0, 50) . (strlen($refund['reason']) > 50 ? '...' : '') ?></small>
                                    </td>
                                    <td><?= formatDateTime($refund['created_at']) ?></td>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'pending' => '<span class="badge bg-warning">Chờ xử lý</span>',
                                            'approved' => '<span class="badge bg-success">Đã duyệt</span>',
                                            'rejected' => '<span class="badge bg-danger">Đã từ chối</span>'
                                        ];
                                        echo $statusBadges[$refund['status']];
                                        ?>
                                    </td>
                                    <td>
                                        <a href="detail.php?id=<?= $refund['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
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

<style>
.border-left-warning {
    border-left: 4px solid #f6c23e;
}
.border-left-success {
    border-left: 4px solid #1cc88a;
}
</style>

<?php include '../../includes/footer.php'; ?>
