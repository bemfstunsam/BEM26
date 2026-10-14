<?php
/**
 * Daftar Registrasi — Admin Panel SUIC 2026
 * Fitur: search, filter kategori/negara/status/tanggal, pagination, sorting
 */
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/db.php';

$pageTitle   = 'Registrasi';
$currentPage = 'registrations';

// ── Parameter request ────────────────────────────────────────
$search    = trim(mb_substr($_GET['q']       ?? '', 0, 100));
$fKategori = trim($_GET['kategori']          ?? '');
$fNegara   = trim(mb_substr($_GET['negara']  ?? '', 0, 100));
$fStatus   = trim($_GET['status']            ?? '');
$fDateFrom = trim($_GET['date_from']         ?? '');
$fDateTo   = trim($_GET['date_to']           ?? '');
$page      = safePage($_GET['p']   ?? 1);
$perPage   = safePerPage($_GET['pp'] ?? 20, 20, 100);
[$sortCol, $sortDir] = safeSort($_GET['sort'] ?? 'created_at', $_GET['dir'] ?? 'DESC');

// Validasi filter enum
if (!in_array($fKategori, array_merge([''], IDC_CATEGORIES), true)) $fKategori = '';
if (!in_array($fStatus, array_merge([''], REG_STATUSES), true))     $fStatus   = '';

// Validasi format tanggal
$validDate = function(string $d): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) return false;
    [$y, $m, $dd] = explode('-', $d);
    return checkdate((int)$m, (int)$dd, (int)$y);
};
if (!$validDate($fDateFrom)) $fDateFrom = '';
if (!$validDate($fDateTo))   $fDateTo   = '';
if ($fDateFrom && $fDateTo && $fDateFrom > $fDateTo) {
    // Swap jika terbalik
    [$fDateFrom, $fDateTo] = [$fDateTo, $fDateFrom];
}

// ── Query builder ─────────────────────────────────────────────
$conn       = getDBConnection();
$conditions = [];
$params     = [];
$types      = '';

if ($search !== '') {
    $conditions[] = '(p.nama_lengkap LIKE ? OR p.nim LIKE ? OR p.email LIKE ? OR p.universitas LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= 'ssss';
}
if ($fKategori !== '') {
    $conditions[] = 'p.kategori_idc = ?';
    $params[]  = $fKategori;
    $types    .= 's';
}
if ($fNegara !== '') {
    $conditions[] = 'p.negara = ?';
    $params[]  = $fNegara;
    $types    .= 's';
}
if ($fStatus !== '') {
    $conditions[] = 'p.status = ?';
    $params[]  = $fStatus;
    $types    .= 's';
}
if ($fDateFrom !== '') {
    $conditions[] = 'DATE(p.created_at) >= ?';
    $params[]  = $fDateFrom;
    $types    .= 's';
}
if ($fDateTo !== '') {
    $conditions[] = 'DATE(p.created_at) <= ?';
    $params[]  = $fDateTo;
    $types    .= 's';
}

$where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

// Total count
$countSql = "SELECT COUNT(*) AS n FROM suic_pendaftaran p $where";
$stmt     = $conn->prepare($countSql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['n'];
$stmt->close();

$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// Data query — sort col sudah divalidasi oleh safeSort()
$sql  = "SELECT p.id, p.nama_lengkap, p.nim, p.universitas, p.negara,
                p.email, p.nomor_hp, p.kategori_idc, p.status, p.created_at
         FROM suic_pendaftaran p $where
         ORDER BY p.{$sortCol} {$sortDir}
         LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$allParams = array_merge($params, [$perPage, $offset]);
$allTypes  = $types . 'ii';
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Daftar negara unik untuk filter dropdown
$negaraList = [];
$r = $conn->query("SELECT DISTINCT negara FROM suic_pendaftaran ORDER BY negara");
while ($row = $r->fetch_assoc()) {
    $negaraList[] = $row['negara'];
}
$conn->close();

// ── Helper: URL dengan parameter ─────────────────────────────
$queryBase = http_build_query(array_filter([
    'page' => 'registrations',
    'q'    => $search, 'kategori' => $fKategori, 'negara' => $fNegara,
    'status' => $fStatus, 'date_from' => $fDateFrom, 'date_to' => $fDateTo,
    'sort' => $sortCol, 'dir' => $sortDir, 'pp' => $perPage,
]));

function pageUrl(int $p, string $base): string {
    return 'index.php?' . $base . '&p=' . $p;
}
function sortUrl(string $col, string $curCol, string $curDir, string $base): string {
    $newDir = ($col === $curCol && $curDir === 'ASC') ? 'DESC' : 'ASC';
    $p = preg_replace('/(&?sort=[^&]*)/', '', $base);
    $p = preg_replace('/(&?dir=[^&]*)/',  '', $p);
    return 'index.php?' . ltrim($p, '&') . '&sort=' . urlencode($col) . '&dir=' . $newDir;
}
function sortIcon(string $col, string $curCol, string $curDir): string {
    if ($col !== $curCol) return '<svg class="w-3 h-3 opacity-30 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 8V4m0 12l4-4m-4 4l-4-4"/></svg>';
    return $curDir === 'ASC'
        ? '<svg class="w-3 h-3 text-orange-400 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>'
        : '<svg class="w-3 h-3 text-orange-400 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
}

include __DIR__ . '/../includes/layout_head.php';
?>

<!-- Filter & Search Bar -->
<div class="card p-4 mb-5">
    <form method="GET" action="index.php" class="space-y-3" id="filter-form">
        <input type="hidden" name="page" value="registrations">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search -->
            <div class="sm:col-span-2 lg:col-span-1">
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama, NIM, email…"
                       class="form-input" maxlength="100">
            </div>
            <!-- Kategori -->
            <div>
                <select name="kategori" class="form-input">
                    <option value="">Semua Kategori</option>
                    <?php foreach (IDC_CATEGORIES as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $fKategori === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Status -->
            <div>
                <select name="status" class="form-input">
                    <option value="">Semua Status</option>
                    <?php foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= $fStatus === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Negara -->
            <div>
                <select name="negara" class="form-input">
                    <option value="">Semua Negara</option>
                    <?php foreach ($negaraList as $n): ?>
                    <option value="<?= e($n) ?>" <?= $fNegara === $n ? 'selected' : '' ?>><?= e($n) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap gap-3 items-end">
            <!-- Date range -->
            <div class="flex items-center gap-2">
                <input type="date" name="date_from" value="<?= e($fDateFrom) ?>" class="form-input w-36" placeholder="Dari">
                <span class="text-gray-600 text-xs">s/d</span>
                <input type="date" name="date_to"   value="<?= e($fDateTo) ?>"   class="form-input w-36" placeholder="Sampai">
            </div>
            <div class="flex gap-2 ml-auto">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Filter
                </button>
                <a href="index.php?page=registrations" class="btn-secondary">Reset</a>
                <a href="<?= e(adminBaseUrl() . 'export.php?' . $queryBase) ?>"
                   class="btn-secondary" title="Export CSV">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card">
    <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-700">
        <p class="text-sm text-gray-400">
            Menampilkan <span class="text-white font-bold"><?= count($rows) ?></span>
            dari <span class="text-white font-bold"><?= $total ?></span> data
        </p>
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-500">Per halaman:</label>
            <select onchange="changePP(this.value)" class="form-input py-1 text-xs w-16">
                <?php foreach ([20, 50, 100] as $pp): ?>
                <option value="<?= $pp ?>" <?= $perPage === $pp ? 'selected' : '' ?>><?= $pp ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (empty($rows)): ?>
    <div class="text-center py-16 text-gray-600">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p class="text-sm">Tidak ada data yang cocok dengan filter.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-700 text-xs text-gray-500 uppercase tracking-wider">
                    <?php
                    $ths = [
                        ['col' => 'id',           'label' => '#'],
                        ['col' => 'nama_lengkap', 'label' => 'Nama / NIM'],
                        ['col' => 'universitas',  'label' => 'Universitas'],
                        ['col' => 'kategori_idc', 'label' => 'Kategori IDC'],
                        ['col' => 'negara',       'label' => 'Negara'],
                        ['col' => 'status',       'label' => 'Status'],
                        ['col' => 'created_at',   'label' => 'Tanggal'],
                        ['col' => '',             'label' => ''],
                    ];
                    foreach ($ths as $th):
                        $isSortable = $th['col'] !== '';
                    ?>
                    <th class="text-left px-4 py-3 font-semibold <?= $isSortable ? 'cursor-pointer hover:text-gray-300' : '' ?>"
                        <?= $isSortable ? 'onclick="window.location=\'' . e(sortUrl($th['col'], $sortCol, $sortDir, $queryBase)) . '\'"' : '' ?>>
                        <?= e($th['label']) ?>
                        <?= $isSortable ? sortIcon($th['col'], $sortCol, $sortDir) : '' ?>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr class="table-row-hover border-b border-gray-800/50">
                    <td class="px-4 py-3 text-gray-500 text-xs"><?= (int)$row['id'] ?></td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-white text-sm leading-snug"><?= e($row['nama_lengkap']) ?></p>
                        <p class="text-xs text-gray-500 mt-0.5"><?= e($row['nim']) ?></p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-gray-300 max-w-[160px] truncate" title="<?= e($row['universitas']) ?>"><?= e($row['universitas']) ?></p>
                        <p class="text-xs text-gray-600 mt-0.5"><?= e($row['email']) ?></p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs text-gray-400 leading-snug"><?= e($row['kategori_idc'] ?? '-') ?></span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400"><?= e($row['negara']) ?></td>
                    <td class="px-4 py-3"><?= statusBadge($row['status']) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                        <?= e(date('d M Y', strtotime($row['created_at']))) ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="<?= e(adminBaseUrl() . 'index.php?page=registration_detail&id=' . (int)$row['id']) ?>"
                           class="btn-secondary py-1 px-3 text-xs">Detail</a>
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
            <?php
            $pRange = range(max(1, $page - 2), min($totalPages, $page + 2));
            if ($page > 1): ?>
            <a href="<?= e(pageUrl(1, $queryBase)) ?>" class="btn-secondary py-1 px-2.5 text-xs">«</a>
            <a href="<?= e(pageUrl($page - 1, $queryBase)) ?>" class="btn-secondary py-1 px-2.5 text-xs">‹</a>
            <?php endif;
            foreach ($pRange as $pn): ?>
            <a href="<?= e(pageUrl($pn, $queryBase)) ?>"
               class="<?= $pn === $page ? 'btn-primary' : 'btn-secondary' ?> py-1 px-2.5 text-xs"><?= $pn ?></a>
            <?php endforeach;
            if ($page < $totalPages): ?>
            <a href="<?= e(pageUrl($page + 1, $queryBase)) ?>" class="btn-secondary py-1 px-2.5 text-xs">›</a>
            <a href="<?= e(pageUrl($totalPages, $queryBase)) ?>" class="btn-secondary py-1 px-2.5 text-xs">»</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function changePP(val) {
    var url = new URL(window.location.href);
    url.searchParams.set('pp', val);
    url.searchParams.set('p', '1');
    window.location = url.toString();
}
</script>

<?php include __DIR__ . '/../includes/layout_foot.php'; ?>
