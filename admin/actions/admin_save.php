<?php
/**
 * Action: Manajemen Admin (create / edit / toggle_aktif)
 * POST-only. Hanya super_admin.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

$adminsUrl = adminBaseUrl() . 'index.php?page=admins';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $adminsUrl);
    exit;
}

requireAuth();
requireSuperAdmin();

if (!verifyCsrf()) {
    setFlash('error', 'Token keamanan tidak valid.');
    header('Location: ' . $adminsUrl);
    exit;
}

$action = trim($_POST['action'] ?? '');

// ─────────────────────────────────────────────────────────────
if ($action === 'create') {

    $nama      = trim(mb_substr($_POST['nama']     ?? '', 0, 200));
    $username  = trim(mb_substr($_POST['username'] ?? '', 0, 100));
    $password  = $_POST['password']         ?? '';
    $passConf  = $_POST['password_confirm'] ?? '';
    $peran     = trim($_POST['peran']        ?? '');

    // Validasi
    $errors = [];
    if (!$nama)                                      $errors[] = 'Nama wajib diisi.';
    if (!$username || !preg_match('/^\w{3,50}$/', $username)) $errors[] = 'Username tidak valid (hanya huruf, angka, underscore, 3-50 karakter).';
    if (strlen($password) < 8)                       $errors[] = 'Password minimal 8 karakter.';
    if ($password !== $passConf)                     $errors[] = 'Konfirmasi password tidak cocok.';
    if (!in_array($peran, ADMIN_ROLES, true))        $errors[] = 'Peran tidak valid.';

    if ($errors) {
        setFlash('error', implode(' | ', $errors));
        header('Location: ' . $adminsUrl);
        exit;
    }

    $conn = getDBConnection();

    // Cek username unik
    $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        $conn->close();
        setFlash('error', 'Username "' . e($username) . '" sudah digunakan.');
        header('Location: ' . $adminsUrl);
        exit;
    }
    $stmt->close();

    $hash  = password_hash($password, PASSWORD_BCRYPT);
    $aktif = 1;
    $stmt  = $conn->prepare(
        "INSERT INTO admin (username, password, nama, peran, aktif) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('ssssi', $username, $hash, $nama, $peran, $aktif);

    if ($stmt->execute()) {
        $newId = (int)$conn->insert_id;
        $stmt->close();
        auditLog($conn, 'create_admin', 'admin', $newId,
            'Admin baru dibuat: username=' . $username . ', peran=' . $peran);
        $conn->close();
        setFlash('success', 'Admin "' . e($nama) . '" berhasil ditambahkan.');
    } else {
        $stmt->close();
        $conn->close();
        setFlash('error', 'Gagal menyimpan admin baru. Silakan coba lagi.');
    }
    header('Location: ' . $adminsUrl);
    exit;
}

// ─────────────────────────────────────────────────────────────
if ($action === 'edit') {

    $adminId   = filter_var($_POST['admin_id'] ?? 0, FILTER_VALIDATE_INT);
    $nama      = trim(mb_substr($_POST['nama']     ?? '', 0, 200));
    $username  = trim(mb_substr($_POST['username'] ?? '', 0, 100));
    $password  = $_POST['password']         ?? '';
    $passConf  = $_POST['password_confirm'] ?? '';
    $peran     = trim($_POST['peran']        ?? '');

    if (!$adminId || $adminId < 1) {
        setFlash('error', 'ID admin tidak valid.');
        header('Location: ' . $adminsUrl);
        exit;
    }

    $errors = [];
    if (!$nama)                                         $errors[] = 'Nama wajib diisi.';
    if (!$username || !preg_match('/^\w{3,50}$/', $username)) $errors[] = 'Username tidak valid.';
    if (!in_array($peran, ADMIN_ROLES, true))           $errors[] = 'Peran tidak valid.';
    if ($password !== '' && strlen($password) < 8)      $errors[] = 'Password minimal 8 karakter.';
    if ($password !== '' && $password !== $passConf)     $errors[] = 'Konfirmasi password tidak cocok.';

    if ($errors) {
        setFlash('error', implode(' | ', $errors));
        header('Location: ' . $adminsUrl);
        exit;
    }

    $conn = getDBConnection();

    // Pastikan admin ada
    $stmt = $conn->prepare("SELECT id, username FROM admin WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$existing) {
        $conn->close();
        setFlash('error', 'Admin tidak ditemukan.');
        header('Location: ' . $adminsUrl);
        exit;
    }

    // Cek username unik (kecuali milik sendiri)
    if ($username !== $existing['username']) {
        $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ? AND id != ? LIMIT 1");
        $stmt->bind_param('si', $username, $adminId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            $conn->close();
            setFlash('error', 'Username "' . e($username) . '" sudah digunakan.');
            header('Location: ' . $adminsUrl);
            exit;
        }
        $stmt->close();
    }

    if ($password !== '') {
        // Update dengan password baru
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE admin SET nama=?, username=?, password=?, peran=? WHERE id=?");
        $stmt->bind_param('ssssi', $nama, $username, $hash, $peran, $adminId);
    } else {
        // Update tanpa password
        $stmt = $conn->prepare("UPDATE admin SET nama=?, username=?, peran=? WHERE id=?");
        $stmt->bind_param('sssi', $nama, $username, $peran, $adminId);
    }

    if ($stmt->execute()) {
        $stmt->close();
        auditLog($conn, 'edit_admin', 'admin', $adminId,
            'Admin diedit: username=' . $username . ', peran=' . $peran .
            ($password !== '' ? ', password diubah' : ''));
        $conn->close();
        setFlash('success', 'Data admin "' . e($nama) . '" berhasil diperbarui.');
    } else {
        $stmt->close();
        $conn->close();
        setFlash('error', 'Gagal memperbarui data admin.');
    }
    header('Location: ' . $adminsUrl);
    exit;
}

// ─────────────────────────────────────────────────────────────
if ($action === 'toggle_aktif') {

    $adminId = filter_var($_POST['admin_id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$adminId || $adminId < 1) {
        setFlash('error', 'ID admin tidak valid.');
        header('Location: ' . $adminsUrl);
        exit;
    }

    // Tidak bisa menonaktifkan diri sendiri
    if ($adminId === (int)$_SESSION['admin_id']) {
        setFlash('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        header('Location: ' . $adminsUrl);
        exit;
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT aktif, nama FROM admin WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $adm = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$adm) {
        $conn->close();
        setFlash('error', 'Admin tidak ditemukan.');
        header('Location: ' . $adminsUrl);
        exit;
    }

    // Jangan biarkan super_admin terakhir yang aktif dinonaktifkan
    if ($adm['aktif']) {
        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS n FROM admin WHERE peran='super_admin' AND aktif=1 AND id != ?"
        );
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $remaining = (int)$stmt->get_result()->fetch_assoc()['n'];
        $stmt->close();

        // Cek apakah yang akan dinonaktifkan adalah super_admin
        $stmtPeran = $conn->prepare("SELECT peran FROM admin WHERE id = ? LIMIT 1");
        $stmtPeran->bind_param('i', $adminId);
        $stmtPeran->execute();
        $peranRow = $stmtPeran->get_result()->fetch_assoc();
        $stmtPeran->close();

        if ($peranRow['peran'] === 'super_admin' && $remaining === 0) {
            $conn->close();
            setFlash('error', 'Tidak dapat menonaktifkan satu-satunya Super Admin aktif.');
            header('Location: ' . $adminsUrl);
            exit;
        }
    }

    $newAktif = $adm['aktif'] ? 0 : 1;
    $stmt = $conn->prepare("UPDATE admin SET aktif = ? WHERE id = ?");
    $stmt->bind_param('ii', $newAktif, $adminId);

    if ($stmt->execute()) {
        $stmt->close();
        $label = $newAktif ? 'diaktifkan' : 'dinonaktifkan';
        auditLog($conn, 'toggle_aktif', 'admin', $adminId,
            'Admin "' . $adm['nama'] . '" ' . $label);
        $conn->close();
        setFlash('success', 'Admin "' . e($adm['nama']) . '" berhasil ' . $label . '.');
    } else {
        $stmt->close();
        $conn->close();
        setFlash('error', 'Gagal mengubah status aktif admin.');
    }
    header('Location: ' . $adminsUrl);
    exit;
}

// Aksi tidak dikenal
setFlash('error', 'Aksi tidak dikenal.');
header('Location: ' . $adminsUrl);
exit;
