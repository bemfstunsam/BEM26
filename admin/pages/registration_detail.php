<?php
/**
 * Detail Registrasi — Admin Panel SUIC 2026
 */
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/db.php';

$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    setFlash('error', 'ID registrasi tidak valid.');
    header('Location: index.php?page=registrations');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare(
    "SELECT id, nama_lengkap, nim, universitas, negara, nomor_hp, email,
            kategori_idc, status, file_surat, file_bukti_follow, created_at
     FROM suic_pendaftaran WHERE id = ? LIMIT 1"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$reg = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reg) {
    $conn->close();
    setFlash('error', 'Registrasi tidak ditemukan.');
    header('Location: index.php?page=registrations');
    exit;
}

// Riwayat audit terkait registrasi ini
$stmt = $conn->prepare(
    "SELECT admin_nama, aksi, deskripsi, ip_address, dibuat_pada
     FROM suic_audit_log WHERE entitas = 'registrasi' AND entitas_id = ?
     ORDER BY dibuat_pada DESC LIMIT 20"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$auditRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$pageTitle   = 'Detail Registrasi #' . $id;
$currentPage = 'registrations';

include __DIR__ . '/../includes/layout_head.php';
?>

<!-- Back + Actions -->
<div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
    <a href="index.php?page=registrations" class="btn-secondary text-xs">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <div class="flex items-center gap-2">
        <?= statusBadge($reg['status']) ?>
        <span class="text-xs text-gray-600">ID #<?= (int)$reg['id'] ?></span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- Data Peserta -->
    <div class="lg:col-span-2 space-y-5">
        <div class="card p-5">
            <h3 class="text-sm font-bold text-white mb-4 pb-3 border-b border-gray-700">Informasi Peserta</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                <?php
                $fields = [
                    'Nama Lengkap'        => $reg['nama_lengkap'],
                    'NIM / Student ID'    => $reg['nim'],
                    'Universitas'         => $reg['universitas'],
                    'Negara'              => $reg['negara'],
                    'Nomor HP / WA'       => $reg['nomor_hp'],
                    'Email'               => $reg['email'],
                    'Kategori IDC'        => $reg['kategori_idc'] ?? '—',
                    'Tanggal Daftar'      => date('d F Y, H:i', strtotime($reg['created_at'])) . ' WIB',
                ];
                foreach ($fields as $lbl => $val): ?>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1"><?= e($lbl) ?></dt>
                    <dd class="text-sm text-gray-200 font-medium break-all"><?= e($val) ?></dd>
                </div>
                <?php endforeach; ?>
            </dl>
        </div>

        <!-- Dokumen -->
        <div class="card p-5">
            <h3 class="text-sm font-bold text-white mb-4 pb-3 border-b border-gray-700">Dokumen Pendaftaran</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php
                $docs = [
                    ['label' => 'Surat Keterangan Aktif / KTM', 'type' => 'surat', 'file' => $reg['file_surat']],
                    ['label' => 'Bukti Follow Instagram SUIC',   'type' => 'bukti', 'file' => $reg['file_bukti_follow']],
                ];
                foreach ($docs as $doc):
                    $hasFile = !empty($doc['file']);
                    $docUrl  = $hasFile
                        ? e(adminBaseUrl() . 'doc.php?id=' . (int)$reg['id'] . '&type=' . urlencode($doc['type']))
                        : '#';
                ?>
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3"><?= e($doc['label']) ?></p>
                    <?php if ($hasFile): ?>
                    <div class="flex gap-2 flex-wrap">
                        <a href="<?= $docUrl ?>" target="_blank"
                           class="btn-secondary text-xs py-1.5 px-3">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Lihat
                        </a>
                        <a href="<?= $docUrl ?>&download=1"
                           class="btn-secondary text-xs py-1.5 px-3">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                        </a>
                    </div>
                    <p class="text-xs text-gray-600 mt-2 truncate" title="<?= e($doc['file']) ?>"><?= e(basename($doc['file'])) ?></p>
                    <?php else: ?>
                    <p class="text-xs text-red-400">⚠️ File tidak ditemukan.</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Panel Kanan: Status + Audit -->
    <div class="space-y-5">

        <!-- Update Status -->
        <div class="card p-5">
            <h3 class="text-sm font-bold text-white mb-4 pb-3 border-b border-gray-700">Update Status</h3>
            <p class="text-xs text-gray-500 mb-3">
                Status saat ini: <?= statusBadge($reg['status']) ?>
            </p>
            <form method="POST" action="<?= e(adminBaseUrl() . 'actions/status_update.php') ?>"
                  onsubmit="return confirm('Yakin ingin mengubah status pendaftaran ini?')">
                <?= csrfField() ?>
                <input type="hidden" name="reg_id" value="<?= (int)$reg['id'] ?>">
                <div class="mb-3">
                    <label class="form-label">Status Baru</label>
                    <select name="status" class="form-input" required>
                        <?php foreach (REG_STATUSES as $s): ?>
                        <option value="<?= e($s) ?>" <?= $reg['status'] === $s ? 'selected' : '' ?>>
                            <?= e(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$s] ?? ucfirst($s)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="catatan" class="form-input" rows="2"
                              placeholder="Alasan perubahan status…" maxlength="500"></textarea>
                </div>
                <button type="submit" class="btn-primary w-full justify-center text-sm">
                    Simpan Perubahan
                </button>
            </form>
        </div>

        <!-- Riwayat Audit -->
        <div class="card p-5">
            <h3 class="text-sm font-bold text-white mb-4 pb-3 border-b border-gray-700">Riwayat Aksi</h3>
            <?php if (empty($auditRows)): ?>
            <p class="text-xs text-gray-600 text-center py-4">Belum ada riwayat.</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($auditRows as $ar): ?>
                <div class="border-l-2 border-orange-500/40 pl-3">
                    <p class="text-xs text-gray-300 font-semibold"><?= e($ar['aksi']) ?></p>
                    <p class="text-xs text-gray-500 mt-0.5"><?= e($ar['deskripsi'] ?? '') ?></p>
                    <p class="text-xs text-gray-600 mt-1">
                        <?= e($ar['admin_nama']) ?> ·
                        <?= e(date('d M Y H:i', strtotime($ar['dibuat_pada']))) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/layout_foot.php'; ?>
