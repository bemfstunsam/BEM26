<?php
/**
 * Dedicated Router & Admin Panel: Registrasi SUIC 2026 (IDC)
 * URL: /bem/suic/admin/index.php
 * Khusus untuk pengelolaan pendaftaran International Design Competition (IDC) SUIC 2026.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../admin/includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

requireAuth();

$conn = getDBConnection();

// ── Handle Action: Update Status (POST) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_GET['action'] ?? '') === 'status_update' || isset($_POST['action_status_update']))) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrf($csrf)) {
        setFlash('error', 'Token keamanan CSRF tidak valid. Silakan coba lagi.');
        header('Location: index.php' . (isset($_POST['id']) ? '?page=detail&id=' . (int)$_POST['id'] : ''));
        exit;
    }

    $id        = filter_var($_POST['id']     ?? 0,  FILTER_VALIDATE_INT);
    $newStatus = trim($_POST['status']       ?? '');
    $catatan   = trim(mb_substr($_POST['catatan'] ?? '', 0, 500));

    if (!$id || $id < 1) {
        setFlash('error', 'ID pendaftaran tidak valid.');
        header('Location: index.php');
        exit;
    }

    if (!in_array($newStatus, REG_STATUSES, true)) {
        setFlash('error', 'Status yang dipilih tidak valid.');
        header('Location: index.php?page=detail&id=' . $id);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, status, nama_lengkap, email FROM suic_pendaftaran WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $reg = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$reg) {
        setFlash('error', 'Data pendaftaran tidak ditemukan.');
        header('Location: index.php');
        exit;
    }

    $oldStatus = $reg['status'];

    if ($oldStatus !== $newStatus) {
        $stmt = $conn->prepare("UPDATE suic_pendaftaran SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $newStatus, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $desc = "Update status ID #{$id} ({$reg['nama_lengkap']}): '{$oldStatus}' → '{$newStatus}'";
            if ($catatan !== '') {
                $desc .= " | Catatan: {$catatan}";
            }
            auditLog($conn, 'status_update', 'suic_pendaftaran', $id, $desc);
            setFlash('success', "Status pendaftaran #{$id} ({$reg['nama_lengkap']}) berhasil diubah menjadi: " . ucfirst($newStatus));
        } else {
            setFlash('error', 'Gagal memperbarui status di database.');
        }
    } else {
        setFlash('info', 'Status tidak berubah.');
    }

    $redirectPage = ($_POST['redirect_to'] ?? '') === 'detail' ? ('?page=detail&id=' . $id) : '';
    header('Location: index.php' . $redirectPage);
    exit;
}

// ── Detail View Mode ──────────────────────────────────────────
$subPage = trim($_GET['page'] ?? '');
if ($subPage === 'detail') {
    $detailId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
    if (!$detailId || $detailId < 1) {
        setFlash('error', 'ID pendaftaran tidak valid.');
        header('Location: index.php');
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM suic_pendaftaran WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $detailId);
    $stmt->execute();
    $reg = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$reg) {
        setFlash('error', 'Data pendaftaran tidak ditemukan.');
        header('Location: index.php');
        exit;
    }

    // Riwayat audit log pendaftaran ini
    $stmt = $conn->prepare(
        "SELECT a.aksi, a.deskripsi, a.dibuat_pada, a.admin_nama
         FROM suic_audit_log a
         WHERE a.entitas = 'suic_pendaftaran' AND a.entitas_id = ?
         ORDER BY a.dibuat_pada DESC"
    );
    $stmt->bind_param('i', $detailId);
    $stmt->execute();
    $auditLogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $flashSuccess = getFlash('success');
    $flashError   = getFlash('error');
    $flashInfo    = getFlash('info');
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detail Pendaftaran #<?= (int)$reg['id'] ?> — SUIC 2026 Admin</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; }
            .badge-pending  { background: rgba(234, 88, 12, 0.15); color: #fb923c; border: 1px solid rgba(234, 88, 12, 0.3); }
            .badge-approved { background: rgba(22, 163, 74, 0.15); color: #4ade80; border: 1px solid rgba(22, 163, 74, 0.3); }
            .badge-rejected { background: rgba(220, 38, 38, 0.15); color: #f87171; border: 1px solid rgba(220, 38, 38, 0.3); }
        </style>
    </head>
    <body class="bg-gray-950 text-gray-100 min-h-screen">
        <!-- Top Nav -->
        <header class="bg-gray-900 border-b border-gray-800 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="index.php" class="flex items-center gap-2 text-white hover:text-orange-400 transition-colors">
                        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center font-bold text-white shadow-md shadow-orange-500/20">S</div>
                        <div>
                            <span class="font-extrabold text-white text-base">SUIC 2026</span>
                            <span class="text-orange-400 text-xs block -mt-1 font-semibold">Registrasi IDC Portal</span>
                        </div>
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <a href="index.php" class="px-3.5 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold rounded-lg border border-gray-700 transition-colors flex items-center gap-1.5">
                        ← Kembali ke Daftar
                    </a>
                    <a href="/bem/admin/index.php" class="px-3.5 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold rounded-lg border border-gray-700 transition-colors">
                        Dashboard Utama BEM
                    </a>
                </div>
            </div>
        </header>

        <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-6">
                <a href="/bem/admin/index.php" class="hover:text-gray-300">Admin Utama</a>
                <span>/</span>
                <a href="index.php" class="hover:text-gray-300">Registrasi SUIC</a>
                <span>/</span>
                <span class="text-orange-400 font-medium">Detail Peserta #<?= (int)$reg['id'] ?></span>
            </div>

            <?php if ($flashSuccess): ?>
            <div class="bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-3 mb-6 text-green-400 text-sm flex items-center gap-2">
                <span>✓</span> <?= e($flashSuccess) ?>
            </div>
            <?php endif; ?>
            <?php if ($flashError): ?>
            <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-400 text-sm flex items-center gap-2">
                <span>⚠️</span> <?= e($flashError) ?>
            </div>
            <?php endif; ?>

            <!-- Header Info Card -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-white"><?= e($reg['nama_lengkap']) ?></h1>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?= 'badge-' . $reg['status'] ?>">
                            <?= match($reg['status']) { 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Pending' } ?>
                        </span>
                    </div>
                    <p class="text-gray-400 text-sm mt-1">NIM: <span class="text-white font-mono"><?= e($reg['nim']) ?></span> • <?= e($reg['universitas']) ?> • <?= e($reg['negara']) ?></p>
                </div>
                <div class="text-right text-xs text-gray-500">
                    <p>Terdaftar pada:</p>
                    <p class="text-gray-300 font-medium mt-0.5"><?= date('d F Y, H:i', strtotime($reg['created_at'])) ?> WIB</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Info Kolom Kiri & Tengah -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Detail Informasi -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                            <span class="text-orange-400">📋</span> Data Pendaftaran
                        </h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="bg-gray-950/60 p-3.5 rounded-xl border border-gray-800">
                                <dt class="text-xs text-gray-500 font-medium">Kategori IDC</dt>
                                <dd class="text-white font-semibold mt-1 text-orange-400"><?= e($reg['kategori_idc'] ?? '-') ?></dd>
                            </div>
                            <div class="bg-gray-950/60 p-3.5 rounded-xl border border-gray-800">
                                <dt class="text-xs text-gray-500 font-medium">Asal Negara</dt>
                                <dd class="text-white font-semibold mt-1"><?= e($reg['negara']) ?></dd>
                            </div>
                            <div class="bg-gray-950/60 p-3.5 rounded-xl border border-gray-800">
                                <dt class="text-xs text-gray-500 font-medium">Email Peserta</dt>
                                <dd class="text-white font-semibold mt-1">
                                    <a href="mailto:<?= e($reg['email']) ?>" class="hover:underline text-blue-400"><?= e($reg['email']) ?></a>
                                </dd>
                            </div>
                            <div class="bg-gray-950/60 p-3.5 rounded-xl border border-gray-800">
                                <dt class="text-xs text-gray-500 font-medium">Nomor WhatsApp / HP</dt>
                                <dd class="text-white font-semibold mt-1">
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $reg['nomor_hp']) ?>" target="_blank" class="hover:underline text-green-400"><?= e($reg['nomor_hp']) ?></a>
                                </dd>
                            </div>
                            <div class="sm:col-span-2 bg-gray-950/60 p-3.5 rounded-xl border border-gray-800">
                                <dt class="text-xs text-gray-500 font-medium">Universitas / Institusi</dt>
                                <dd class="text-white font-semibold mt-1"><?= e($reg['universitas']) ?></dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Detail Inovasi & Karya Kompetisi -->
                    <?php if (!empty($reg['judul_karya']) || !empty($reg['deskripsi_karya']) || !empty($reg['data_spesifik'])):
                        $specData = !empty($reg['data_spesifik']) ? json_decode($reg['data_spesifik'], true) : [];
                    ?>
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                            <span class="text-orange-400">💡</span> Karya & Inovasi Kompetisi
                        </h2>
                        <div class="space-y-4 text-sm">
                            <?php if (!empty($reg['judul_karya'])): ?>
                            <div class="bg-gray-950/60 p-4 rounded-xl border border-gray-800">
                                <dt class="text-xs text-gray-500 font-medium">Judul Karya / Inovasi</dt>
                                <dd class="text-white font-bold text-base mt-1 text-orange-400"><?= e($reg['judul_karya']) ?></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($specData)): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <?php foreach ($specData as $sk => $sv):
                                    $label = ucwords(str_replace('_', ' ', $sk));
                                ?>
                                <div class="bg-gray-950/60 p-3 rounded-xl border border-gray-800">
                                    <dt class="text-[11px] text-gray-500 font-medium"><?= e($label) ?></dt>
                                    <dd class="text-gray-200 font-semibold text-xs mt-0.5"><?= e($sv) ?></dd>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($reg['deskripsi_karya'])): ?>
                            <div class="bg-gray-950/60 p-4 rounded-xl border border-gray-800">
                                <dt class="text-xs text-gray-500 font-medium mb-1">Deskripsi & Konsep Inovasi</dt>
                                <dd class="text-gray-300 text-xs leading-relaxed whitespace-pre-line"><?= e($reg['deskripsi_karya']) ?></dd>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Dokumen Terunggah -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                            <span class="text-orange-400">📁</span> Dokumen Terunggah
                        </h2>
                        <div class="space-y-3">
                            <!-- Proposal / Lembar Konsep Teknis -->
                            <div class="bg-gray-950/60 p-4 rounded-xl border border-gray-800 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-white">Proposal / Dokumen Desain Teknis</p>
                                    <p class="text-xs text-gray-500 font-mono mt-0.5"><?= e($reg['file_proposal'] ?? '') ?: 'Tidak ada file diunggah' ?></p>
                                </div>
                                <?php if (!empty($reg['file_proposal'])): ?>
                                <div class="flex gap-2">
                                    <a href="/bem/admin/doc.php?id=<?= (int)$reg['id'] ?>&type=proposal" target="_blank"
                                       class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold rounded-lg border border-gray-700 transition-colors">
                                        Lihat File
                                    </a>
                                    <a href="/bem/admin/doc.php?id=<?= (int)$reg['id'] ?>&type=proposal&download=1"
                                       class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                        Unduh
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Surat Rekomendasi / KTM -->
                            <div class="bg-gray-950/60 p-4 rounded-xl border border-gray-800 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-white">Surat Rekomendasi / Keterangan Aktif / KTM</p>
                                    <p class="text-xs text-gray-500 font-mono mt-0.5"><?= e($reg['file_surat'] ?: 'Tidak ada file') ?></p>
                                </div>
                                <?php if (!empty($reg['file_surat'])): ?>
                                <div class="flex gap-2">
                                    <a href="/bem/admin/doc.php?id=<?= (int)$reg['id'] ?>&type=surat" target="_blank"
                                       class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold rounded-lg border border-gray-700 transition-colors">
                                        Lihat File
                                    </a>
                                    <a href="/bem/admin/doc.php?id=<?= (int)$reg['id'] ?>&type=surat&download=1"
                                       class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                        Unduh
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Bukti Follow -->
                            <div class="bg-gray-950/60 p-4 rounded-xl border border-gray-800 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-white">Bukti Follow Sosial Media SUIC</p>
                                    <p class="text-xs text-gray-500 font-mono mt-0.5"><?= e($reg['file_bukti_follow'] ?: 'Tidak ada file') ?></p>
                                </div>
                                <?php if (!empty($reg['file_bukti_follow'])): ?>
                                <div class="flex gap-2">
                                    <a href="/bem/admin/doc.php?id=<?= (int)$reg['id'] ?>&type=bukti" target="_blank"
                                       class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold rounded-lg border border-gray-700 transition-colors">
                                        Lihat File
                                    </a>
                                    <a href="/bem/admin/doc.php?id=<?= (int)$reg['id'] ?>&type=bukti&download=1"
                                       class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                        Unduh
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Kolom Kanan: Update Status & Audit Log -->
                <div class="space-y-6">
                    <!-- Form Update Status -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                            <span class="text-orange-400">⚖️</span> Verifikasi Status
                        </h2>
                        <form method="POST" action="index.php?action=status_update">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= (int)$reg['id'] ?>">
                            <input type="hidden" name="redirect_to" value="detail">

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase">Pilih Status Baru</label>
                                    <select name="status" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-orange-500">
                                        <option value="pending" <?= $reg['status'] === 'pending' ? 'selected' : '' ?>>⏳ Pending (Menunggu)</option>
                                        <option value="approved" <?= $reg['status'] === 'approved' ? 'selected' : '' ?>>✓ Approved (Disetujui)</option>
                                        <option value="rejected" <?= $reg['status'] === 'rejected' ? 'selected' : '' ?>>✕ Rejected (Ditolak)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase">Catatan Verifikasi (Opsional)</label>
                                    <textarea name="catatan" rows="3" placeholder="Alasan persetujuan/penolakan berkas..."
                                              class="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-orange-500"></textarea>
                                </div>
                                <button type="submit" class="w-full py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm rounded-xl transition-colors shadow-lg shadow-orange-500/20">
                                    Simpan Perubahan Status
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Riwayat Audit Log -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                            <span class="text-orange-400">🕒</span> Riwayat Aktivitas
                        </h2>
                        <?php if (empty($auditLogs)): ?>
                        <p class="text-xs text-gray-500 italic">Belum ada riwayat perubahan.</p>
                        <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($auditLogs as $log): ?>
                            <div class="text-xs border-l-2 border-orange-500/60 pl-3 py-1">
                                <p class="text-gray-300 leading-snug"><?= e($log['deskripsi']) ?></p>
                                <p class="text-gray-500 text-[10px] mt-0.5">
                                    Oleh: <span class="text-gray-400"><?= e($log['admin_nama'] ?: 'Sistem') ?></span> • <?= date('d/m/y H:i', strtotime($log['dibuat_pada'])) ?>
                                </p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// ── Default Mode: SUIC Registration List Management Panel ─────
// Parameter filter
$search    = trim(mb_substr($_GET['q']       ?? '', 0, 100));
$fKategori = trim($_GET['kategori']          ?? '');
$fNegara   = trim(mb_substr($_GET['negara']  ?? '', 0, 100));
$fStatus   = trim($_GET['status']            ?? '');
$fDateFrom = trim($_GET['date_from']         ?? '');
$fDateTo   = trim($_GET['date_to']           ?? '');
$page      = safePage($_GET['p']   ?? 1);
$perPage   = safePerPage($_GET['pp'] ?? 20, 20, 100);
[$sortCol, $sortDir] = safeSort($_GET['sort'] ?? 'created_at', $_GET['dir'] ?? 'DESC');

if (!in_array($fKategori, array_merge([''], IDC_CATEGORIES), true)) $fKategori = '';
if (!in_array($fStatus, array_merge([''], REG_STATUSES), true))     $fStatus   = '';

$validDate = function(string $d): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) return false;
    [$y, $m, $dd] = explode('-', $d);
    return checkdate((int)$m, (int)$dd, (int)$y);
};
if (!$validDate($fDateFrom)) $fDateFrom = '';
if (!$validDate($fDateTo))   $fDateTo   = '';
if ($fDateFrom && $fDateTo && $fDateFrom > $fDateTo) {
    [$fDateFrom, $fDateTo] = [$fDateTo, $fDateFrom];
}

// ── KPI Statistik SUIC ────────────────────────────────────────
$kpiTotal = (int)$conn->query("SELECT COUNT(*) AS c FROM suic_pendaftaran")->fetch_assoc()['c'];
$kpiRes   = $conn->query("SELECT status, COUNT(*) AS c FROM suic_pendaftaran GROUP BY status");
$kpiStatus = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
while ($kr = $kpiRes->fetch_assoc()) {
    $kpiStatus[$kr['status']] = (int)$kr['c'];
}

// ── Query Builder untuk Tabel ─────────────────────────────────
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

// Count total terfilter
$countSql  = "SELECT COUNT(*) AS total FROM suic_pendaftaran p $where";
$countStmt = $conn->prepare($countSql);
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalFiltered = (int)$countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalFiltered / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// Fetch Rows
$dataSql = "SELECT p.* FROM suic_pendaftaran p $where ORDER BY p.{$sortCol} {$sortDir} LIMIT ?, ?";
$dataStmt = $conn->prepare($dataSql);
$bindTypes = $types . 'ii';
$bindParams = array_merge($params, [$offset, $perPage]);
$dataStmt->bind_param($bindTypes, ...$bindParams);
$dataStmt->execute();
$rows = $dataStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$dataStmt->close();

// Daftar negara unik untuk filter
$negaraList = [];
$nRes = $conn->query("SELECT DISTINCT negara FROM suic_pendaftaran WHERE negara IS NOT NULL AND negara != '' ORDER BY negara ASC");
while ($nr = $nRes->fetch_assoc()) $negaraList[] = $nr['negara'];

// Query string base untuk pagination/sort/export
$exportParams = array_filter([
    'q'         => $search,
    'kategori'  => $fKategori,
    'negara'    => $fNegara,
    'status'    => $fStatus,
    'date_from' => $fDateFrom,
    'date_to'   => $fDateTo,
]);
$exportUrl = 'export.php?' . http_build_query($exportParams);

$flashSuccess = getFlash('success');
$flashError   = getFlash('error');
$flashInfo    = getFlash('info');

function paginationUrl(int $p): string {
    $pms = $_GET;
    $pms['p'] = $p;
    return 'index.php?' . http_build_query($pms);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi SUIC 2026 — Admin Panel IDC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .badge-pending  { background: rgba(234, 88, 12, 0.15); color: #fb923c; border: 1px solid rgba(234, 88, 12, 0.3); }
        .badge-approved { background: rgba(22, 163, 74, 0.15); color: #4ade80; border: 1px solid rgba(22, 163, 74, 0.3); }
        .badge-rejected { background: rgba(220, 38, 38, 0.15); color: #f87171; border: 1px solid rgba(220, 38, 38, 0.3); }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">

    <!-- Top Navigation Header -->
    <header class="bg-gray-900 border-b border-gray-800 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center font-bold text-white shadow-lg shadow-orange-500/20">
                    S
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-white leading-none">SUIC 2026 — Admin Registrasi</h1>
                    <p class="text-xs text-orange-400 font-semibold mt-0.5">International Design Competition (IDC)</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= e($exportUrl) ?>"
                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg transition-colors shadow-md shadow-emerald-600/20 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export ke Excel
                </a>
                <a href="/bem/admin/index.php"
                   class="px-3.5 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold rounded-lg border border-gray-700 transition-colors">
                    Dashboard BEM
                </a>
                <a href="/bem/admin/logout.php" onclick="return confirm('Keluar dari sesi admin?')"
                   class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-semibold rounded-lg border border-red-500/30 transition-colors">
                    Logout
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Flash Messages -->
        <?php if ($flashSuccess): ?>
        <div class="bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-3 text-green-400 text-sm flex items-center gap-2">
            <span>✓</span> <?= e($flashSuccess) ?>
        </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-400 text-sm flex items-center gap-2">
            <span>⚠️</span> <?= e($flashError) ?>
        </div>
        <?php endif; ?>

        <!-- KPI Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pendaftar</p>
                <p class="text-3xl font-extrabold text-white mt-2"><?= number_format($kpiTotal) ?></p>
                <p class="text-[11px] text-gray-500 mt-1">Seluruh kategori IDC</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                <p class="text-xs font-semibold text-orange-400 uppercase tracking-wider">⏳ Menunggu (Pending)</p>
                <p class="text-3xl font-extrabold text-orange-400 mt-2"><?= number_format($kpiStatus['pending']) ?></p>
                <p class="text-[11px] text-gray-500 mt-1">Perlu verifikasi dokumen</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                <p class="text-xs font-semibold text-green-400 uppercase tracking-wider">✓ Disetujui (Approved)</p>
                <p class="text-3xl font-extrabold text-green-400 mt-2"><?= number_format($kpiStatus['approved']) ?></p>
                <p class="text-[11px] text-gray-500 mt-1">Memenuhi persyaratan</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                <p class="text-xs font-semibold text-red-400 uppercase tracking-wider">✕ Ditolak (Rejected)</p>
                <p class="text-3xl font-extrabold text-red-400 mt-2"><?= number_format($kpiStatus['rejected']) ?></p>
                <p class="text-[11px] text-gray-500 mt-1">Berkas tidak valid</p>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <form method="GET" action="index.php" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <!-- Search Input -->
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase mb-1">Cari Peserta</label>
                        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Nama, NIM, Email, Universitas..."
                               class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-gray-600 focus:outline-none focus:border-orange-500">
                    </div>

                    <!-- Filter Kategori IDC -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase mb-1">Kategori IDC</label>
                        <select name="kategori" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-orange-500">
                            <option value="">Semua Kategori</option>
                            <?php foreach (IDC_CATEGORIES as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= $fKategori === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase mb-1">Status Verifikasi</label>
                        <select name="status" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-orange-500">
                            <option value="">Semua Status</option>
                            <option value="pending" <?= $fStatus === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                            <option value="approved" <?= $fStatus === 'approved' ? 'selected' : '' ?>>✓ Approved</option>
                            <option value="rejected" <?= $fStatus === 'rejected' ? 'selected' : '' ?>>✕ Rejected</option>
                        </select>
                    </div>

                    <!-- Filter Negara -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase mb-1">Negara</label>
                        <select name="negara" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-orange-500">
                            <option value="">Semua Negara</option>
                            <?php foreach ($negaraList as $neg): ?>
                            <option value="<?= e($neg) ?>" <?= $fNegara === $neg ? 'selected' : '' ?>><?= e($neg) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Tanggal Dari -->
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase mb-1">Tanggal Mulai</label>
                        <input type="date" name="date_from" value="<?= e($fDateFrom) ?>"
                               class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-orange-500">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-gray-800/60">
                    <div class="text-xs text-gray-400">
                        Menampilkan <span class="text-white font-bold"><?= count($rows) ?></span> dari <span class="text-white font-bold"><?= $totalFiltered ?></span> data pendaftaran
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if ($search || $fKategori || $fNegara || $fStatus || $fDateFrom || $fDateTo): ?>
                        <a href="index.php" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold rounded-lg border border-gray-700 transition-colors">
                            Reset Filter
                        </a>
                        <?php endif; ?>
                        <button type="submit" class="px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-lg transition-colors shadow-md shadow-orange-500/20">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Registrasi -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-xl">
            <?php if (empty($rows)): ?>
            <div class="p-12 text-center">
                <p class="text-gray-500 text-sm">Tidak ada pendaftaran yang sesuai dengan kriteria filter.</p>
                <?php if ($search || $fKategori || $fStatus || $fNegara): ?>
                <a href="index.php" class="inline-block mt-3 px-3.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition-colors">
                    Hapus Filter
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-950 border-b border-gray-800 text-gray-400 font-bold uppercase tracking-wider">
                            <th class="px-4 py-3.5">ID</th>
                            <th class="px-4 py-3.5">Peserta</th>
                            <th class="px-4 py-3.5">Universitas</th>
                            <th class="px-4 py-3.5">Kategori IDC</th>
                            <th class="px-4 py-3.5">Negara</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Tanggal</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60">
                        <?php foreach ($rows as $row): ?>
                        <tr class="hover:bg-gray-800/40 transition-colors">
                            <td class="px-4 py-3 font-mono text-gray-500">#<?= (int)$row['id'] ?></td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-white text-sm"><?= e($row['nama_lengkap']) ?></p>
                                <p class="text-gray-500 text-[11px] font-mono mt-0.5"><?= e($row['nim']) ?> • <?= e($row['email']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                <?= e($row['universitas']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-md bg-gray-800 border border-gray-700 text-gray-300 text-[11px]">
                                    <?= e($row['kategori_idc'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400"><?= e($row['negara']) ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider <?= 'badge-' . $row['status'] ?>">
                                    <?= match($row['status']) { 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Pending' } ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                <?= date('d M Y', strtotime($row['created_at'])) ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="index.php?page=detail&id=<?= (int)$row['id'] ?>"
                                       class="px-2.5 py-1 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold rounded-lg border border-gray-700 transition-colors">
                                        Detail & Verifikasi
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if ($totalPages > 1): ?>
            <div class="px-4 py-3.5 bg-gray-950 border-t border-gray-800 flex items-center justify-between text-xs">
                <span class="text-gray-500">Halaman <?= $page ?> dari <?= $totalPages ?></span>
                <div class="flex items-center gap-1">
                    <?php if ($page > 1): ?>
                    <a href="<?= e(paginationUrl($page - 1)) ?>" class="px-2.5 py-1 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-md border border-gray-700">‹ Prev</a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="<?= e(paginationUrl($i)) ?>"
                       class="px-2.5 py-1 rounded-md border <?= $i === $page ? 'bg-orange-500 border-orange-500 text-white font-bold' : 'bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                    <a href="<?= e(paginationUrl($page + 1)) ?>" class="px-2.5 py-1 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-md border border-gray-700">Next ›</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>
