<?php
// ============================================
// FILE: auth/check_auth.php
// ============================================
session_start();
require_once __DIR__ . '/../config/constants.php';

if (!isset($_SESSION['user_id'])) {
    redirect('auth/login.php');
}
?>