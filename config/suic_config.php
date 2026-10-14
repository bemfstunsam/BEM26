<?php
/**
 * Konfigurasi Resmi SUIC 2026 & International Design Competition (IDC)
 * BEM FST Universitas Samudra (UNSAM)
 *
 * File ini mengatur:
 * 1. Nomor WhatsApp resmi panitia SUIC 2026 (PLACEHOLDER RESMI).
 *    PERHATIAN: Jangan gunakan nomor pribadi. Ganti nilai SUIC_WA_COMMITTEE_NUMBER
 *    dengan nomor WhatsApp resmi panitia SUIC 2026 setelah nomor resmi ditetapkan.
 * 2. Helper pembuatan tautan dan pesan konfirmasi WhatsApp yang aman dan terenkode.
 * 3. Keamanan CSRF dan pencegahan duplikasi pendaftaran.
 */

// ── 1. KONFIGURASI NOMOR WHATSAPP PANITIA ────────────────────
// Nomor resmi panitia SUIC 2026:
// Format lokal: 081377178981
// Format internasional untuk link WhatsApp: 6281377178981
// URL WhatsApp: https://wa.me/6281377178981
if (!defined('SUIC_WA_COMMITTEE_NUMBER')) {
    $envNumber = getenv('SUIC_WA_COMMITTEE_NUMBER');
    define('SUIC_WA_COMMITTEE_NUMBER', ($envNumber !== false && trim($envNumber) !== '') ? trim($envNumber) : '6281377178981');
}

/**
 * Mengambil nomor WhatsApp panitia yang telah disanitasi (hanya angka)
 */
function suic_get_committee_wa_number(): string {
    $num = defined('SUIC_WA_COMMITTEE_NUMBER') ? SUIC_WA_COMMITTEE_NUMBER : '6281377178981';
    $clean = preg_replace('/[^0-9]/', '', (string)$num);
    return $clean !== '' ? $clean : '6281377178981';
}

/**
 * Menyusun pesan WhatsApp konfirmasi pendaftaran secara dinamis
 * Hanya menampilkan kolom yang memiliki nilai relevan.
 */
function suic_build_wa_message(array $data): string {
    $nama        = trim((string)($data['nama_lengkap'] ?? ''));
    $nim         = trim((string)($data['nim'] ?? ''));
    $universitas = trim((string)($data['universitas'] ?? ''));
    $negara      = trim((string)($data['negara'] ?? ''));
    $email       = trim((string)($data['email'] ?? ''));
    $kategori    = trim((string)($data['kategori_idc'] ?? ''));
    $judul       = trim((string)($data['judul_karya'] ?? ''));

    $lines = [];
    $lines[] = "Halo Panitia SUIC 2026 / IDC BEM FST Universitas Samudra,";
    $lines[] = "";
    $lines[] = "Saya telah berhasil melakukan pendaftaran online dengan rincian data sebagai berikut:";
    $lines[] = "";

    if ($nama !== '') {
        $lines[] = "• Nama Lengkap: " . $nama;
    }
    if ($nim !== '') {
        $lines[] = "• NIM / Student ID: " . $nim;
    }
    if ($universitas !== '') {
        $lines[] = "• Universitas / Institusi: " . $universitas;
    }
    if ($negara !== '') {
        $lines[] = "• Asal Negara: " . $negara;
    }
    if ($email !== '') {
        $lines[] = "• Email: " . $email;
    }

    $lines[] = "• Kompetisi: International Design Competition (IDC) — SUIC 2026";

    if ($kategori !== '') {
        $lines[] = "• Kategori: " . $kategori;
    }
    if ($judul !== '') {
        $lines[] = "• Judul Karya: " . $judul;
    }

    // Tampilkan parameter spesifik jika tersedia
    if (!empty($data['data_spesifik']) && is_array($data['data_spesifik'])) {
        $specLabels = [
            'drone_type'           => 'Tipe Drone',
            'drone_mission'        => 'Misi Drone',
            'plantation_commodity' => 'Komoditas Perkebunan',
            'fishery_commodity'    => 'Komoditas Perikanan',
            'science_field'        => 'Bidang Sains',
            'product_category'     => 'Kategori Produk',
        ];
        foreach ($specLabels as $specKey => $specLabel) {
            if (!empty($data['data_spesifik'][$specKey])) {
                $lines[] = "• " . $specLabel . ": " . trim($data['data_spesifik'][$specKey]);
                break; // Ambil 1 representasi spesifik terpenting agar pesan tetap ringkas
            }
        }
    }

    $lines[] = "";
    $lines[] = "Berkas pendaftaran telah saya unggah melalui portal resmi. Mohon informasi verifikasi selanjutnya. Terima kasih.";

    return implode("\n", $lines);
}

/**
 * Menghasilkan tautan wa.me resmi dengan URL-encoding yang benar menggunakan http_build_query
 */
function suic_build_wa_link(array $data): string {
    $phone = suic_get_committee_wa_number();
    $message = suic_build_wa_message($data);
    return 'https://wa.me/' . $phone . '?' . http_build_query(['text' => $message], '', '&', PHP_QUERY_RFC3986);
}

function suic_ensure_session(): void {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        @session_start();
    }
}

// ── 2. MANAJEMEN DATA SUKSES DALAM SESI ──────────────────────
function suic_set_registration_success_data(array $data): void {
    suic_ensure_session();
    $_SESSION['suic_registration_success'] = $data;
}

function suic_get_registration_success_data(?string $fallbackCategory = null): array {
    suic_ensure_session();
    $data = $_SESSION['suic_registration_success'] ?? [];
    if (empty($data['kategori_idc']) && $fallbackCategory !== null && $fallbackCategory !== '') {
        $data['kategori_idc'] = $fallbackCategory;
    }
    return $data;
}

function suic_clear_registration_success_data(): void {
    suic_ensure_session();
    unset($_SESSION['suic_registration_success']);
}

// ── 3. CSRF & PENCEGAHAN DUPLIKASI SUBMISI ────────────────────
function suic_csrf_token(): string {
    suic_ensure_session();
    if (empty($_SESSION['suic_csrf_token'])) {
        $_SESSION['suic_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['suic_csrf_token'];
}

function suic_csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(suic_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function suic_verify_csrf(?string $token = null): bool {
    suic_ensure_session();
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    if (empty($_SESSION['suic_csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['suic_csrf_token'], (string)$token);
}

function suic_generate_submission_token(): string {
    suic_ensure_session();
    $token = bin2hex(random_bytes(16));
    if (!isset($_SESSION['suic_active_form_tokens']) || !is_array($_SESSION['suic_active_form_tokens'])) {
        $_SESSION['suic_active_form_tokens'] = [];
    }
    $_SESSION['suic_active_form_tokens'][$token] = time();
    // Bersihkan token lama (> 2 jam)
    foreach ($_SESSION['suic_active_form_tokens'] as $t => $time) {
        if (time() - $time > 7200) {
            unset($_SESSION['suic_active_form_tokens'][$t]);
        }
    }
    return $token;
}

function suic_submission_token_field(): string {
    return '<input type="hidden" name="submission_token" value="' . htmlspecialchars(suic_generate_submission_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function suic_consume_submission_token(?string $token): bool {
    suic_ensure_session();
    if (empty($token)) {
        return true; // Jika tidak ada token khusus, tidak blokir form standar
    }
    if (!isset($_SESSION['suic_active_form_tokens'][$token])) {
        // Token tidak ada atau sudah pernah digunakan (resubmission)
        return false;
    }
    // Hapus token setelah digunakan
    unset($_SESSION['suic_active_form_tokens'][$token]);
    return true;
}
