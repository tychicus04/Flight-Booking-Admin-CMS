<?php
// ============================================
// FILE: modules/logs/login_history.php
// ============================================
$page_title = "Lịch sử Đăng nhập";
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
    $where[] = "lh.user_id = ?";
    $params[] = $_GET['user_id'];
}

if (!empty($_GET['date_from'])) {
    $where[] = "DATE(lh.login_time) >= ?";
    $params[] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $where[] = "DATE(lh.login_time) <= ?";
    $params[] = $_GET['date_to'];
}

$whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total
$countQuery = "SELECT COUNT(*) as total FROM login_history lh $whereSQL";
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Get login history
$query = "SELECT lh.*, u.username, u.full_name 
          FROM login_history lh 
          LEFT JOIN users u ON lh.user_id = u.id 
          $whereSQL 
          ORDER BY lh.login_time DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$logins = $stmt->fetchAll();

// Get all users for filter
$usersQuery = "SELECT id, username, full_name FROM users ORDER BY full_name";
$users = $db->query($usersQuery)->fetchAll();
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
                    <div class="col-md-4">
                        <label class="form-label">User</label>
                        <select name="user_id" class="form-select">
                            <option value="">Tất cả</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" 
                                    <?= ($_GET['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                    <?= $user['full_name'] ?> (<?= $user['username'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="<?= $_GET['date_from'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="<?= $_GET['date_to'] ?? '' ?>">
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
            <h5 class="card-title mb-0">Lịch sử Đăng nhập</h5>
        </div>
        <div class="card-body">
            <?php if (empty($logins)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>Không có dữ liệu</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Thời gian đăng nhập</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logins as $login): ?>
                                <tr>
                                    <td><?= $login['id'] ?></td>
                                    <td>
                                        <?php if ($login['username']): ?>
                                            <strong><?= $login['full_name'] ?></strong>
                                            <br><small class="text-muted"><?= $login['username'] ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">User đã bị xóa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= formatDateTime($login['login_time']) ?></td>
                                    <td><code><?= $login['ip_address'] ?></code></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= substr($login['user_agent'], 0, 100) . (strlen($login['user_agent']) > 100 ? '...' : '') ?>
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

<?php
// ============================================
// FILE: config/log_helper.php
// Helper functions để ghi logs
// ============================================

/**
 * Log login activity
 */
function logLogin($user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $query = "INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id, $ip, $userAgent]);
    } catch (PDOException $e) {
        // Silent fail - don't break login process
        error_log("Failed to log login: " . $e->getMessage());
    }
}

/**
 * Log operation/activity
 */
function logOperation($user_id, $action, $module = null, $details = null) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO operation_logs (user_id, action, module, details) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id, $action, $module, $details]);
    } catch (PDOException $e) {
        // Silent fail
        error_log("Failed to log operation: " . $e->getMessage());
    }
}
?>