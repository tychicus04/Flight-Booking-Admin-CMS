
<?php
// ============================================
// FILE: modules/permissions/process.php
// ============================================

$errors = [];
$id = $_POST['id'] ?? null;

// Validation
$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($name)) {
    $errors['name'] = 'Tên quyền không được để trống!';
}

if (empty($slug)) {
    $errors['slug'] = 'Slug không được để trống!';
} elseif (!preg_match('/^[a-z0-9_]+$/', $slug)) {
    $errors['slug'] = 'Slug chỉ được chứa chữ thường, số và dấu gạch dưới!';
}

// Check unique slug
$checkQuery = "SELECT id FROM permissions WHERE slug = ?" . ($id ? " AND id != ?" : "");
$stmt = $db->prepare($checkQuery);
$params = $id ? [$slug, $id] : [$slug];
$stmt->execute($params);
if ($stmt->fetch()) {
    $errors['slug'] = 'Slug đã tồn tại!';
}

if (empty($errors)) {
    try {
        if ($id) {
            // Update
            $updateQuery = "UPDATE permissions SET name = ?, slug = ?, description = ? WHERE id = ?";
            $stmt = $db->prepare($updateQuery);
            $stmt->execute([$name, $slug, $description, $id]);
            setFlashMessage('Cập nhật quyền thành công!', 'success');
        } else {
            // Insert
            $insertQuery = "INSERT INTO permissions (name, slug, description) VALUES (?, ?, ?)";
            $stmt = $db->prepare($insertQuery);
            $stmt->execute([$name, $slug, $description]);
            setFlashMessage('Thêm quyền mới thành công!', 'success');
            redirect('modules/permissions/index.php');
        }
    } catch (PDOException $e) {
        $errors['database'] = 'Lỗi: ' . $e->getMessage();
    }
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        setFlashMessage($error, 'danger');
    }
}
