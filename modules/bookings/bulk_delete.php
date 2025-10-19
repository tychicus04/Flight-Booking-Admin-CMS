<?php
// ============================================
// FILE: modules/bookings/bulk_delete.php
// Bulk Delete Bookings
// ============================================
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('modules/bookings/index.php');
}

if (!isset($_POST['ids']) || !is_array($_POST['ids']) || empty($_POST['ids'])) {
    setFlashMessage('Không có booking nào được chọn!', 'danger');
    redirect('modules/bookings/index.php');
}

$database = new Database();
$db = $database->getConnection();

$ids = array_map('intval', $_POST['ids']);
$placeholders = str_repeat('?,', count($ids) - 1) . '?';

try {
    $db->beginTransaction();
    
    // Delete booking flights first (foreign key constraint)
    $query = "DELETE FROM booking_flights WHERE booking_id IN ($placeholders)";
    $stmt = $db->prepare($query);
    $stmt->execute($ids);
    
    // Delete bookings
    $query = "DELETE FROM bookings WHERE id IN ($placeholders)";
    $stmt = $db->prepare($query);
    $stmt->execute($ids);
    
    $deletedCount = $stmt->rowCount();
    
    $db->commit();
    
    // Log activity
    logActivity(
        $_SESSION['user_id'], 
        'bulk_delete', 
        'bookings', 
        null, 
        "Deleted $deletedCount bookings: " . implode(', ', $ids)
    );
    
    setFlashMessage("Đã xóa thành công $deletedCount booking!", 'success');
} catch (Exception $e) {
    $db->rollBack();
    setFlashMessage('Lỗi khi xóa: ' . $e->getMessage(), 'danger');
}

redirect('modules/bookings/index.php');
?>
