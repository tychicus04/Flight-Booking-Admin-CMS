<?php
// ============================================
// FILE: modules/permissions/index.php
// ============================================
$page_title = "Quản lý Quyền";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get all permissions
$query = "SELECT * FROM permissions ORDER BY name";
$permissions = $db->query($query)->fetchAll();
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Danh sách Quyền</h5>
            <div class="card-actions">
                <a href="add.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Thêm Quyền mới
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($permissions)): ?>
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
                                <th>Tên quyền</th>
                                <th>Slug</th>
                                <th>Mô tả</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($permissions as $permission): ?>
                                <tr>
                                    <td><?= $permission['id'] ?></td>
                                    <td><strong><?= $permission['name'] ?></strong></td>
                                    <td><code><?= $permission['slug'] ?></code></td>
                                    <td><?= $permission['description'] ?? '-' ?></td>
                                    <td><?= formatDateTime($permission['created_at']) ?></td>
                                    <td>
                                        <a href="edit.php?id=<?= $permission['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $permission['id'] ?>" 
                                           onclick="return confirmDelete('Bạn có chắc chắn muốn xóa quyền này?')" 
                                           class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
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
