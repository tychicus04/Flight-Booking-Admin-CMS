
<?php
// ============================================
// FILE: modules/permissions/delete.php
// ============================================
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    setFlashMessage('ID không hợp lệ!', 'danger');
    redirect('modules/permissions/index.php');
}

$database = new Database();
$db = $database->getConnection();

try {
    // Check if permission is being used
    $checkQuery = "SELECT COUNT(*) as count FROM role_permissions WHERE permission_id = ?";
    $stmt = $db->prepare($checkQuery);
    $stmt->execute([$id]);
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        setFlashMessage('Không thể xóa quyền đang được gán cho vai trò!', 'danger');
        redirect('modules/permissions/index.php');
    }
    
    // Delete permission
    $deleteQuery = "DELETE FROM permissions WHERE id = ?";
    $stmt = $db->prepare($deleteQuery);
    $stmt->execute([$id]);
    
    setFlashMessage('Xóa quyền thành công!', 'success');
} catch (PDOException $e) {
    setFlashMessage('Lỗi: ' . $e->getMessage(), 'danger');
}

redirect('modules/permissions/index.php');
?>