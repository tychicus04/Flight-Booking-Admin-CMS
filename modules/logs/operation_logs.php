
<?php
// ============================================
// FILE: modules/logs/operation_logs.php
// ============================================
$page_title = "Logs Hoạt động";
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

if (!empty($_GET['user_id'])) {
    $where[] = "ol.user_id = ?";
    $params[] = $_GET['user_id'];
}

if (!empty($_GET['module'])) {
    $where[] = "ol.module = ?";
    $params[] = $_GET['module'];
}

if (!empty($_GET['search'])) {
    $where[] = "(ol.action LIKE ? OR ol.details LIKE ?)";
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
}

if (!empty($_GET['date_from'])) {
    $where[] = "DATE(ol.created_at) >= ?";
    $params[] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $where[] = "DATE(ol.created_at) <= ?";
    $params[] = $_GET['date_to'];
}

$whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total
$countQuery = "SELECT COUNT(*) as total FROM operation_logs ol $whereSQL";
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Get operation logs
$query = "SELECT ol.*, u.username, u.full_name 
          FROM operation_logs ol 
          LEFT JOIN users u ON ol.user_id = u.id 
          $whereSQL 
          ORDER BY ol.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get all users for filter
$usersQuery = "SELECT id, username, full_name FROM users ORDER BY full_name";
$users = $db->query($usersQuery)->fetchAll();

// Get distinct modules
$modulesQuery = "SELECT DISTINCT module FROM operation_logs WHERE module IS NOT NULL ORDER BY module";
$modules = $db->query($modulesQuery)->fetchAll(PDO::FETCH_COLUMN);
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
                               placeholder="Hành động, Chi tiết..." 
                               value="<?= $_GET['search'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">User</label>
                        <select name="user_id" class="form-select">
                            <option value="">Tất cả</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" 
                                    <?= ($_GET['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                    <?= $user['full_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Module</label>
                        <select name="module" class="form-select">
                            <option value="">Tất cả</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?= $module ?>" 
                                    <?= ($_GET['module'] ?? '') == $module ? 'selected' : '' ?>>
                                    <?= ucfirst($module) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Đặt lại
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Logs Hoạt động</h5>
        </div>
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>Không có dữ liệu</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px">ID</th>
                                <th style="width: 150px">Thời gian</th>
                                <th style="width: 150px">User</th>
                                <th style="width: 100px">Module</th>
                                <th style="width: 200px">Hành động</th>
                                <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $log['id'] ?></td>
                                    <td>
                                        <small><?= formatDateTime($log['created_at']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($log['username']): ?>
                                            <strong><?= $log['full_name'] ?></strong>
                                            <br><small class="text-muted"><?= $log['username'] ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log['module']): ?>
                                            <span class="badge bg-secondary"><?= $log['module'] ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $log['action'] ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $log['details'] ?? '-' ?>
                                        </small>
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

<?php include '../../includes/footer.php'; ?>
