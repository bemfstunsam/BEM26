<?php
/**
 * Admin Panel Layout: HTML head + sidebar.
 * Di-include setelah $pageTitle dan $currentPage di-set oleh halaman.
 */
$flash      = getFlash();
$adminNama  = e($_SESSION['admin_nama'] ?? 'Admin');
$adminPeran = $_SESSION['admin_peran'] ?? 'admin';
$base       = adminBaseUrl(); // e.g. /bem/admin/

// Navigasi items
$navItems = [
    ['page' => 'dashboard',           'label' => 'Dashboard',       'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>'],
    ['page' => 'registrations',       'label' => 'Registrasi',      'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>'],
    ['page' => 'admins',              'label' => 'Administrator',   'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>', 'superOnly' => true],
    ['page' => 'audit_log',           'label' => 'Audit Log',       'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?> — SUIC 2026 Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #111827; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #4B5563; }
        .table-row-hover:hover td { background-color: rgba(249,115,22,0.04); }
        .sidebar-link { display:flex; align-items:center; gap:0.75rem; padding:0.625rem 0.75rem; border-radius:0.75rem; font-size:0.875rem; font-weight:500; transition:background .15s,color .15s; }
        .sidebar-link.active { background:rgba(249,115,22,0.15); color:#FB923C; }
        .sidebar-link:not(.active) { color:#9CA3AF; }
        .sidebar-link:not(.active):hover { background:#1F2937; color:#F9FAFB; }
        .stat-card { background:#1F2937; border:1px solid #374151; border-radius:1rem; padding:1.25rem; }
        .form-input { background:#111827; border:1px solid #374151; color:#F9FAFB; border-radius:0.625rem; padding:0.6rem 0.875rem; width:100%; font-size:0.875rem; outline:none; transition:border-color .2s,box-shadow .2s; }
        .form-input:focus { border-color:#F97316; box-shadow:0 0 0 3px rgba(249,115,22,.15); }
        .form-input::placeholder { color:#6B7280; }
        .form-label { display:block; font-size:0.78rem; font-weight:600; color:#D1D5DB; margin-bottom:0.35rem; letter-spacing:.03em; text-transform:uppercase; }
        .btn-primary { display:inline-flex; align-items:center; gap:0.5rem; background:#F97316; color:#fff; font-weight:700; padding:0.55rem 1.25rem; border-radius:0.625rem; font-size:0.875rem; transition:background .2s; cursor:pointer; border:none; }
        .btn-primary:hover { background:#EA580C; }
        .btn-secondary { display:inline-flex; align-items:center; gap:0.5rem; background:#1F2937; color:#D1D5DB; font-weight:600; padding:0.55rem 1.25rem; border-radius:0.625rem; font-size:0.875rem; border:1px solid #374151; transition:background .2s,color .2s; cursor:pointer; }
        .btn-secondary:hover { background:#374151; color:#fff; }
        .btn-danger { display:inline-flex; align-items:center; gap:0.5rem; background:rgba(239,68,68,0.15); color:#F87171; font-weight:600; padding:0.55rem 1.25rem; border-radius:0.625rem; font-size:0.875rem; border:1px solid rgba(239,68,68,0.3); transition:background .2s; cursor:pointer; }
        .btn-danger:hover { background:rgba(239,68,68,0.25); }
        .card { background:#1F2937; border:1px solid #374151; border-radius:1rem; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 flex overflow-hidden" style="height:100dvh">

<!-- ══ SIDEBAR ══ -->
<aside id="sidebar" class="w-60 bg-gray-900 border-r border-gray-800 flex flex-col flex-shrink-0 z-30">

    <!-- Logo -->
    <div class="px-5 py-4 border-b border-gray-800 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-white font-extrabold text-sm">S</span>
            </div>
            <div class="min-w-0">
                <p class="text-white font-bold text-sm leading-none">SUIC 2026</p>
                <p class="text-orange-400 text-xs mt-0.5 font-medium">Admin Panel</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">
        <p class="px-3 text-xs font-semibold text-gray-600 uppercase tracking-widest mb-2 mt-1">Menu</p>
        <?php foreach ($navItems as $item):
            if (!empty($item['superOnly']) && !isSuperAdmin()) continue;
            $isActive = ($currentPage ?? '') === $item['page'];
        ?>
        <a href="<?= e($base . 'index.php?page=' . $item['page']) ?>"
           class="sidebar-link <?= $isActive ? 'active' : '' ?>">
            <?= $item['icon'] ?>
            <?= e($item['label']) ?>
        </a>
        <?php endforeach; ?>

        <div class="border-t border-gray-800 my-3"></div>
        <p class="px-3 text-xs font-semibold text-gray-600 uppercase tracking-widest mb-2">Export</p>
        <a href="<?= e($base . 'export.php') ?>" target="_blank"
           class="sidebar-link">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </nav>

    <!-- User + Logout -->
    <div class="px-3 py-3 border-t border-gray-800 flex-shrink-0 space-y-0.5">
        <div class="px-3 py-2">
            <p class="text-sm font-semibold text-white truncate"><?= $adminNama ?></p>
            <p class="text-xs text-gray-500 mt-0.5"><?= $adminPeran === 'super_admin' ? 'Super Admin' : 'Admin' ?></p>
        </div>
        <a href="<?= e($base . 'logout.php') ?>"
           onclick="return confirm('Keluar dari admin panel?')"
           class="sidebar-link text-red-400 hover:text-red-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Keluar
        </a>
    </div>
</aside>

<!-- ══ MAIN CONTENT ══ -->
<div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    <!-- Top bar -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-3.5 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
            <button id="sidebar-toggle" class="md:hidden text-gray-400 hover:text-white mr-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-base font-bold text-white"><?= e($pageTitle ?? '') ?></h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-500 hidden sm:block">SUIC 2026 IDC</span>
            <div class="w-8 h-8 bg-orange-500/20 rounded-full flex items-center justify-center">
                <span class="text-orange-400 text-xs font-bold"><?= mb_strtoupper(mb_substr($adminNama, 0, 1)) ?></span>
            </div>
        </div>
    </header>

    <?php if ($flash): ?>
    <div class="flex-shrink-0 px-6 pt-4">
        <?php
        $flashColors = [
            'success' => 'bg-green-500/10 border-green-500/30 text-green-400',
            'error'   => 'bg-red-500/10   border-red-500/30   text-red-400',
            'info'    => 'bg-blue-500/10  border-blue-500/30  text-blue-400',
            'warning' => 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400',
        ];
        $fc = $flashColors[$flash['type']] ?? $flashColors['info'];
        $fi = ['success' => '✅', 'error' => '⚠️', 'info' => 'ℹ️', 'warning' => '⚠️'][$flash['type']] ?? 'ℹ️';
        ?>
        <div class="border rounded-xl px-4 py-3 text-sm font-medium <?= $fc ?>">
            <?= $fi ?> <?= e($flash['message']) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scrollable page content -->
    <main class="flex-1 overflow-y-auto p-6">
