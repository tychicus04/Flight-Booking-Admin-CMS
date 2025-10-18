
<?php
// ============================================
// FILE: modules/users/edit.php
// ============================================
$page_title = "Chỉnh sửa User";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    setFlashMessage('ID không hợp lệ!', 'danger');
    redirect('modules/users/index.php');
}

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('User không tồn tại!', 'danger');
    redirect('modules/users/index.php');
}

// Get roles
$rolesQuery = "SELECT * FROM roles ORDER BY name";
$roles = $db->query($rolesQuery)->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'process.php';
    if (empty($errors)) {
        // Refresh user data
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Chỉnh sửa User: <?= $user['username'] ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Username</label>
                                    <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                           value="<?= $_POST['username'] ?? $user['username'] ?>" required>
                                    <?php if (isset($errors['username'])): ?>
                                        <div class="invalid-feedback d-block"><?= $errors['username'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Email</label>
                                    <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                           value="<?= $_POST['email'] ?? $user['email'] ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback d-block"><?= $errors['email'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Họ và Tên</label>
                            <input type="text" name="full_name" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                                   value="<?= $_POST['full_name'] ?? $user['full_name'] ?>" required>
                            <?php if (isset($errors['full_name'])): ?>
                                <div class="invalid-feedback d-block"><?= $errors['full_name'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mật khẩu mới (để trống nếu không đổi)</label>
                                    <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
                                    <div class="form-text">Tối thiểu 6 ký tự</div>
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback d-block"><?= $errors['password'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Xác nhận mật khẩu</label>
                                    <input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>">
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback d-block"><?= $errors['confirm_password'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Vai trò</label>
                                    <select name="role_id" class="form-select <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>" required>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['id'] ?>" 
                                                <?= (isset($_POST['role_id']) ? $_POST['role_id'] : $user['role_id']) == $role['id'] ? 'selected' : '' ?>>
                                                <?= $role['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['role_id'])): ?>
                                        <div class="invalid-feedback d-block"><?= $errors['role_id'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?= (isset($_POST['status']) ? $_POST['status'] : $user['status']) == 'active' ? 'selected' : '' ?>>
                                            Hoạt động
                                        </option>
                                        <option value="locked" <?= (isset($_POST['status']) ? $_POST['status'] : $user['status']) == 'locked' ? 'selected' : '' ?>>
                                            Bị khóa
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Avatar</label>
                            <?php if (!empty($user['avatar'])): ?>
                                <div class="mb-2">
                                    <img src="<?= BASE_URL ?>uploads/avatars/<?= $user['avatar'] ?>" alt="Current Avatar" class="img-thumbnail" style="max-width: 150px;">
                                </div>
                            <?php endif; ?>
                            <div class="file-upload">
                                <input type="file" name="avatar" id="avatar" accept="image/*" onchange="previewImage(this)">
                                <label for="avatar" class="file-upload-label">
                                    <i class="bi bi-upload"></i> Chọn file mới
                                </label>
                            </div>
                            <div class="file-preview mt-2">
                                <img id="imagePreview" src="" alt="Preview" style="display: none; max-width: 200px;">
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

<?php include '../../includes/footer.php'; ?>