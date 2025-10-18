
<?php
// ============================================
// FILE: modules/roles/edit.php
// ============================================
$page_title = "Chỉnh sửa Vai trò";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    setFlashMessage('ID không hợp lệ!', 'danger');
    redirect('modules/roles/index.php');
}

// Get role data
$query = "SELECT * FROM roles WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$role = $stmt->fetch();

if (!$role) {
    setFlashMessage('Vai trò không tồn tại!', 'danger');
    redirect('modules/roles/index.php');
}

// Get all permissions
$permissionsQuery = "SELECT * FROM permissions ORDER BY name";
$permissions = $db->query($permissionsQuery)->fetchAll();

// Get current role permissions
$rolePermsQuery = "SELECT permission_id FROM role_permissions WHERE role_id = ?";
$stmt = $db->prepare($rolePermsQuery);
$stmt->execute([$id]);
$rolePermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'process.php';
    if (empty($errors)) {
        $stmt->execute([$id]);
        $role = $stmt->fetch();
        
        // Refresh role permissions
        $stmt = $db->prepare($rolePermsQuery);
        $stmt->execute([$id]);
        $rolePermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Chỉnh sửa Vai trò: <?= $role['name'] ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="id" value="<?= $role['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label required">Tên vai trò</label>
                            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   value="<?= $_POST['name'] ?? $role['name'] ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback d-block"><?= $errors['name'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3"><?= $_POST['description'] ?? $role['description'] ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gán quyền cho vai trò</label>
                            <div class="card">
                                <div class="card-body">
                                    <?php if (empty($permissions)): ?>
                                        <p class="text-muted">Chưa có quyền nào trong hệ thống</p>
                                    <?php else: ?>
                                        <div class="row">
                                            <?php foreach ($permissions as $permission): ?>
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="permissions[]" 
                                                               value="<?= $permission['id'] ?>" 
                                                               id="perm_<?= $permission['id'] ?>"
                                                               <?= (isset($_POST['permissions']) ? in_array($permission['id'], $_POST['permissions'] ?? []) : in_array($permission['id'], $rolePermissions)) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="perm_<?= $permission['id'] ?>">
                                                            <strong><?= $permission['name'] ?></strong>
                                                            <br><small class="text-muted"><?= $permission['description'] ?></small>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllPermissions(true)">
                                                <i class="bi bi-check-all"></i> Chọn tất cả
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllPermissions(false)">
                                                <i class="bi bi-x-lg"></i> Bỏ chọn tất cả
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectAllPermissions(checked) {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(cb => cb.checked = checked);
}
</script>

<?php include '../../includes/footer.php'; ?>
