
<?php
// ============================================
// FILE: modules/permissions/add.php
// ============================================
$page_title = "Thêm Quyền Mới";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'process.php';
    if (empty($errors)) {
        redirect('modules/permissions/index.php');
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thêm Quyền Mới</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label required">Tên quyền</label>
                            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   value="<?= $_POST['name'] ?? '' ?>" 
                                   placeholder="VD: Quản lý Khách hàng" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback d-block"><?= $errors['name'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Slug</label>
                            <input type="text" name="slug" class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" 
                                   value="<?= $_POST['slug'] ?? '' ?>" 
                                   placeholder="VD: manage_customers" required>
                            <div class="form-text">Chỉ sử dụng chữ thường, số và dấu gạch dưới</div>
                            <?php if (isset($errors['slug'])): ?>
                                <div class="invalid-feedback d-block"><?= $errors['slug'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3"><?= $_POST['description'] ?? '' ?></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Lưu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
