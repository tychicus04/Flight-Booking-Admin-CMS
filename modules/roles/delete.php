
<?php
// ============================================
// FILE: modules/roles/delete.php
// ============================================
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id || $id <= 3) {
    setFlashMessage('Không thể xóa vai trò mặc định!', 'danger');
    redirect('modules/roles/index.php');
}

$database = new Database();
$db = $database->getConnection();

try {
    // Check if role is being used
    $checkQuery = "SELECT COUNT(*) as count FROM users WHERE role_id = ?";
    $stmt = $db->prepare($checkQuery);
    $stmt->execute([$id]);
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        setFlashMessage('Không thể xóa vai trò đang được sử dụng bởi ' . $count . ' user!', 'danger');
        redirect('modules/roles/index.php');
    }
    
    // Delete role (permissions will be deleted by CASCADE)
    $deleteQuery = "DELETE FROM roles WHERE id = ?";
    $stmt = $db->prepare($deleteQuery);
    $stmt->execute([$id]);
    
    setFlashMessage('Xóa vai trò thành công!', 'success');
} catch (PDOException $e) {
    setFlashMessage('Lỗi: ' . $e->getMessage(), 'danger');
}

redirect('modules/roles/index.php');
?>