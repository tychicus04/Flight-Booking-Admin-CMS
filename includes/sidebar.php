<?php
// ============================================
// FILE: includes/sidebar.php
// ============================================
$current_page = basename($_SERVER['PHP_SELF']);
$current_module = basename(dirname($_SERVER['PHP_SELF']));

// Get current user info
$user_name = $_SESSION['user_name'] ?? 'Admin User';
$user_avatar = $_SESSION['user_avatar'] ?? '';
?>
<aside class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>index.php" class="logo">
            <i class="bi bi-airplane-engines"></i>
            <span class="logo-text">Flight CMS</span>
        </a>
    </div>
    
    <!-- User Profile -->
    <div class="sidebar-user">
        <?php if (!empty($user_avatar)): ?>
            <img src="<?= $user_avatar ?>" alt="<?= htmlspecialchars($user_name) ?>" class="user-avatar">
        <?php else: ?>
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=2196F3&color=fff" alt="<?= htmlspecialchars($user_name) ?>" class="user-avatar">
        <?php endif; ?>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
            <div class="user-status">● Online</div>
        </div>
    </div>
    
    <!-- Search Box -->
    <div class="sidebar-search">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search..." id="sidebarSearch">
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a class="nav-link <?= ($current_page == 'index.php' && $current_module != 'users' && $current_module != 'bookings' && $current_module != 'wallet' && $current_module != 'roles' && $current_module != 'permissions' && $current_module != 'refunds' && $current_module != 'logs') ? 'active' : '' ?>" 
           href="<?= BASE_URL ?>index.php">
            <i class="bi bi-speedometer2"></i>
            <span class="nav-text">Dashboard</span>
        </a>
        
        <!-- Admin Group -->
        <div class="nav-group">
            <a href="#adminSubmenu" 
               class="nav-link nav-parent <?= ($current_module == 'users' || $current_module == 'roles' || $current_module == 'permissions' || $current_module == 'wallet' || ($current_module == 'logs' && $current_page == 'operation_logs.php')) ? 'active' : '' ?>" 
               aria-expanded="<?= ($current_module == 'users' || $current_module == 'roles' || $current_module == 'permissions' || $current_module == 'wallet' || ($current_module == 'logs' && $current_page == 'operation_logs.php')) ? 'true' : 'false' ?>">
                <i class="bi bi-layers"></i>
                <span class="nav-text">Admin</span>
                <i class="bi bi-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu <?= ($current_module == 'users' || $current_module == 'roles' || $current_module == 'permissions' || $current_module == 'wallet' || ($current_module == 'logs' && $current_page == 'operation_logs.php')) ? 'show' : '' ?>" id="adminSubmenu">
                <a class="nav-link <?= $current_module == 'users' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>modules/users/index.php">
                    <i class="bi bi-people"></i>
                    <span class="nav-text">Users</span>
                </a>
                <a class="nav-link <?= $current_module == 'roles' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>modules/roles/index.php">
                    <i class="bi bi-shield-check"></i>
                    <span class="nav-text">Roles</span>
                </a>
                <a class="nav-link <?= $current_module == 'permissions' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>modules/permissions/index.php">
                    <i class="bi bi-key"></i>
                    <span class="nav-text">Permissions</span>
                </a>
                <a class="nav-link <?= $current_module == 'wallet' ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>modules/wallet/index.php">
                    <i class="bi bi-wallet2"></i>
                    <span class="nav-text">Ví</span>
                </a>
                <a class="nav-link <?= ($current_module == 'logs' && $current_page == 'operation_logs.php') ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>modules/logs/operation_logs.php">
                    <i class="bi bi-clock-history"></i>
                    <span class="nav-text">Operation Log</span>
                </a>
            </div>
        </div>
        
        <!-- Booking Group -->
        <div class="nav-group">
            <a href="#bookingSubmenu" 
               class="nav-link nav-parent <?= $current_module == 'bookings' ? 'active' : '' ?>" 
               aria-expanded="<?= $current_module == 'bookings' ? 'true' : 'false' ?>">
                <i class="bi bi-ticket-perforated"></i>
                <span class="nav-text">Booking</span>
                <i class="bi bi-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu <?= $current_module == 'bookings' ? 'show' : '' ?>" id="bookingSubmenu">
                <a class="nav-link <?= ($current_module == 'bookings' && $current_page == 'index.php') ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>modules/bookings/index.php">
                    <i class="bi bi-list"></i>
                    <span class="nav-text">Quản lí Booking</span>
                </a>
                <a class="nav-link <?= ($current_module == 'bookings' && $current_page == 'add.php') ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>modules/bookings/add.php">
                    <i class="bi bi-plus-circle"></i>
                    <span class="nav-text">Tạo mới</span>
                </a>
            </div>
        </div>
        
        <div class="nav-divider"></div>
        
        <!-- Login History -->
        <a class="nav-link <?= ($current_module == 'logs' && $current_page == 'login_history.php') ? 'active' : '' ?>" 
           href="<?= BASE_URL ?>modules/logs/login_history.php">
            <i class="bi bi-clock-history"></i>
            <span class="nav-text">Lịch sử đăng nhập</span>
        </a>
        
        <!-- Logout -->
        <a class="nav-link" href="<?= BASE_URL ?>auth/logout.php">
            <i class="bi bi-box-arrow-right"></i>
            <span class="nav-text">Logout</span>
        </a>
    </nav>
</aside>

