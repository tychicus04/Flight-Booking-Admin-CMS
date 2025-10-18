
<?php
// ============================================
// FILE: modules/refunds/detail.php
// ============================================
$page_title = "Chi tiết Yêu cầu Hoàn tiền";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    setFlashMessage('ID không hợp lệ!', 'danger');
    redirect('modules/refunds/index.php');
}

// Get refund request details
$query = "SELECT r.*, 
          b.booking_code, b.booking_status, b.payment_status, b.total_amount as booking_amount,
          c.full_name as customer_name, c.email, c.phone,
          u.full_name as processor_name
          FROM refund_requests r 
          JOIN bookings b ON r.booking_id = b.id
          JOIN customers c ON r.customer_id = c.id
          LEFT JOIN users u ON r.processed_by = u.id
          WHERE r.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$refund = $stmt->fetch();

if (!$refund) {
    setFlashMessage('Yêu cầu hoàn tiền không tồn tại!', 'danger');
    redirect('modules/refunds/index.php');
}

// Get wallet info
$walletQuery = "SELECT * FROM wallets WHERE customer_id = ?";
$stmt = $db->prepare($walletQuery);
$stmt->execute([$refund['customer_id']]);
$wallet = $stmt->fetch();
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cash-coin"></i> 
                        Chi tiết Yêu cầu Hoàn tiền #<?= $refund['id'] ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Mã Booking:</strong></p>
                            <h5>
                                <a href="<?= BASE_URL ?>modules/bookings/detail.php?id=<?= $refund['booking_id'] ?>" 
                                   class="text-primary">
                                    <?= $refund['booking_code'] ?>
                                </a>
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Số tiền yêu cầu hoàn:</strong></p>
                            <h4 class="text-danger"><?= formatCurrency($refund['amount']) ?></h4>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Khách hàng:</strong></p>
                            <p><?= $refund['customer_name'] ?></p>
                            <p class="mb-0"><small class="text-muted"><?= $refund['email'] ?? 'N/A' ?></small></p>
                            <p><small class="text-muted"><?= $refund['phone'] ?? 'N/A' ?></small></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Trạng thái:</strong></p>
                            <?php
                            $statusBadges = [
                                'pending' => '<span class="badge bg-warning fs-6">Chờ xử lý</span>',
                                'approved' => '<span class="badge bg-success fs-6">Đã duyệt</span>',
                                'rejected' => '<span class="badge bg-danger fs-6">Đã từ chối</span>'
                            ];
                            echo $statusBadges[$refund['status']];
                            ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="mb-2"><strong>Lý do hoàn tiền:</strong></p>
                        <div class="alert alert-info">
                            <?= nl2br($refund['reason']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Ngày yêu cầu:</strong></p>
                            <p><?= formatDateTime($refund['created_at']) ?></p>
                        </div>
                        <?php if ($refund['processed_at']): ?>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Ngày xử lý:</strong></p>
                                <p><?= formatDateTime($refund['processed_at']) ?></p>
                                <p class="mb-0"><small class="text-muted">Bởi: <?= $refund['processor_name'] ?></small></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($refund['status'] == 'pending'): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Yêu cầu này đang chờ xử lý.</strong> Vui lòng kiểm tra kỹ thông tin trước khi duyệt hoặc từ chối.
                        </div>
                        
                        <div class="d-flex gap-3 justify-content-center mt-4">
                            <form method="POST" action="process.php" onsubmit="return confirm('Bạn có chắc chắn muốn DUYỆT yêu cầu hoàn tiền này?')">
                                <input type="hidden" name="refund_id" value="<?= $refund['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle"></i> Duyệt Hoàn tiền
                                </button>
                            </form>
                            
                            <form method="POST" action="process.php" onsubmit="return confirm('Bạn có chắc chắn muốn TỪ CHỐI yêu cầu hoàn tiền này?')">
                                <input type="hidden" name="refund_id" value="<?= $refund['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="bi bi-x-circle"></i> Từ chối
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Booking Info -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Thông tin Booking</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Trạng thái booking:</strong></p>
                    <p><?= getStatusBadge($refund['booking_status']) ?></p>
                    
                    <p class="mb-2 mt-3"><strong>Trạng thái thanh toán:</strong></p>
                    <p><?= getStatusBadge($refund['payment_status']) ?></p>
                    
                    <p class="mb-2 mt-3"><strong>Tổng tiền booking:</strong></p>
                    <p class="fw-bold"><?= formatCurrency($refund['booking_amount']) ?></p>
                </div>
            </div>

            <!-- Wallet Info -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">Thông tin Ví</h6>
                </div>
                <div class="card-body">
                    <?php if ($wallet): ?>
                        <p class="mb-2"><strong>Số dư hiện tại:</strong></p>
                        <h4 class="text-success"><?= formatCurrency($wallet['balance']) ?></h4>
                        
                        <?php if ($refund['status'] == 'pending'): ?>
                            <hr>
                            <p class="mb-2"><strong>Số dư sau khi hoàn:</strong></p>
                            <h4 class="text-primary">
                                <?= formatCurrency($wallet['balance'] + $refund['amount']) ?>
                            </h4>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">Chưa có ví</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
