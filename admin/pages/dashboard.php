<?php
/**
 * Dashboard — Admin Panel SUIC 2026
 */
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/db.php';

$pageTitle   = 'Dashboard';
$currentPage = 'dashboard';

$conn = getDBConnection();

// ── Statistik utama ───────────────────────────────────────────
$stats = [];

// Total pendaftaran
$r = $conn->query("SELECT COUNT(*) AS n FROM suic_pendaftaran");
$stats['total'] = (int)$r->fetch_assoc()['n'];

// Per status
$r = $conn->query("SELECT status, COUNT(*) AS n FROM suic_pendaftaran GROUP BY status");
$byStatus = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
while ($row = $r->fetch_assoc()) {
    $byStatus[$row['status']] = (int)$row['n'];
}

// Per kategori IDC
$r = $conn->query(
    "SELECT kategori_idc, COUNT(*) AS n FROM suic_pendaftaran
     WHERE kategori_idc IS NOT NULL GROUP BY kategori_idc ORDER BY n DESC"
);
$byKategori = [];
while ($row = $r->fetch_assoc()) {
    $byKategori[$row['kategori_idc']] = (int)$row['n'];
}

// Per negara (top 8)
$r = $conn->query(
    "SELECT negara, COUNT(*) AS n FROM suic_pendaftaran
     GROUP BY negara ORDER BY n DESC LIMIT 8"
);
$byNegara = [];
while ($row = $r->fetch_assoc()) {
    $byNegara[$row['negara']] = (int)$row['n'];
}

// Hari ini
$r = $conn->query("SELECT COUNT(*) AS n FROM suic_pendaftaran WHERE DATE(created_at) = CURDATE()");
$stats['today'] = (int)$r->fetch_assoc()['n'];

// 7 hari terakhir
$r = $conn->query("SELECT COUNT(*) AS n FROM suic_pendaftaran WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['week'] = (int)$r->fetch_assoc()['n'];

// 5 pendaftaran terbaru
$r = $conn->query(
    "SELECT id, nama_lengkap, universitas, negara, kategori_idc, status, created_at
     FROM suic_pendaftaran ORDER BY created_at DESC LIMIT 5"
);
$recents = [];
while ($row = $r->fetch_assoc()) {
    $recents[] = $row;
}

$conn->close();

include __DIR__ . '/../includes/layout_head.php';
?>

<!-- Stat Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <?php
    $cards = [
        ['label' => 'Total Pendaftar',   'value' => $stats['total'],        'color' => 'orange', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
        ['label' => 'Menunggu',          'value' => $byStatus['pending'],   'color' => 'yellow', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Disetujui',         'value' => $byStatus['approved'],  'color' => 'green',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Ditolak',           'value' => $byStatus['rejected'],  'color' => 'red',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ];
    $colorMap = [
        'orange' => ['bg' => 'bg-orange-500/15', 'text' => 'text-orange-400', 'num' => 'text-orange-400'],
        'yellow' => ['bg' => 'bg-yellow-500/15', 'text' => 'text-yellow-400', 'num' => 'text-yellow-300'],
        'green'  => ['bg' => 'bg-green-500/15',  'text' => 'text-green-400',  'num' => 'text-green-400'],
        'red'    => ['bg' => 'bg-red-500/15',     'text' => 'text-red-400',   'num' => 'text-red-400'],
    ];
    foreach ($cards as $card):
        $c = $colorMap[$card['color']];
    ?>
    <div class="stat-card">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1"><?= e($card['label']) ?></p>
                <p class="text-3xl font-extrabold <?= $c['num'] ?>"><?= $card['value'] ?></p>
            </div>
            <div class="w-10 h-10 <?= $c['bg'] ?> rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 <?= $c['text'] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?= $card['icon'] ?>
                </svg>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Secondary stats -->
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="stat-card flex items-center gap-4">
        <div class="w-10 h-10 bg-blue-500/15 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Hari Ini</p>
            <p class="text-2xl font-extrabold text-blue-400"><?= $stats['today'] ?></p>
        </div>
    </div>
    <div class="stat-card flex items-center gap-4">
        <div class="w-10 h-10 bg-purple-500/15 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">7 Hari Terakhir</p>
            <p class="text-2xl font-extrabold text-purple-400"><?= $stats['week'] ?></p>
        </div>
    </div>
</div>

<!-- Kategori IDC + Negara -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <!-- Per Kategori IDC -->
    <div class="card p-5">
        <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-2 h-2 bg-orange-500 rounded-full inline-block"></span>
            Pendaftaran per Kategori IDC
        </h3>
        <?php if (empty($byKategori)): ?>
        <p class="text-gray-600 text-sm text-center py-6">Belum ada data.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php
            $shortNames = [
                'Agricultural Drone Design'               => 'Agricultural Drone Design',
                'Plantation Product Processing Equipment' => 'Plantation Processing',
                'Fishery Product Processing Equipment'    => 'Fishery Processing',
                'Scientific Tools Design'                 => 'Scientific Tools',
                'Product Design'                          => 'Product Design',
            ];
            foreach (IDC_CATEGORIES as $cat):
                $n    = $byKategori[$cat] ?? 0;
                $pct  = $stats['total'] > 0 ? round($n / $stats['total'] * 100) : 0;
                $name = $shortNames[$cat] ?? $cat;
            ?>
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs text-gray-400 truncate max-w-[75%]" title="<?= e($cat) ?>"><?= e($name) ?></span>
                    <span class="text-xs font-bold text-white ml-2"><?= $n ?> <span class="text-gray-600">(<?= $pct ?>%)</span></span>
                </div>
                <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-orange-500 rounded-full" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Per Negara -->
    <div class="card p-5">
        <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-2 h-2 bg-blue-500 rounded-full inline-block"></span>
            Pendaftaran per Negara (Top 8)
        </h3>
        <?php if (empty($byNegara)): ?>
        <p class="text-gray-600 text-sm text-center py-6">Belum ada data.</p>
        <?php else: ?>
        <div class="space-y-2.5">
            <?php foreach ($byNegara as $negara => $n):
                $pct = $stats['total'] > 0 ? round($n / $stats['total'] * 100) : 0;
            ?>
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs text-gray-400 truncate"><?= e($negara) ?></span>
                    <span class="text-xs font-bold text-white ml-2"><?= $n ?></span>
                </div>
                <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pendaftaran Terbaru -->
<div class="card">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-700">
        <h3 class="text-sm font-bold text-white">5 Pendaftaran Terbaru</h3>
        <a href="<?= e(adminBaseUrl() . 'index.php?page=registrations') ?>" class="text-xs text-orange-400 hover:text-orange-300 font-medium">
            Lihat semua →
        </a>
    </div>
    <?php if (empty($recents)): ?>
    <div class="text-center py-10 text-gray-600 text-sm">Belum ada pendaftaran.</div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Kategori</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Tanggal</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recents as $row): ?>
                <tr class="table-row-hover border-b border-gray-800/50">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-white text-sm"><?= e($row['nama_lengkap']) ?></p>
                        <p class="text-xs text-gray-500"><?= e($row['universitas']) ?></p>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <span class="text-xs text-gray-400"><?= e($row['kategori_idc'] ?? '-') ?></span>
                    </td>
                    <td class="px-5 py-3.5"><?= statusBadge($row['status']) ?></td>
                    <td class="px-5 py-3.5 hidden sm:table-cell text-xs text-gray-500">
                        <?= e(date('d M Y', strtotime($row['created_at']))) ?>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="<?= e(adminBaseUrl() . 'index.php?page=registration_detail&id=' . (int)$row['id']) ?>"
                           class="text-xs text-orange-400 hover:text-orange-300 font-medium whitespace-nowrap">Detail →</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/layout_foot.php'; ?>
