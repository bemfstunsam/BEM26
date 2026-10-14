<?php
/**
 * Manajemen Administrator — Admin Panel SUIC 2026
 * Hanya dapat diakses oleh super_admin.
 */
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
requireSuperAdmin();
require_once __DIR__ . '/../../config/db.php';

$pageTitle   = 'Administrator';
$currentPage = 'admins';

$conn = getDBConnection();
$stmt = $conn->prepare(
    "SELECT id, username, nama, peran, aktif, dibuat_pada FROM admin ORDER BY dibuat_pada DESC"
);
$stmt->execute();
$admins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

include __DIR__ . '/../includes/layout_head.php';
?>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-gray-500">Kelola akun administrator panel SUIC 2026.</p>
    <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
            class="btn-primary text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Admin
    </button>
</div>

<!-- Table -->
<div class="card">
    <?php if (empty($admins)): ?>
    <div class="text-center py-12 text-gray-600 text-sm">Belum ada admin.</div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-700 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="text-left px-5 py-3">Nama / Username</th>
                    <th class="text-left px-5 py-3">Peran</th>
                    <th class="text-left px-5 py-3">Status</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Dibuat</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $adm):
                    $isSelf = (int)$adm['id'] === (int)$_SESSION['admin_id'];
                ?>
                <tr class="table-row-hover border-b border-gray-800/50">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-white"><?= e($adm['nama']) ?> <?= $isSelf ? '<span class="text-xs text-orange-400">(Anda)</span>' : '' ?></p>
                        <p class="text-xs text-gray-500 mt-0.5">@<?= e($adm['username']) ?></p>
                    </td>
                    <td class="px-5 py-3.5"><?= peranBadge($adm['peran']) ?></td>
                    <td class="px-5 py-3.5">
                        <?php if ($adm['aktif']): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-500/15 text-green-400 border border-green-500/30">Aktif</span>
                        <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-500 border border-gray-600/30">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500 hidden sm:table-cell">
                        <?= e(date('d M Y', strtotime($adm['dibuat_pada']))) ?>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex gap-2 justify-end">
                            <button onclick="openEditModal(<?= htmlspecialchars(json_encode([
                                'id'       => $adm['id'],
                                'username' => $adm['username'],
                                'nama'     => $adm['nama'],
                                'peran'    => $adm['peran'],
                                'aktif'    => $adm['aktif'],
                            ]), ENT_QUOTES) ?>)"
                                class="btn-secondary py-1 px-3 text-xs">Edit</button>
                            <?php if (!$isSelf): ?>
                            <form method="POST" action="<?= e(adminBaseUrl() . 'actions/admin_save.php') ?>"
                                  onsubmit="return confirm('<?= $adm['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?> admin ini?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle_aktif">
                                <input type="hidden" name="admin_id" value="<?= (int)$adm['id'] ?>">
                                <button type="submit" class="<?= $adm['aktif'] ? 'btn-danger' : 'btn-secondary' ?> py-1 px-3 text-xs">
                                    <?= $adm['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Kategori IDC (readonly reference) -->
<div class="card mt-5 p-5">
    <h3 class="text-sm font-bold text-white mb-4 pb-3 border-b border-gray-700">
        Kategori IDC — SUIC 2026 (Referensi)
    </h3>
    <p class="text-xs text-gray-500 mb-3">Lima kategori yang ditetapkan penyelenggara. Tidak dapat diubah melalui panel admin.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php
        $catIcons = ['🚁', '🌿', '🐟', '🔬', '📦'];
        $catDescs = [
            'Desain Drone Pertanian',
            'Desain Alat Pengolahan Hasil Perkebunan',
            'Desain Alat Pengolahan Hasil Perikanan',
            'Desain Alat atau Perangkat Sains',
            'Desain Produk',
        ];
        foreach (IDC_CATEGORIES as $i => $cat):
        ?>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4 flex gap-3">
            <span class="text-2xl"><?= $catIcons[$i] ?></span>
            <div>
                <p class="text-sm font-semibold text-white leading-snug"><?= e($cat) ?></p>
                <p class="text-xs text-gray-500 mt-0.5"><?= e($catDescs[$i]) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Tambah Admin -->
<div id="modal-create" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
            <h3 class="text-base font-bold text-white">Tambah Administrator</h3>
            <button onclick="document.getElementById('modal-create').classList.add('hidden')" class="text-gray-500 hover:text-white">✕</button>
        </div>
        <form method="POST" action="<?= e(adminBaseUrl() . 'actions/admin_save.php') ?>" class="p-6 space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">
            <div>
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-input" required maxlength="200" placeholder="Nama Admin">
            </div>
            <div>
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required maxlength="100" placeholder="username" autocomplete="off">
            </div>
            <div>
                <label class="form-label">Password (min. 8 karakter)</label>
                <input type="password" name="password" class="form-input" required minlength="8" autocomplete="new-password">
            </div>
            <div>
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirm" class="form-input" required minlength="8" autocomplete="new-password">
            </div>
            <div>
                <label class="form-label">Peran</label>
                <select name="peran" class="form-input" required>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 justify-center">Simpan</button>
                <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')"
                        class="btn-secondary flex-1 justify-center">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Admin -->
<div id="modal-edit" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
            <h3 class="text-base font-bold text-white">Edit Administrator</h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-gray-500 hover:text-white">✕</button>
        </div>
        <form method="POST" action="<?= e(adminBaseUrl() . 'actions/admin_save.php') ?>" class="p-6 space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="admin_id" id="edit-id">
            <div>
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" id="edit-nama" class="form-input" required maxlength="200">
            </div>
            <div>
                <label class="form-label">Username</label>
                <input type="text" name="username" id="edit-username" class="form-input" required maxlength="100">
            </div>
            <div>
                <label class="form-label">Password Baru (kosongkan jika tidak berubah)</label>
                <input type="password" name="password" class="form-input" minlength="8" autocomplete="new-password" placeholder="••••••••">
            </div>
            <div>
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirm" class="form-input" minlength="8" autocomplete="new-password">
            </div>
            <div>
                <label class="form-label">Peran</label>
                <select name="peran" id="edit-peran" class="form-input" required>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 justify-center">Simpan</button>
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="btn-secondary flex-1 justify-center">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('edit-id').value       = data.id;
    document.getElementById('edit-nama').value     = data.nama;
    document.getElementById('edit-username').value = data.username;
    document.getElementById('edit-peran').value    = data.peran;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>

<?php include __DIR__ . '/../includes/layout_foot.php'; ?>
