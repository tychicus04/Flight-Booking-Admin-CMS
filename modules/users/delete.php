
<?php
// ============================================
// FILE: modules/users/delete.php
// ============================================
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    setFlashMessage('ID không hợp lệ!', 'danger');
    redirect('modules/users/index.php');
}

// Không cho phép xóa chính mình
if ($id == $_SESSION['user_id']) {
    setFlashMessage('Bạn không thể xóa tài khoản của chính mình!', 'danger');
    redirect('modules/users/index.php');
}

$database = new Database();
$db = $database->getConnection();

try {
    // Get user info to delete avatar
    $query = "SELECT avatar FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        setFlashMessage('User không tồn tại!', 'danger');
        redirect('modules/users/index.php');
    }
    
    // Delete avatar file
    if (!empty($user['avatar']) && file_exists(UPLOAD_PATH . 'avatars/' . $user['avatar'])) {
        unlink(UPLOAD_PATH . 'avatars/' . $user['avatar']);
    }
    
    // Delete user
    $deleteQuery = "DELETE FROM users WHERE id = ?";
    $stmt = $db->prepare($deleteQuery);
    $stmt->execute([$id]);
    
    setFlashMessage('Xóa user thành công!', 'success');
} catch (PDOException $e) {
    setFlashMessage('Lỗi khi xóa user: ' . $e->getMessage(), 'danger');
}

redirect('modules/users/index.php');
?>