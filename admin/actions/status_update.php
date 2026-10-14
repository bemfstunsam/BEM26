<?php
/**
 * Action: Update status registrasi — Admin Panel SUIC 2026
 * POST-only. Memerlukan autentikasi + CSRF.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

// Hanya POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . adminBaseUrl() . 'index.php?page=registrations');
    exit;
}

// Autentikasi
requireAuth();

// CSRF
if (!verifyCsrf()) {
    setFlash('error', 'Token keamanan tidak valid. Silakan muat ulang halaman.');
    header('Location: ' . adminBaseUrl() . 'index.php?page=registrations');
    exit;
}

// Validasi input
$regId  = filter_var($_POST['reg_id'] ?? 0, FILTER_VALIDATE_INT);
$status = trim($_POST['status'] ?? '');
$catatan = trim(mb_substr($_POST['catatan'] ?? '', 0, 500));

if (!$regId || $regId < 1) {
    setFlash('error', 'ID registrasi tidak valid.');
    header('Location: ' . adminBaseUrl() . 'index.php?page=registrations');
    exit;
}

// Validasi status dari whitelist — tidak menerima nilai bebas
if (!in_array($status, REG_STATUSES, true)) {
    setFlash('error', 'Status tidak diizinkan: ' . e($status));
    header('Location: ' . adminBaseUrl() . 'index.php?page=registration_detail&id=' . $regId);
    exit;
}

$conn = getDBConnection();

// Periksa registrasi benar-benar ada dan ambil status lama
$stmt = $conn->prepare("SELECT id, nama_lengkap, status FROM suic_pendaftaran WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $regId);
$stmt->execute();
$reg = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reg) {
    $conn->close();
    setFlash('error', 'Registrasi tidak ditemukan.');
    header('Location: ' . adminBaseUrl() . 'index.php?page=registrations');
    exit;
}

$statusLama = $reg['status'];

// Update status
$stmt = $conn->prepare("UPDATE suic_pendaftaran SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $regId);
$ok = $stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if (!$ok) {
    $conn->close();
    setFlash('error', 'Gagal memperbarui status. Silakan coba lagi.');
    header('Location: ' . adminBaseUrl() . 'index.php?page=registration_detail&id=' . $regId);
    exit;
}

// Audit log
$statusLabel = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
$desc = sprintf(
    'Status diubah dari "%s" ke "%s" untuk peserta "%s" (ID #%d)%s',
    $statusLabel[$statusLama] ?? $statusLama,
    $statusLabel[$status]     ?? $status,
    $reg['nama_lengkap'],
    $regId,
    $catatan ? '. Catatan: ' . $catatan : ''
);
auditLog($conn, 'update_status', 'registrasi', $regId, $desc);
$conn->close();

if ($affected > 0) {
    setFlash('success', 'Status berhasil diubah menjadi "' . ($statusLabel[$status] ?? $status) . '".');
} else {
    setFlash('info', 'Tidak ada perubahan (status sudah sama).');
}

header('Location: ' . adminBaseUrl() . 'index.php?page=registration_detail&id=' . $regId);
exit;
