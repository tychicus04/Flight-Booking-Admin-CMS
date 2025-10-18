
<?php
// ============================================
// FILE: modules/wallet/adjust.php
// ============================================
require_once '../../auth/check_auth.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('modules/wallet/index.php');
}

$wallet_id = isset($_POST['wallet_id']) ? (int)$_POST['wallet_id'] : 0;
$adjust_type = $_POST['adjust_type'] ?? '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$description = trim($_POST['description'] ?? '');

// Validation
if (!$wallet_id || !$adjust_type || !$amount || empty($description)) {
    setFlashMessage('Vui lòng điền đầy đủ thông tin!', 'danger');
    redirect('modules/wallet/index.php');
}

if ($amount <= 0) {
    setFlashMessage('Số tiền phải lớn hơn 0!', 'danger');
    redirect('modules/wallet/index.php');
}

$database = new Database();
$db = $database->getConnection();

try {
    $db->beginTransaction();
    
    // Get current balance
    $walletQuery = "SELECT balance FROM wallets WHERE id = ? FOR UPDATE";
    $stmt = $db->prepare($walletQuery);
    $stmt->execute([$wallet_id]);
    $wallet = $stmt->fetch();
    
    if (!$wallet) {
        throw new Exception('Ví không tồn tại!');
    }
    
    $balance_before = $wallet['balance'];
    
    // Calculate new balance
    if ($adjust_type === 'add') {
        $balance_after = $balance_before + $amount;
        $transaction_amount = $amount;
    } else {
        if ($balance_before < $amount) {
            throw new Exception('Số dư không đủ để thực hiện giao dịch!');
        }
        $balance_after = $balance_before - $amount;
        $transaction_amount = -$amount;
    }
    
    // Update wallet balance
    $updateWallet = "UPDATE wallets SET balance = ?, last_transaction_at = NOW() WHERE id = ?";
    $stmt = $db->prepare($updateWallet);
    $stmt->execute([$balance_after, $wallet_id]);
    
    // Insert transaction record
    $insertTrans = "INSERT INTO wallet_transactions 
                   (wallet_id, transaction_type, amount, balance_before, balance_after, description, created_by) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($insertTrans);
    $stmt->execute([
        $wallet_id,
        'admin_adjust',
        $transaction_amount,
        $balance_before,
        $balance_after,
        $description,
        $_SESSION['user_id']
    ]);
    
    $db->commit();
    
    $message = $adjust_type === 'add' 
        ? "Đã cộng " . formatCurrency($amount) . " vào ví thành công!" 
        : "Đã trừ " . formatCurrency($amount) . " khỏi ví thành công!";
    
    setFlashMessage($message, 'success');
    
} catch (Exception $e) {
    $db->rollBack();
    setFlashMessage('Lỗi: ' . $e->getMessage(), 'danger');
}

redirect('modules/wallet/index.php');
?>