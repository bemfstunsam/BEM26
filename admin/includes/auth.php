<?php
/**
 * Auth guard, CSRF, helper, dan konstanta untuk Admin Panel SUIC 2026.
 * Di-include oleh setiap file admin. JANGAN include langsung dari publik.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Konstanta global ──────────────────────────────────────────
if (!defined('IDC_CATEGORIES')) {
    define('IDC_CATEGORIES', [
        'Agricultural Drone Design',
        'Plantation Product Processing Equipment',
        'Fishery Product Processing Equipment',
        'Scientific Tools Design',
        'Product Design',
    ]);
}
if (!defined('REG_STATUSES')) {
    define('REG_STATUSES', ['pending', 'approved', 'rejected']);
}
if (!defined('ADMIN_ROLES')) {
    define('ADMIN_ROLES', ['super_admin', 'admin']);
}
// Kolom yang diizinkan untuk sorting — divalidasi sebelum dimasukkan ke query
if (!defined('SORT_ALLOWLIST')) {
    define('SORT_ALLOWLIST', [
        'id', 'nama_lengkap', 'nim', 'universitas',
        'negara', 'email', 'kategori_idc', 'status', 'created_at',
    ]);
}
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
    define('LOCKOUT_SECONDS', 300); // 5 menit
}
if (!defined('UPLOAD_DIR')) {
    // Resolusi ke direktori uploads/suic/ dari manapun file ini di-include
    define('UPLOAD_DIR', realpath(__DIR__ . '/../../uploads/suic') . DIRECTORY_SEPARATOR);
}

// ── URL admin base ────────────────────────────────────────────
function adminBaseUrl(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $pos    = strpos($script, '/admin/');
    if ($pos === false) {
        return '/admin/';
    }
    return substr($script, 0, $pos + 7); // e.g. /bem/admin/
}

// ── Auth guard ────────────────────────────────────────────────
function requireAuth(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . adminBaseUrl() . 'login.php');
        exit;
    }
    // Blokir akun yang dinonaktifkan
    if (isset($_SESSION['admin_aktif']) && !$_SESSION['admin_aktif']) {
        session_unset();
        session_destroy();
        header('Location: ' . adminBaseUrl() . 'login.php?err=deactivated');
        exit;
    }
}

function requireSuperAdmin(): void {
    requireAuth();
    if (($_SESSION['admin_peran'] ?? '') !== 'super_admin') {
        http_response_code(403);
        // Render error inline agar tidak break layout jika dipanggil dari dalam halaman
        echo '<div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 text-red-400 text-sm font-semibold">'
           . '⛔ Akses ditolak — fitur ini memerlukan hak akses Super Admin.</div>';
        exit;
    }
}

function isLoggedIn(): bool { return !empty($_SESSION['admin_id']); }
function isSuperAdmin(): bool { return ($_SESSION['admin_peran'] ?? '') === 'super_admin'; }

// ── Login throttle (berbasis session, per browser) ────────────
function isLoginLocked(): bool {
    $attempts    = (int)($_SESSION['login_attempts'] ?? 0);
    $lastAttempt = (int)($_SESSION['login_last_attempt'] ?? 0);
    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        if ((time() - $lastAttempt) < LOCKOUT_SECONDS) {
            return true;
        }
        // Reset setelah periode lockout habis
        $_SESSION['login_attempts'] = 0;
    }
    return false;
}

function recordLoginAttempt(): void {
    $_SESSION['login_attempts']     = (int)($_SESSION['login_attempts'] ?? 0) + 1;
    $_SESSION['login_last_attempt'] = time();
}

function resetLoginAttempts(): void {
    $_SESSION['login_attempts']     = 0;
    $_SESSION['login_last_attempt'] = 0;
}

function loginLockRemaining(): int {
    $last = (int)($_SESSION['login_last_attempt'] ?? 0);
    return max(0, LOCKOUT_SECONDS - (time() - $last));
}

// ── CSRF ──────────────────────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && strlen($token) > 0
        && hash_equals($_SESSION['csrf_token'], $token);
}

// ── Output escaping ───────────────────────────────────────────
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ── CSV formula injection prevention ─────────────────────────
function csvSafe(?string $value): string {
    if ($value === null) return '';
    $value = str_replace(["\r", "\n", "\t"], ' ', $value);
    $first = mb_substr($value, 0, 1);
    if (in_array($first, ['=', '+', '-', '@', "\t", "\r", ';', '|', '%'], true)) {
        $value = "'" . $value; // Prefix dengan apostrophe — Excel membaca sebagai teks
    }
    return $value;
}

// ── Flash messages ────────────────────────────────────────────
function setFlash(string $type, string $message): void {
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return $flash;
}

// ── Audit log ─────────────────────────────────────────────────
function auditLog($conn, string $aksi, string $entitas, ?int $entitasId, string $deskripsi): void {
    $adminId   = (int)($_SESSION['admin_id'] ?? 0);
    $adminNama = $_SESSION['admin_nama'] ?? 'Unknown';
    $ip        = $_SERVER['REMOTE_ADDR'] ?? '';
    $idVal     = (int)($entitasId ?? 0);

    $stmt = $conn->prepare(
        "INSERT INTO suic_audit_log
            (admin_id, admin_nama, aksi, entitas, entitas_id, deskripsi, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    if ($stmt) {
        $stmt->bind_param('isssiss', $adminId, $adminNama, $aksi, $entitas, $idVal, $deskripsi, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

// ── UI helper: status badge ───────────────────────────────────
function statusBadge(string $status): string {
    $styles = [
        'pending'  => 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30',
        'approved' => 'bg-green-500/20  text-green-400  border border-green-500/30',
        'rejected' => 'bg-red-500/20    text-red-400    border border-red-500/30',
    ];
    $labels = [
        'pending'  => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];
    $cls   = $styles[$status]  ?? 'bg-gray-500/20 text-gray-400 border border-gray-500/30';
    $label = $labels[$status]  ?? e(ucfirst($status));
    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ' . $cls . '">'
         . e($label) . '</span>';
}

// ── UI helper: peran badge ────────────────────────────────────
function peranBadge(string $peran): string {
    if ($peran === 'super_admin') {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-500/20 text-orange-400 border border-orange-500/30">Super Admin</span>';
    }
    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30">Admin</span>';
}

// ── Pagination helper ─────────────────────────────────────────
function safePage(mixed $raw, int $default = 1): int {
    $p = filter_var($raw, FILTER_VALIDATE_INT);
    return ($p !== false && $p >= 1) ? $p : $default;
}

function safePerPage(mixed $raw, int $default = 20, int $max = 100): int {
    $p = filter_var($raw, FILTER_VALIDATE_INT);
    if ($p === false || $p < 1) return $default;
    return min($p, $max);
}

// ── Sort helper ───────────────────────────────────────────────
function safeSort(string $col, string $dir, string $default = 'created_at'): array {
    $col = in_array($col, SORT_ALLOWLIST, true) ? $col : $default;
    $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
    return [$col, $dir];
}
