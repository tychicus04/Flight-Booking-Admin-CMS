<?php
// ============================================
// FILE: modules/bookings/cancel.php
// ============================================
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    setFlashMessage('ID không hợp lệ!', 'danger');
    redirect('modules/bookings/index.php');
}

$database = new Database();
$db = $database->getConnection();

try {
    $updateQuery = "UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?";
    $stmt = $db->prepare($updateQuery);
    $stmt->execute([$id]);
    
    setFlashMessage('Hủy booking thành công!', 'success');
} catch (PDOException $e) {
    setFlashMessage('Lỗi: ' . $e->getMessage(), 'danger');
}

redirect('modules/bookings/index.php');
?>