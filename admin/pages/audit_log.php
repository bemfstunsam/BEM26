<?php
/**
 * Audit Log — Admin Panel SUIC 2026
 */
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/db.php';

$pageTitle   = 'Audit Log';
$currentPage = 'audit_log';

$fAdmin = trim(mb_substr($_GET['admin'] ?? '', 0, 100));
$fAksi  = trim(mb_substr($_GET['aksi']  ?? '', 0, 100));
$fFrom  = trim($_GET['date_from'] ?? '');
$fTo    = trim($_GET['date_to']   ?? '');
$page   = safePage($_GET['p'] ?? 1);
$perPage = 30;

$validDate = function(string $d): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) return false;
    [$y, $m, $dd] = explode('-', $d);
    return checkdate((int)$m, (int)$dd, (int)$y);
};
if (!$validDate($fFrom)) $fFrom = '';
if (!$validDate($fTo))   $fTo   = '';
if ($fFrom && $fTo && $fFrom > $fTo) [$fFrom, $fTo] = [$fTo, $fFrom];

$conn       = getDBConnection();
$conditions = [];
$params     = [];
$types      = '';

if ($fAdmin !== '') {
    $conditions[] = 'admin_nama LIKE ?';
    $params[]  = '%' . $fAdmin . '%';
    $types    .= 's';
}
if ($fAksi !== '') {
    $conditions[] = 'aksi LIKE ?';
    $params[]  = '%' . $fAksi . '%';
    $types    .= 's';
}
if ($fFrom !== '') {
    $conditions[] = 'DATE(dibuat_pada) >= ?';
    $params[]  = $fFrom;
    $types    .= 's';
}
if ($fTo !== '') {
    $conditions[] = 'DATE(dibuat_pada) <= ?';
    $params[]  = $fTo;
    $types    .= 's';
}

$where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$stmt = $conn->prepare("SELECT COUNT(*) AS n FROM suic_audit_log $where");
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['n'];
$stmt->close();

$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$sql  = "SELECT id, admin_id, admin_nama, aksi, entitas, entitas_id, deskripsi, ip_address, dibuat_pada
         FROM suic_audit_log $where ORDER BY dibuat_pada DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$allP = array_merge($params, [$perPage, $offset]);
$allT = $types . 'ii';
$stmt->bind_param($allT, ...$allP);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$qBase = http_build_query(array_filter([
    'page' => 'audit_log', 'admin' => $fAdmin,
    'aksi' => $fAksi, 'date_from' => $fFrom, 'date_to' => $fTo,
]));

include __DIR__ . '/../includes/layout_head.php';
?>

<!-- Filter -->
<div class="card p-4 mb-5">
    <form method="GET" action="index.php" class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="page" value="audit_log">
        <div>
            <label class="form-label">Admin</label>
            <input type="text" name="admin" value="<?= e($fAdmin) ?>" class="form-input w-36" placeholder="Nama admin">
        </div>
        <div>
            <label class="form-label">Aksi</label>
            <input type="text" name="aksi" value="<?= e($fAksi) ?>" class="form-input w-36" placeholder="login, update…">
        </div>
        <div class="flex items-end gap-2">
            <div>
                <label class="form-label">Dari</label>
                <input type="date" name="date_from" value="<?= e($fFrom) ?>" class="form-input w-36">
            </div>
            <span class="text-gray-600 text-xs mb-2">s/d</span>
            <div>
                <label class="form-label">Sampai</label>
                <input type="date" name="date_to" value="<?= e($fTo) ?>" class="form-input w-36">
            </div>
        </div>
        <div class="flex gap-2 mt-auto">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="index.php?page=audit_log" class="btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card">
    <div class="px-5 py-3.5 border-b border-gray-700 flex items-center justify-between">
        <p class="text-sm text-gray-400">
            Total <span class="text-white font-bold"><?= $total ?></span> entri
        </p>
    </div>

    <?php if (empty($logs)): ?>
    <div class="text-center py-12 text-gray-600 text-sm">
        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Tidak ada log yang ditemukan.
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-700 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="text-left px-5 py-3">Waktu</th>
                    <th class="text-left px-5 py-3">Admin</th>
                    <th class="text-left px-5 py-3">Aksi</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Deskripsi</th>
                    <th class="text-left px-5 py-3 hidden lg:table-cell">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log):
                    $aksiColors = [
                        'login'         => 'text-green-400',
                        'logout'        => 'text-gray-400',
                        'update_status' => 'text-orange-400',
                        'create_admin'  => 'text-blue-400',
                        'edit_admin'    => 'text-yellow-400',
                        'toggle_aktif'  => 'text-red-400',
                    ];
                    $aksiColor = $aksiColors[$log['aksi']] ?? 'text-gray-300';
                ?>
                <tr class="table-row-hover border-b border-gray-800/50">
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">
                        <?= e(date('d M Y', strtotime($log['dibuat_pada']))) ?><br>
                        <span class="text-gray-600"><?= e(date('H:i:s', strtotime($log['dibuat_pada']))) ?></span>
                    </td>
                    <td class="px-5 py-3 text-sm font-medium text-white"><?= e($log['admin_nama']) ?></td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-bold <?= $aksiColor ?>"><?= e($log['aksi']) ?></span>
                        <?php if ($log['entitas'] && $log['entitas_id']): ?>
                        <span class="text-xs text-gray-600 block mt-0.5"><?= e($log['entitas']) ?> #<?= (int)$log['entitas_id'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500 hidden md:table-cell max-w-xs">
                        <span class="truncate block" title="<?= e($log['deskripsi'] ?? '') ?>"><?= e(mb_substr($log['deskripsi'] ?? '', 0, 80)) ?></span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-600 hidden lg:table-cell font-mono">
                        <?= e($log['ip_address'] ?? '') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-between px-5 py-4 border-t border-gray-700">
        <p class="text-xs text-gray-600">Halaman <?= $page ?> dari <?= $totalPages ?></p>
        <div class="flex gap-1.5">
            <?php if ($page > 1): ?>
            <a href="index.php?<?= e($qBase) ?>&p=<?= $page - 1 ?>" class="btn-secondary py-1 px-2.5 text-xs">‹ Prev</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
            <a href="index.php?<?= e($qBase) ?>&p=<?= $page + 1 ?>" class="btn-secondary py-1 px-2.5 text-xs">Next ›</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/layout_foot.php'; ?>
