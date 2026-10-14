<?php
/**
 * Protected Document Server — Admin Panel SUIC 2026
 * Melayani file upload hanya kepada admin yang terautentikasi.
 * URL: admin/doc.php?id={reg_id}&type={surat|bukti}&download=1
 *
 * Keamanan:
 * - Otentikasi wajib
 * - Path divalidasi dari record database (bukan dari URL)
 * - Cegah path traversal dengan realpath()
 * - File executable tidak diizinkan
 * - Tidak mengekspos path filesystem
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';

requireAuth();

// ── Validasi parameter ────────────────────────────────────────
$id       = filter_var($_GET['id']   ?? 0, FILTER_VALIDATE_INT);
$type     = trim($_GET['type']       ?? '');
$download = isset($_GET['download']) && $_GET['download'] === '1';

if (!$id || $id < 1) {
    http_response_code(400);
    die('Parameter tidak valid.');
}
if (!in_array($type, ['surat', 'bukti', 'proposal'], true)) {
    http_response_code(400);
    die('Tipe dokumen tidak dikenal.');
}

// ── Ambil nama file dari database ─────────────────────────────
// Tidak menggunakan path dari request; path diambil dari record DB
$conn = getDBConnection();
$stmt = $conn->prepare(
    "SELECT file_surat, file_bukti_follow, file_proposal FROM suic_pendaftaran WHERE id = ? LIMIT 1"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$reg = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$reg) {
    http_response_code(404);
    die('Registrasi tidak ditemukan.');
}

$filename = match($type) {
    'surat'    => $reg['file_surat'] ?? null,
    'bukti'    => $reg['file_bukti_follow'] ?? null,
    'proposal' => $reg['file_proposal'] ?? null,
    default    => null,
};

if (!$filename) {
    http_response_code(404);
    die('File tidak tersedia untuk dokumen ini.');
}

// ── Validasi path — cegah path traversal ─────────────────────
// UPLOAD_DIR didefinisikan di auth.php dengan realpath()
$targetPath = realpath(UPLOAD_DIR . $filename);

if ($targetPath === false) {
    http_response_code(404);
    die('File tidak ditemukan.');
}

// Pastikan file berada di dalam direktori upload yang diizinkan
$uploadDirReal = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR);
if (strpos($targetPath, $uploadDirReal) !== 0) {
    // Path traversal terdeteksi
    http_response_code(403);
    die('Akses ditolak.');
}

if (!is_file($targetPath) || !is_readable($targetPath)) {
    http_response_code(404);
    die('File tidak dapat dibaca.');
}

// ── Validasi MIME type (double-check dari isi file) ───────────
// Ekstensi yang diizinkan — file executable tidak boleh diservir
$allowedMimes = [
    'application/pdf' => 'pdf',
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
];

$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $targetPath);
finfo_close($finfo);

if (!array_key_exists($mimeType, $allowedMimes)) {
    http_response_code(403);
    die('Tipe file tidak diizinkan untuk diservir.');
}

// ── Kirim file ────────────────────────────────────────────────
$safeBasename = 'suic_' . $id . '_' . $type . '.' . $allowedMimes[$mimeType];
$filesize     = filesize($targetPath);

// Bersihkan buffer output
if (ob_get_level()) ob_end_clean();

header('Content-Type: ' . $mimeType);
if ($download) {
    header('Content-Disposition: attachment; filename="' . $safeBasename . '"');
} else {
    // Inline view — cegah sniffing konten
    header('Content-Disposition: inline; filename="' . $safeBasename . '"');
    header('X-Content-Type-Options: nosniff');
}
header('Content-Length: ' . $filesize);
header('Cache-Control: private, no-store');
header('Pragma: no-cache');

readfile($targetPath);
exit;
