<?php
session_start();
// Handler submit pendaftaran SUIC 2026 — Mendukung 5 Kategori TOR IDC dengan formulir spesifik

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=suic');
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/suic_config.php';

// ── Daftar kategori IDC yang diizinkan ───────────────────────
const IDC_CATEGORIES = [
    'Agricultural Drone Design',
    'Plantation Product Processing Equipment',
    'Fishery Product Processing Equipment',
    'Scientific Tools Design',
    'Product Design',
];

// ── Daftar negara yang diizinkan ─────────────────────────────
const ALLOWED_COUNTRIES = [
    'Indonesia','Malaysia','Thailand','Singapore','Philippines',
    'Vietnam','Myanmar','Cambodia','Laos','Brunei','Timor-Leste',
    'Bangladesh','India','Pakistan','Nepal','Sri Lanka','China',
    'Japan','South Korea','Australia','United States',
    'United Kingdom','Germany','France','Netherlands','Other',
];

// ── Sanitasi input teks ──────────────────────────────────────
function clean($str) {
    return htmlspecialchars(strip_tags(trim((string)$str)));
}

$returnPage = clean($_POST['return_page'] ?? 'suic');
$compParam  = clean($_POST['comp_param']  ?? '');

$nama        = clean($_POST['nama_lengkap'] ?? '');
$nim         = clean($_POST['nim'] ?? '');
$universitas = clean($_POST['universitas'] ?? '');
$negara      = trim($_POST['negara'] ?? '');
$hp          = clean($_POST['nomor_hp'] ?? '');
$email       = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$kategoriRaw = trim($_POST['kategori_idc'] ?? '');

// Normalisasi alias kategori (misalnya dengan akhiran 'Design')
$categoryAliases = [
    'Plantation Product Processing Equipment Design' => 'Plantation Product Processing Equipment',
    'Fishery Product Processing Equipment Design'    => 'Fishery Product Processing Equipment',
];
$kategori = $categoryAliases[$kategoriRaw] ?? $kategoriRaw;

$judulKarya     = clean($_POST['judul_karya']     ?? '');
$deskripsiKarya = clean($_POST['deskripsi_karya'] ?? '');

// Ambil field-field spesifik kompetisi
$specificKeys = [
    // Drone
    'drone_type', 'drone_mission', 'drone_payload', 'drone_flight_time',
    // Plantation
    'plantation_commodity', 'plantation_stage', 'plantation_power', 'plantation_capacity',
    // Fishery
    'fishery_commodity', 'fishery_focus', 'fishery_material', 'fishery_capacity',
    // Science
    'science_field', 'science_parameters', 'science_hardware',
    // Product
    'product_category', 'product_materials', 'product_target',
];

$dataSpesifik = [];
foreach ($specificKeys as $key) {
    if (isset($_POST[$key]) && trim($_POST[$key]) !== '') {
        $dataSpesifik[$key] = clean($_POST[$key]);
    }
}
$dataSpesifikJson = !empty($dataSpesifik) ? json_encode($dataSpesifik, JSON_UNESCAPED_UNICODE) : null;

// ── Validasi Keamanan (CSRF & Token Submisi) ─────────────────
$errors = [];

if (isset($_POST['csrf_token']) && !suic_verify_csrf($_POST['csrf_token'])) {
    $errors[] = 'Sesi keamanan formulir tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.';
}

$submissionToken = $_POST['submission_token'] ?? null;
if ($submissionToken !== null && !suic_consume_submission_token($submissionToken)) {
    // Jika token sudah pernah dikonsumsi (misal user klik ganda atau refresh POST)
    // dan sudah ada data pendaftaran sebelumnya untuk email ini:
    if (!empty($_SESSION['suic_registration_success']) && 
        ($_SESSION['suic_registration_success']['email'] ?? '') === $email &&
        ($_SESSION['suic_registration_success']['kategori_idc'] ?? '') === $kategori) {
        header('Location: ' . buildRedirectUrl($returnPage, $compParam, 'success'));
        exit;
    }
    $errors[] = 'Permintaan pendaftaran ini telah dikirimkan sebelumnya. Mohon tidak mengirimkan data ganda.';
}

// ── Validasi server-side data formulir ───────────────────────
if (!$nama)        $errors[] = 'Nama lengkap wajib diisi.';
if (!$nim)         $errors[] = 'NIM wajib diisi.';
if (!$universitas) $errors[] = 'Universitas wajib diisi.';

if (!$negara || !in_array($negara, ALLOWED_COUNTRIES, true)) {
    $errors[] = 'Negara wajib dipilih dari daftar yang tersedia.';
}

if (!$hp || !preg_match('/^[\+\d\s\-]{7,20}$/', preg_replace('/\s+/', '', $hp))) {
    $errors[] = 'Nomor HP wajib diisi dengan format yang valid.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid.';
}

if (!$kategori || !in_array($kategori, IDC_CATEGORIES, true)) {
    $errors[] = 'Kategori kompetisi IDC wajib dipilih dari pilihan resmi.';
}

// ── Pencegahan Duplikasi Data di Database ────────────────────
if (empty($errors)) {
    $conn = getDBConnection();
    $dupCheck = $conn->prepare("SELECT id FROM suic_pendaftaran WHERE email = ? AND kategori_idc = ? LIMIT 1");
    if ($dupCheck) {
        $dupCheck->bind_param('ss', $email, $kategori);
        $dupCheck->execute();
        $dupRes = $dupCheck->get_result();
        if ($dupRes && $dupRes->fetch_assoc()) {
            $errors[] = 'Email (' . $email . ') sudah terdaftar untuk kategori ' . $kategori . '. Setiap peserta hanya dapat mendaftar 1 kali per kategori.';
        }
        $dupCheck->close();
    }
    $conn->close();
}

// ── Upload File ───────────────────────────────────────────────
$uploadDir  = __DIR__ . '/../uploads/suic/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$allowedPdf = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
$allowedImg = ['image/jpeg', 'image/jpg', 'image/png'];
$allowedDoc = ['application/pdf'];

function uploadFileCustom($fieldName, $uploadDir, $allowedMimes, &$errors, $prefix, $maxBytes = 5242880, $required = true) {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            $errors[] = 'File ' . $fieldName . ' wajib diupload.';
        }
        return null;
    }
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Gagal mengupload ' . $fieldName . ' (kode error: ' . $file['error'] . ').';
        return null;
    }
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimeType, $allowedMimes, true)) {
        $errors[] = 'Format file ' . $fieldName . ' tidak diizinkan (' . $mimeType . ').';
        return null;
    }
    if ($file['size'] > $maxBytes) {
        $mb = round($maxBytes / (1024 * 1024));
        $errors[] = 'Ukuran file ' . $fieldName . ' maksimal ' . $mb . 'MB.';
        return null;
    }
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        $errors[] = 'Gagal menyimpan file ' . $fieldName . '. Periksa folder uploads/suic/.';
        return null;
    }
    return $filename;
}

$fileSurat    = null;
$fileBukti    = null;
$fileProposal = null;

// Hanya lakukan upload jika validasi dasar lolos
if (empty($errors)) {
    $fileSurat    = uploadFileCustom('file_surat',        $uploadDir, $allowedPdf, $errors, 'surat', 5 * 1024 * 1024, true);
    $fileBukti    = uploadFileCustom('file_bukti_follow', $uploadDir, $allowedImg, $errors, 'bukti', 5 * 1024 * 1024, true);
    $fileProposal = uploadFileCustom('file_proposal',     $uploadDir, $allowedPdf, $errors, 'proposal', 10 * 1024 * 1024, false);
}

// ── Redirect helper ──────────────────────────────────────────
function buildRedirectUrl($page, $comp, $status) {
    $qs = 'page=' . urlencode($page);
    if ($comp !== '') {
        $qs .= '&comp=' . urlencode($comp);
    }
    $qs .= '&status=' . urlencode($status);
    return '../index.php?' . $qs;
}

// ── Jika ada error, kembalikan ke form ───────────────────────
if (!empty($errors)) {
    if ($fileSurat && file_exists($uploadDir . $fileSurat))       @unlink($uploadDir . $fileSurat);
    if ($fileBukti && file_exists($uploadDir . $fileBukti))       @unlink($uploadDir . $fileBukti);
    if ($fileProposal && file_exists($uploadDir . $fileProposal)) @unlink($uploadDir . $fileProposal);

    $_SESSION['suic_errors'] = $errors;
    $_SESSION['suic_old']    = $_POST;
    header('Location: ' . buildRedirectUrl($returnPage, $compParam, 'error'));
    exit;
}

// ── Simpan ke Database ────────────────────────────────────────
$conn = getDBConnection();
$stmt = $conn->prepare(
    "INSERT INTO suic_pendaftaran 
     (nama_lengkap, nim, universitas, negara, nomor_hp, email, kategori_idc, judul_karya, deskripsi_karya, data_spesifik, file_surat, file_bukti_follow, file_proposal)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    if ($fileSurat && file_exists($uploadDir . $fileSurat))       @unlink($uploadDir . $fileSurat);
    if ($fileBukti && file_exists($uploadDir . $fileBukti))       @unlink($uploadDir . $fileBukti);
    if ($fileProposal && file_exists($uploadDir . $fileProposal)) @unlink($uploadDir . $fileProposal);

    $_SESSION['suic_errors'] = ['Gagal menyiapkan penyimpanan data: ' . $conn->error];
    $conn->close();
    header('Location: ' . buildRedirectUrl($returnPage, $compParam, 'error'));
    exit;
}

$stmt->bind_param(
    'sssssssssssss',
    $nama, $nim, $universitas, $negara, $hp, $email, $kategori,
    $judulKarya, $deskripsiKarya, $dataSpesifikJson,
    $fileSurat, $fileBukti, $fileProposal
);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    // Simpan data pendaftaran sukses ke sesi untuk render kartu sukses & pesan WhatsApp
    // Catatan: Data tidak diekspos di parameter URL demi keamanan privasi peserta
    $_SESSION['suic_last_id']   = $newId;
    $_SESSION['suic_last_comp'] = $kategori;

    suic_set_registration_success_data([
        'nama_lengkap'    => $nama,
        'nim'             => $nim,
        'universitas'     => $universitas,
        'negara'          => $negara,
        'nomor_hp'        => $hp,
        'email'           => $email,
        'kategori_idc'    => $kategori,
        'judul_karya'     => $judulKarya,
        'deskripsi_karya' => $deskripsiKarya,
        'data_spesifik'   => $dataSpesifik,
        'submitted_at'    => date('Y-m-d H:i:s'),
    ]);

    header('Location: ' . buildRedirectUrl($returnPage, $compParam, 'success'));
    exit;
} else {
    if ($fileSurat && file_exists($uploadDir . $fileSurat))       @unlink($uploadDir . $fileSurat);
    if ($fileBukti && file_exists($uploadDir . $fileBukti))       @unlink($uploadDir . $fileBukti);
    if ($fileProposal && file_exists($uploadDir . $fileProposal)) @unlink($uploadDir . $fileProposal);

    $_SESSION['suic_errors'] = ['Gagal menyimpan pendaftaran ke database: ' . $conn->error];
    $stmt->close();
    $conn->close();
    header('Location: ' . buildRedirectUrl($returnPage, $compParam, 'error'));
    exit;
}
