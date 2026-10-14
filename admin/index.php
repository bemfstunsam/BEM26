<?php
/**
 * Admin Panel Router — SUIC 2026
 * Entry point: /bem/admin/index.php?page=X
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/auth.php';

requireAuth();

$page    = trim($_GET['page'] ?? 'dashboard');
$allowed = ['dashboard', 'registrations', 'registration_detail', 'admins', 'audit_log'];

if (!in_array($page, $allowed, true)) {
    $page = 'dashboard';
}

// Redirect registrations SUIC ke dedicated router: /bem/suic/admin/index.php
if ($page === 'registrations') {
    $params = $_GET;
    unset($params['page']);
    $qs = http_build_query($params);
    header('Location: /bem/suic/admin/index.php' . ($qs !== '' ? '?' . $qs : ''));
    exit;
}

if ($page === 'registration_detail') {
    $id = (int)($_GET['id'] ?? 0);
    header('Location: /bem/suic/admin/index.php?page=detail&id=' . $id);
    exit;
}

// Halaman admins hanya untuk super_admin
if ($page === 'admins' && !isSuperAdmin()) {
    $page = 'dashboard';
}

$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (!file_exists($pageFile)) {
    $page     = 'dashboard';
    $pageFile = __DIR__ . '/pages/dashboard.php';
}

include $pageFile;
