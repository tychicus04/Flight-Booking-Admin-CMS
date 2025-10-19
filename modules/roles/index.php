<?php
// ============================================
// FILE: modules/roles/index.php
// ============================================
$page_title = "Quản lý Vai trò";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get all roles with permission count
$query = "SELECT r.*, 
          (SELECT COUNT(*) FROM role_permissions WHERE role_id = r.id) as permission_count
          FROM roles r 
          ORDER BY r.id";
$roles = $db->query($query)->fetchAll();
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Danh sách Vai trò</h5>
            <div class="card-actions">
                <a href="add.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Thêm Vai trò mới
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($roles)): ?>
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
                                <th>Tên vai trò</th>
                                <th>Mô tả</th>
                                <th>Số lượng quyền</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td><?= $role['id'] ?></td>
                                    <td>
                                        <strong><?= $role['name'] ?></strong>
                                    </td>
                                    <td><?= $role['description'] ?? '-' ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= $role['permission_count'] ?> quyền
                                        </span>
                                    </td>
                                    <td><?= formatDateTime($role['created_at']) ?></td>
                                    <td>
                                        <div class="action-dropdown">
                                            <button class="btn btn-sm btn-link">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <div class="action-menu">
                                                <a href="edit.php?id=<?= $role['id'] ?>">
                                                    <i class="bi bi-pencil"></i> Chỉnh sửa
                                                </a>
                                                <?php if ($role['id'] > 3): // Không cho xóa 3 role mặc định ?>
                                                    <div class="divider"></div>
                                                    <a href="delete.php?id=<?= $role['id'] ?>" 
                                                       onclick="return confirmDelete('Bạn có chắc chắn muốn xóa vai trò này?')" 
                                                       class="text-danger">
                                                        <i class="bi bi-trash"></i> Xóa
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
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>