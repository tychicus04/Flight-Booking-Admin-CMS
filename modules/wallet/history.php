<?php
// ============================================
// FILE: modules/wallet/history.php
// ============================================
$page_title = "Lịch sử Giao dịch";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$wallet_id = isset($_GET['wallet_id']) ? (int)$_GET['wallet_id'] : 0;

if (!$wallet_id) {
    setFlashMessage('ID ví không hợp lệ!', 'danger');
    redirect('modules/wallet/index.php');
}

// Get wallet info
$walletQuery = "SELECT w.*, c.full_name, c.email 
                FROM wallets w 
                JOIN customers c ON w.customer_id = c.id 
                WHERE w.id = ?";
$stmt = $db->prepare($walletQuery);
$stmt->execute([$wallet_id]);
$wallet = $stmt->fetch();

if (!$wallet) {
    setFlashMessage('Ví không tồn tại!', 'danger');
    redirect('modules/wallet/index.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 30;
$offset = ($page - 1) * $limit;

// Filters
$where = ["wallet_id = ?"];
$params = [$wallet_id];

if (!empty($_GET['type'])) {
    $where[] = "transaction_type = ?";
    $params[] = $_GET['type'];
}

if (!empty($_GET['date_from'])) {
    $where[] = "DATE(created_at) >= ?";
    $params[] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $where[] = "DATE(created_at) <= ?";
    $params[] = $_GET['date_to'];
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// Get total
$countQuery = "SELECT COUNT(*) as total FROM wallet_transactions $whereSQL";
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Get transactions
$query = "SELECT wt.*, u.full_name as admin_name 
          FROM wallet_transactions wt 
          LEFT JOIN users u ON wt.created_by = u.id 
          $whereSQL 
          ORDER BY wt.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Calculate statistics
$statsQuery = "SELECT 
                SUM(CASE WHEN transaction_type IN ('deposit', 'refund', 'admin_adjust') AND amount > 0 THEN amount ELSE 0 END) as total_in,
                SUM(CASE WHEN transaction_type = 'payment' OR (transaction_type = 'admin_adjust' AND amount < 0) THEN ABS(amount) ELSE 0 END) as total_out,
                COUNT(*) as total_trans
               FROM wallet_transactions 
               WHERE wallet_id = ?";
$stmt = $db->prepare($statsQuery);
$stmt->execute([$wallet_id]);
$stats = $stmt->fetch();
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Wallet Info -->
    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-person-circle fs-1 text-primary mb-3"></i>
                    <h4><?= $wallet['full_name'] ?></h4>
                    <p class="text-muted"><?= $wallet['email'] ?? 'Chưa có email' ?></p>
                    <hr>
                    <h6 class="text-muted mb-2">Số dư hiện tại</h6>
                    <h2 class="text-success mb-0"><?= formatCurrency($wallet['balance']) ?></h2>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Tổng tiền vào</h6>
                            <h4 class="text-success mb-0"><?= formatCurrency($stats['total_in']) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-left-danger">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Tổng tiền ra</h6>
                            <h4 class="text-danger mb-0"><?= formatCurrency($stats['total_out']) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Tổng giao dịch</h6>
                            <h4 class="text-info mb-0"><?= number_format($stats['total_trans']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card">
                <div class="card-body">
                    <form method="GET">
                        <input type="hidden" name="wallet_id" value="<?= $wallet_id ?>">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">Tất cả loại GD</option>
                                    <option value="deposit" <?= ($_GET['type'] ?? '') == 'deposit' ? 'selected' : '' ?>>Nạp tiền</option>
                                    <option value="payment" <?= ($_GET['type'] ?? '') == 'payment' ? 'selected' : '' ?>>Thanh toán</option>
                                    <option value="refund" <?= ($_GET['type'] ?? '') == 'refund' ? 'selected' : '' ?>>Hoàn tiền</option>
                                    <option value="admin_adjust" <?= ($_GET['type'] ?? '') == 'admin_adjust' ? 'selected' : '' ?>>Admin điều chỉnh</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_from" class="form-control form-control-sm" 
                                       value="<?= $_GET['date_from'] ?? '' ?>" placeholder="Từ ngày">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_to" class="form-control form-control-sm" 
                                       value="<?= $_GET['date_to'] ?? '' ?>" placeholder="Đến ngày">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-search"></i> Lọc
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Lịch sử Giao dịch</h5>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>Chưa có giao dịch</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Thời gian</th>
                                <th>Loại giao dịch</th>
                                <th>Số tiền</th>
                                <th>Số dư trước</th>
                                <th>Số dư sau</th>
                                <th>Người thực hiện</th>
                                <th>Nội dung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $trans): ?>
                                <tr>
                                    <td><?= $trans['id'] ?></td>
                                    <td><?= formatDateTime($trans['created_at']) ?></td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'deposit' => '<span class="badge bg-success">Nạp tiền</span>',
                                            'payment' => '<span class="badge bg-warning">Thanh toán</span>',
                                            'refund' => '<span class="badge bg-info">Hoàn tiền</span>',
                                            'admin_adjust' => '<span class="badge bg-secondary">Admin điều chỉnh</span>'
                                        ];
                                        echo $typeLabels[$trans['transaction_type']];
                                        ?>
                                    </td>
                                    <td>
                                        <strong class="<?= $trans['amount'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $trans['amount'] > 0 ? '+' : '' ?><?= formatCurrency($trans['amount']) ?>
                                        </strong>
                                    </td>
                                    <td><?= formatCurrency($trans['balance_before']) ?></td>
                                    <td><strong><?= formatCurrency($trans['balance_after']) ?></strong></td>
                                    <td><?= $trans['admin_name'] ?? '-' ?></td>
                                    <td>
                                        <small><?= $trans['description'] ?></small>
                                        <?php if ($trans['reference_id']): ?>
                                            <br><small class="text-muted">Ref: #<?= $trans['reference_id'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?wallet_id=<?= $wallet_id ?>&page=<?= $i ?><?= http_build_query(array_diff_key($_GET, ['wallet_id' => '', 'page' => ''])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.border-left-success {
    border-left: 4px solid #1cc88a;
}
.border-left-danger {
    border-left: 4px solid #e74a3b;
}
.border-left-info {
    border-left: 4px solid #36b9cc;
}
</style>

<?php include '../../includes/footer.php'; ?>
