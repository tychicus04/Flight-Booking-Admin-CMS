
<?php
// ============================================
// FILE: modules/roles/process.php
// ============================================

$errors = [];
$id = $_POST['id'] ?? null;

// Validation
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$permissions = $_POST['permissions'] ?? [];

if (empty($name)) {
    $errors['name'] = 'Tên vai trò không được để trống!';
}

// Check unique name
$checkQuery = "SELECT id FROM roles WHERE name = ?" . ($id ? " AND id != ?" : "");
$stmt = $db->prepare($checkQuery);
$params = $id ? [$name, $id] : [$name];
$stmt->execute($params);
if ($stmt->fetch()) {
    $errors['name'] = 'Tên vai trò đã tồn tại!';
}

if (empty($errors)) {
    try {
        $db->beginTransaction();
        
        if ($id) {
            // Update role
            $updateQuery = "UPDATE roles SET name = ?, description = ? WHERE id = ?";
            $stmt = $db->prepare($updateQuery);
            $stmt->execute([$name, $description, $id]);
            
            // Delete old permissions
            $deletePerms = "DELETE FROM role_permissions WHERE role_id = ?";
            $stmt = $db->prepare($deletePerms);
            $stmt->execute([$id]);
            
            $message = 'Cập nhật vai trò thành công!';
        } else {
            // Insert new role
            $insertQuery = "INSERT INTO roles (name, description) VALUES (?, ?)";
            $stmt = $db->prepare($insertQuery);
            $stmt->execute([$name, $description]);
            $id = $db->lastInsertId();
            
            $message = 'Thêm vai trò mới thành công!';
        }
        
        // Insert permissions
        if (!empty($permissions)) {
            $insertPerm = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
            $stmt = $db->prepare($insertPerm);
            foreach ($permissions as $permId) {
                $stmt->execute([$id, $permId]);
            }
        }
        
        $db->commit();
        setFlashMessage($message, 'success');
        
        if (!isset($_POST['id'])) {
            redirect('modules/roles/index.php');
        }
        
    } catch (PDOException $e) {
        $db->rollBack();
        $errors['database'] = 'Lỗi: ' . $e->getMessage();
    }
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        setFlashMessage($error, 'danger');
    }
}
