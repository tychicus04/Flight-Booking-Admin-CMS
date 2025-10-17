<?php
// ============================================
// FILE: includes/sidebar.php
// ============================================
$current_page = basename($_SERVER['PHP_SELF']);
$current_module = basename(dirname($_SERVER['PHP_SELF']));
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="bi bi-airplane-engines"></i>
            <span class="logo-text">Flight Booking</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'index.php' && $current_module != 'users' && $current_module != 'bookings' && $current_module != 'wallet') ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>index.php">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_module == 'bookings' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>modules/bookings/index.php">
                    <i class="bi bi-ticket-perforated"></i>
                    <span class="nav-text">Quản lý Booking</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_module == 'users' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>modules/users/index.php">
                    <i class="bi bi-people"></i>
                    <span class="nav-text">Quản lý User</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_module == 'wallet' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>modules/wallet/index.php">
                    <i class="bi bi-wallet2"></i>
                    <span class="nav-text">Quản lý Ví</span>
                </a>
            </li>
            
            <li class="nav-divider"></li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>auth/logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="nav-text">Đăng xuất</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

