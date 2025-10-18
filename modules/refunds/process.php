
<?php
// ============================================
// FILE: modules/refunds/process.php
// ============================================
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('modules/refunds/index.php');
}

$refund_id = isset($_POST['refund_id']) ? (int)$_POST['refund_id'] : 0;
$action = $_POST['action'] ?? '';

if (!$refund_id || !in_array($action, ['approve', 'reject'])) {
    setFlashMessage('Dữ liệu không hợp lệ!', 'danger');
    redirect('modules/refunds/index.php');
}

$database = new Database();
$db = $database->getConnection();

try {
    $db->beginTransaction();
    
    // Get refund request
    $query = "SELECT r.*, b.customer_id, w.id as wallet_id, w.balance 
              FROM refund_requests r 
              JOIN bookings b ON r.booking_id = b.id
              LEFT JOIN wallets w ON w.customer_id = b.customer_id
              WHERE r.id = ? AND r.status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute([$refund_id]);
    $refund = $stmt->fetch();
    
    if (!$refund) {
        throw new Exception('Yêu cầu hoàn tiền không tồn tại hoặc đã được xử lý!');
    }
    
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    
    // Update refund request status
    $updateRefund = "UPDATE refund_requests 
                     SET status = ?, processed_by = ?, processed_at = NOW() 
                     WHERE id = ?";
    $stmt = $db->prepare($updateRefund);
    $stmt->execute([$new_status, $_SESSION['user_id'], $refund_id]);
    
    if ($action === 'approve') {
        // Update booking payment status to refunded
        $updateBooking = "UPDATE bookings SET payment_status = 'refunded' WHERE id = ?";
        $stmt = $db->prepare($updateBooking);
        $stmt->execute([$refund['booking_id']]);
        
        // Check if wallet exists
        if (!$refund['wallet_id']) {
            // Create wallet if not exists
            $createWallet = "INSERT INTO wallets (customer_id, balance) VALUES (?, 0)";
            $stmt = $db->prepare($createWallet);
            $stmt->execute([$refund['customer_id']]);
            $wallet_id = $db->lastInsertId();
            $current_balance = 0;
        } else {
            $wallet_id = $refund['wallet_id'];
            $current_balance = $refund['balance'];
        }
        
        $new_balance = $current_balance + $refund['amount'];
        
        // Update wallet balance
        $updateWallet = "UPDATE wallets SET balance = ?, last_transaction_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($updateWallet);
        $stmt->execute([$new_balance, $wallet_id]);
        
        // Create transaction record
        $insertTrans = "INSERT INTO wallet_transactions 
                       (wallet_id, transaction_type, amount, balance_before, balance_after, description, reference_id, created_by) 
                       VALUES (?, 'refund', ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insertTrans);
        $stmt->execute([
            $wallet_id,
            $refund['amount'],
            $current_balance,
            $new_balance,
            'Hoàn tiền booking ' . $refund['booking_id'],
            $refund['booking_id'],
            $_SESSION['user_id']
        ]);
        
        $message = 'Đã duyệt yêu cầu hoàn tiền và cộng ' . formatCurrency($refund['amount']) . ' vào ví khách hàng!';
        $type = 'success';
    } else {
        $message = 'Đã từ chối yêu cầu hoàn tiền!';
        $type = 'info';
    }
    
    $db->commit();
    setFlashMessage($message, $type);
    
} catch (Exception $e) {
    $db->rollBack();
    setFlashMessage('Lỗi: ' . $e->getMessage(), 'danger');
}

redirect('modules/refunds/index.php');
?>