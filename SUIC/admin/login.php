<?php
/**
 * Halaman Login — SUIC Admin Panel (Dedicated Registration Panel)
 * URL: /bem/suic/admin/login.php
 * Menggunakan tabel admin dan logika auth yang sama dengan admin utama.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../admin/includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

// Sudah login? Redirect ke panel registrasi
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error  = '';
$locked = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isLoginLocked()) {
        $locked = true;
        $error  = 'Terlalu banyak percobaan login. Silakan tunggu ' . loginLockRemaining() . ' detik.';
    } else {
        // Validasi CSRF
        if (!verifyCsrf()) {
            $error = 'Sesi form telah kadaluarsa. Silakan muat ulang halaman.';
        } else {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($username === '' || $password === '') {
                $error = 'Username dan password wajib diisi.';
            } else {
                $conn = getDBConnection();
                $stmt = $conn->prepare(
                    "SELECT id, username, password, nama, peran, aktif FROM admin WHERE username = ? LIMIT 1"
                );
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $admin  = $result->fetch_assoc();
                $stmt->close();
                $conn->close();

                if ($admin && (bool)$admin['aktif'] && password_verify($password, $admin['password'])) {
                    // Login berhasil
                    session_regenerate_id(true);
                    resetLoginAttempts();

                    $_SESSION['admin_id']    = (int)$admin['id'];
                    $_SESSION['admin_nama']  = $admin['nama'];
                    $_SESSION['admin_peran'] = $admin['peran'];
                    $_SESSION['admin_aktif'] = (bool)$admin['aktif'];
                    // Regenerate CSRF token
                    unset($_SESSION['csrf_token']);
                    csrfToken();

                    // Audit log
                    $conn2 = getDBConnection();
                    auditLog($conn2, 'login', 'admin', (int)$admin['id'], 'Login SUIC panel dari IP ' . ($_SERVER['REMOTE_ADDR'] ?? ''));
                    $conn2->close();

                    header('Location: index.php');
                    exit;
                } else {
                    recordLoginAttempt();
                    if ($admin && !(bool)$admin['aktif']) {
                        $error = 'Akun Anda telah dinonaktifkan.';
                    } else {
                        $error = 'Username atau password salah.';
                        if (isLoginLocked()) {
                            $error .= ' Akun dikunci selama ' . ceil(LOCKOUT_SECONDS / 60) . ' menit.';
                        }
                    }
                }
            }
        }
    }
}

$deactivated = ($_GET['err'] ?? '') === 'deactivated';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SUIC 2026 Registrasi Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .login-input {
            background:#1F2937; border:1px solid #374151; color:#F9FAFB;
            border-radius:0.75rem; padding:0.75rem 1rem; width:100%;
            font-size:0.875rem; outline:none;
            transition:border-color .2s, box-shadow .2s;
        }
        .login-input:focus { border-color:#F97316; box-shadow:0 0 0 3px rgba(249,115,22,.15); }
        .login-input::placeholder { color:#6B7280; }
    </style>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-sm">
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-orange-500/30">
            <span class="text-white font-extrabold text-2xl">S</span>
        </div>
        <h1 class="text-2xl font-extrabold text-white">SUIC 2026</h1>
        <p class="text-gray-500 text-sm mt-1">Panel Registrasi — International Design Competition</p>
    </div>

    <!-- Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-2xl">
        <h2 class="text-lg font-bold text-white mb-5">Masuk ke Panel Registrasi</h2>

        <?php if ($deactivated): ?>
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-4 text-red-400 text-sm">
            ⚠️ Akun Anda telah dinonaktifkan. Hubungi Super Admin.
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-4 text-red-400 text-sm font-medium">
            ⚠️ <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off" novalidate>
            <?= csrfField() ?>
            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Username</label>
                    <input type="text" id="username" name="username"
                           class="login-input" placeholder="admin"
                           value="<?= e($_POST['username'] ?? '') ?>"
                           autocomplete="username" required
                           <?= $locked ? 'disabled' : '' ?>>
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                               class="login-input pr-10" placeholder="••••••••"
                               autocomplete="current-password" required
                               <?= $locked ? 'disabled' : '' ?>>
                        <button type="button" onclick="togglePass()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300" tabindex="-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" <?= $locked ? 'disabled' : '' ?>
                    class="w-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed
                           text-white font-bold py-3 rounded-xl transition-colors text-sm tracking-wide mt-2">
                    Masuk
                </button>
            </div>
        </form>

        <div class="mt-4 pt-4 border-t border-gray-800 text-center">
            <a href="/bem/admin/index.php" class="text-xs text-gray-500 hover:text-orange-400 transition-colors">
                ← Kembali ke Admin Utama
            </a>
        </div>
    </div>

    <p class="text-center text-xs text-gray-600 mt-6">
        SUIC 2026 — Samudra University International Competition
    </p>
</div>

<script>
function togglePass() {
    var inp = document.getElementById('password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
