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
