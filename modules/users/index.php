<?php
// ============================================
// FILE: modules/users/index.php
// ============================================
$page_title = "Quản lý User";
include '../../includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Filters
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

if (!empty($_GET['role_id'])) {
    $where[] = "role_id = ?";
    $params[] = $_GET['role_id'];
}

if (!empty($_GET['status'])) {
    $where[] = "status = ?";
    $params[] = $_GET['status'];
}

$whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total records
$database = new Database();
$db = $database->getConnection();

$countQuery = "SELECT COUNT(*) as total FROM users " . $whereSQL;
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Get users
$query = "SELECT u.*, r.name as role_name 
          FROM users u 
          JOIN roles r ON u.role_id = r.id 
          $whereSQL 
          ORDER BY u.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get roles for filter
$rolesQuery = "SELECT * FROM roles ORDER BY name";
$roles = $db->query($rolesQuery)->fetchAll();
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
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Username, Họ tên, Email..." 
                               value="<?= $_GET['search'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vai trò</label>
                        <select name="role_id" class="form-select">
                            <option value="">Tất cả</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" 
                                    <?= (isset($_GET['role_id']) && $_GET['role_id'] == $role['id']) ? 'selected' : '' ?>>
                                    <?= $role['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="active" <?= (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="locked" <?= (isset($_GET['status']) && $_GET['status'] == 'locked') ? 'selected' : '' ?>>Bị khóa</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Tìm
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
            <h5 class="card-title mb-0">Danh sách User</h5>
            <div class="card-actions">
                <a href="add.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Thêm User mới
                </a>
                <div class="column-toggle">
                    <button class="btn btn-outline-secondary" id="columnToggleBtn">
                        <i class="bi bi-list-columns"></i> Cột
                    </button>
                    <div class="column-toggle-menu" id="columnToggleMenu">
                        <div class="column-toggle-item">
                            <input type="checkbox" class="column-checkbox" data-column="col-id" checked>
                            <label>ID</label>
                        </div>
                        <div class="column-toggle-item">
                            <input type="checkbox" class="column-checkbox" data-column="col-avatar" checked>
                            <label>Avatar</label>
                        </div>
                        <div class="column-toggle-item">
                            <input type="checkbox" class="column-checkbox" data-column="col-username" checked>
                            <label>Username</label>
                        </div>
                        <div class="column-toggle-item">
                            <input type="checkbox" class="column-checkbox" data-column="col-fullname" checked>
                            <label>Họ và Tên</label>
                        </div>
                        <div class="column-toggle-item">
                            <input type="checkbox" class="column-checkbox" data-column="col-role" checked>
                            <label>Vai trò</label>
                        </div>
                        <div class="column-toggle-item">
                            <input type="checkbox" class="column-checkbox" data-column="col-status" checked>
                            <label>Trạng thái</label>
                        </div>
                        <div class="column-toggle-item">
                            <input type="checkbox" class="column-checkbox" data-column="col-created" checked>
                            <label>Ngày tạo</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>Không có dữ liệu</h5>
                    <p>Không tìm thấy user nào phù hợp với bộ lọc của bạn.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="col-id">ID</th>
                                <th class="col-avatar">Avatar</th>
                                <th class="col-username">Username</th>
                                <th class="col-fullname">Họ và Tên</th>
                                <th class="col-role">Vai trò</th>
                                <th class="col-status">Trạng thái</th>
                                <th class="col-created">Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="col-id"><?= $user['id'] ?></td>
                                    <td class="col-avatar">
                                        <?php if (!empty($user['avatar'])): ?>
                                            <img src="<?= BASE_URL ?>uploads/avatars/<?= $user['avatar'] ?>" 
                                                 alt="Avatar" class="avatar-sm">
                                        <?php else: ?>
                                            <i class="bi bi-person-circle fs-4"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-username"><?= $user['username'] ?></td>
                                    <td class="col-fullname"><?= $user['full_name'] ?></td>
                                    <td class="col-role">
                                        <span class="badge bg-info"><?= $user['role_name'] ?></span>
                                    </td>
                                    <td class="col-status"><?= getStatusBadge($user['status']) ?></td>
                                    <td class="col-created"><?= formatDateTime($user['created_at']) ?></td>
                                    <td>
                                        <div class="action-dropdown">
                                            <button class="btn btn-sm btn-link" onclick="toggleActionMenu(this)">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <div class="action-menu">
                                                <a href="edit.php?id=<?= $user['id'] ?>">
                                                    <i class="bi bi-pencil"></i> Chỉnh sửa
                                                </a>
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal<?= $user['id'] ?>">
                                                    <i class="bi bi-key"></i> Đổi mật khẩu
                                                </a>
                                                <div class="divider"></div>
                                                <a href="delete.php?id=<?= $user['id'] ?>" 
                                                   onclick="return confirmDelete('Bạn có chắc chắn muốn xóa user này?')" 
                                                   class="text-danger">
                                                    <i class="bi bi-trash"></i> Xóa
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                    Trước
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                    Sau
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>