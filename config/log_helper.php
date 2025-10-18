<?php
// ============================================
// FILE: config/log_helper.php
// Helper functions để ghi logs
// ============================================

/**
 * Log login activity
 */
function logLogin($user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $query = "INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id, $ip, $userAgent]);
    } catch (PDOException $e) {
        // Silent fail - don't break login process
        error_log("Failed to log login: " . $e->getMessage());
    }
}

/**
 * Log operation/activity
 */
function logOperation($user_id, $action, $module = null, $details = null) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO operation_logs (user_id, action, module, details) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id, $action, $module, $details]);
    } catch (PDOException $e) {
        // Silent fail
        error_log("Failed to log operation: " . $e->getMessage());
    }
}
?>