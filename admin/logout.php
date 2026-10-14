<?php
/**
 * Logout Admin Panel SUIC 2026
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    require_once __DIR__ . '/../config/db.php';
    $conn = getDBConnection();
    auditLog($conn, 'logout', 'admin', (int)$_SESSION['admin_id'], 'Logout');
    $conn->close();
}

// Invalidasi sesi sepenuhnya
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

header('Location: login.php');
exit;
