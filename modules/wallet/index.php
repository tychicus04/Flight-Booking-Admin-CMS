<?php
// ============================================
// FILE: modules/wallet/index.php
// ============================================
$page_title = "Quản lý Ví";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Filters
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "c.full_name LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
}

if (isset($_GET['balance_min']) && $_GET['balance_min'] !== '') {
    $where[] = "w.balance >= ?";
    $params[] = $_GET['balance_min'];
}

if (isset($_GET['balance_max']) && $_GET['balance_max'] !== '') {
    $where[] = "w.balance <= ?";
    $params[] = $_GET['balance_max'];
}

$whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total
$countQuery = "SELECT COUNT(*) as total 
               FROM wallets w 
               JOIN customers c ON w.customer_id = c.id 
               $whereSQL";
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Get wallets
$query = "SELECT w.*, c.full_name, c.email, c.phone 
          FROM wallets w 
          JOIN customers c ON w.customer_id = c.id 
          $whereSQL 
          ORDER BY w.balance DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$wallets = $stmt->fetchAll();
?>

<div class="container-fluid">
    <!-- Filter Section -->
    <div class="filter-section" id="filterSection">
        <div class="filter-header" onclick="toggleFilter()">
            <h6><i class="bi bi-funnel"></i> Bộ lọc</h6>
            <i class="bi bi-chevron-up"></i>
        </div>
        <div class="filter-body">
            <form method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tìm kiếm khách hàng</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tên khách hàng..." 
                               value="<?= $_GET['search'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Số dư từ</label>
                        <input type="number" name="balance_min" class="form-control" 
                               min="0" step="1000" value="<?= $_GET['balance_min'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Số dư đến</label>
                        <input type="number" name="balance_max" class="form-control" 
                               min="0" step="1000" value="<?= $_GET['balance_max'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Tìm
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Tổng ví</h6>
                            <h3 class="mb-0">
                                <?php 
                                $totalWallets = $db->query("SELECT COUNT(*) as total FROM wallets")->fetch()['total'];
                                echo number_format($totalWallets);
                                ?>
                            </h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-wallet2 fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Tổng số dư</h6>
                            <h3 class="mb-0 text-success">
                                <?php 
                                $totalBalance = $db->query("SELECT SUM(balance) as total FROM wallets")->fetch()['total'];
                                echo formatCurrency($totalBalance);
                                ?>
                            </h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-currency-dollar fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">GD hôm nay</h6>
                            <h3 class="mb-0">
                                <?php 
                                $todayTrans = $db->query("SELECT COUNT(*) as total FROM wallet_transactions 
                                                         WHERE DATE(created_at) = CURDATE()")->fetch()['total'];
                                echo number_format($todayTrans);
                                ?>
                            </h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-arrow-left-right fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Danh sách Ví</h5>
        </div>
        <div class="card-body">
            <?php if (empty($wallets)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>Không có dữ liệu</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Số dư hiện tại</th>
                                <th>GD cuối</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wallets as $wallet): ?>
                                <tr>
                                    <td><?= $wallet['id'] ?></td>
                                    <td>
                                        <strong><?= $wallet['full_name'] ?></strong>
                                    </td>
                                    <td><?= $wallet['email'] ?? '-' ?></td>
                                    <td><?= $wallet['phone'] ?? '-' ?></td>
                                    <td>
                                        <span class="fw-bold <?= $wallet['balance'] > 0 ? 'text-success' : 'text-muted' ?>">
                                            <?= formatCurrency($wallet['balance']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $wallet['last_transaction_at'] ? formatDateTime($wallet['last_transaction_at']) : 'Chưa có' ?>
                                    </td>
                                    <td>
                                        <a href="history.php?wallet_id=<?= $wallet['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Lịch sử giao dịch">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-success" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#adjustModal<?= $wallet['id'] ?>"
                                                title="Điều chỉnh số dư">
                                            <i class="bi bi-plus-slash-minus"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Adjust Modal -->
                                <div class="modal fade" id="adjustModal<?= $wallet['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Điều chỉnh số dư - <?= $wallet['full_name'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="adjust.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="wallet_id" value="<?= $wallet['id'] ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Số dư hiện tại</label>
                                                        <input type="text" class="form-control" 
                                                               value="<?= formatCurrency($wallet['balance']) ?>" readonly>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label required">Loại điều chỉnh</label>
                                                        <select name="adjust_type" class="form-select" required>
                                                            <option value="add">Cộng tiền</option>
                                                            <option value="subtract">Trừ tiền</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label required">Số tiền (VNĐ)</label>
                                                        <input type="number" name="amount" class="form-control" 
                                                               min="1000" step="1000" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label required">Lý do điều chỉnh</label>
                                                        <textarea name="description" class="form-control" 
                                                                  rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
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

<?php include '../../includes/footer.php'; ?>