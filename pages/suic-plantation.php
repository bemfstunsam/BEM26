<?php
/**
 * Formulir Pendaftaran: Plantation Product Processing Equipment
 * International Design Competition (IDC) — SUIC 2026
 */
require_once __DIR__ . '/../config/suic_config.php';

$compCategory = 'Plantation Product Processing Equipment';
$compSlug     = 'plantation';
$returnPage   = 'suic-plantation';

$status = $_GET['status'] ?? '';
$errors = $_SESSION['suic_errors'] ?? [];
$old    = $_SESSION['suic_old'] ?? [];
unset($_SESSION['suic_errors'], $_SESSION['suic_old']);

$countries = ['Indonesia','Malaysia','Thailand','Singapore','Philippines','Vietnam','Myanmar','Cambodia','Laos','Brunei','Timor-Leste','Bangladesh','India','Pakistan','Nepal','Sri Lanka','China','Japan','South Korea','Australia','United States','United Kingdom','Germany','France','Netherlands','Other'];
?>

<style>
    .suic-input {
        background-color: #1F2937;
        border: 1px solid #374151;
        color: #F9FAFB;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        width: 100%;
        font-size: 0.875rem;
        transition: border-color .2s, box-shadow .2s;
        outline: none;
    }
    .suic-input:focus {
        border-color: #F97316;
        box-shadow: 0 0 0 3px rgba(249,115,22,.15);
    }
    .suic-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #D1D5DB;
        margin-bottom: 0.35rem;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .suic-file-label {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        border: 2px dashed #374151; border-radius: 0.75rem; padding: 1.25rem 1rem;
        cursor: pointer; transition: border-color .2s, background .2s;
        color: #6B7280; font-size: 0.85rem; text-align: center; gap: .35rem;
    }
    .suic-file-label:hover { border-color: #F97316; background: rgba(249,115,22,.05); color: #F97316; }
    input[type="file"] { display: none; }
    .suic-error { color: #F87171; font-size: 0.75rem; margin-top: .3rem; }
    @keyframes shake { 0%,100%{transform:translateX(0)} 20%,60%{transform:translateX(-6px)} 40%,80%{transform:translateX(6px)} }
    .shake { animation: shake .4s ease; }
</style>

<div class="bg-gray-900 min-h-screen py-12 md:py-16">
<div class="container mx-auto px-4 md:px-6 max-w-3xl">

    <!-- Back to Hub & Category Badge -->
    <div class="flex items-center justify-between mb-6">
        <a href="index.php?page=suic" class="inline-flex items-center gap-2 text-xs font-semibold text-gray-400 hover:text-orange-400 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Pilihan Kategori IDC
        </a>
        <span class="inline-block bg-emerald-500/15 text-emerald-400 text-xs font-bold uppercase tracking-wider px-3.5 py-1 rounded-full border border-emerald-500/30">
            Kategori 2 • 100% GRATIS
        </span>
    </div>

    <!-- Competition Hero -->
    <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700/80 rounded-2xl p-6 md:p-8 mb-8 shadow-xl">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 bg-emerald-500/20 border border-emerald-500/30 rounded-2xl flex items-center justify-center text-3xl flex-shrink-0 shadow-lg shadow-emerald-500/10">
                🌿
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white">Plantation Product Processing Equipment</h1>
                <p class="text-emerald-400 font-semibold text-sm mt-0.5">Alat Pengolahan Hasil Perkebunan • SUIC 2026</p>
                <p class="text-gray-400 text-xs md:text-sm mt-2 leading-relaxed">
                    Perancangan alat/mesin pasca-panen komoditas perkebunan (sawit, kopi, kakao, karet, tebu, pinang, nilam, dll.) yang hemat energi, meningkatkan higienitas serta nilai tambah hasil kebun masyarakat.
                </p>
            </div>
        </div>
    </div>

    <?php if ($status === 'success'): ?>
        <?php include __DIR__ . '/suic-success-card.php'; ?>
    <?php else: ?>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-5 py-4 mb-6">
        <p class="text-red-400 font-semibold text-sm mb-2">⚠️ Mohon perbaiki kesalahan berikut:</p>
        <ul class="list-disc list-inside text-red-400 text-xs space-y-1">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- ══ FORMULIR SPESIFIK: PLANTATION EQUIPMENT ══ -->
    <form id="plantation-form" action="config/suic_submit.php" method="POST" enctype="multipart/form-data"
          class="bg-gray-800/90 border border-gray-700 rounded-2xl p-6 md:p-8 space-y-6 shadow-xl">

        <?= suic_csrf_field() ?>
        <?= suic_submission_token_field() ?>
        <input type="hidden" name="kategori_idc" value="<?= htmlspecialchars($compCategory) ?>">
        <input type="hidden" name="return_page" value="<?= htmlspecialchars($returnPage) ?>">
        <input type="hidden" name="comp_param" value="<?= htmlspecialchars($compSlug) ?>">

        <!-- Section 1: Data Identitas Peserta -->
        <div>
            <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2 pb-2 border-b border-gray-700">
                <span class="text-emerald-400">👤</span> Bagian 1: Identitas Peserta
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="suic-label" for="nama_lengkap">Nama Lengkap <span class="text-orange-500">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="suic-input"
                           placeholder="Nama lengkap peserta"
                           value="<?= htmlspecialchars($old['nama_lengkap'] ?? '') ?>" required>
                    <p class="suic-error hidden" id="err-nama">Nama lengkap wajib diisi.</p>
                </div>
                <div>
                    <label class="suic-label" for="nim">NIM / Student ID <span class="text-orange-500">*</span></label>
                    <input type="text" id="nim" name="nim" class="suic-input"
                           placeholder="Contoh: 2201010001"
                           value="<?= htmlspecialchars($old['nim'] ?? '') ?>" required>
                    <p class="suic-error hidden" id="err-nim">NIM wajib diisi.</p>
                </div>
                <div>
                    <label class="suic-label" for="universitas">Universitas / Institusi <span class="text-orange-500">*</span></label>
                    <input type="text" id="universitas" name="universitas" class="suic-input"
                           placeholder="Contoh: Universitas Samudra"
                           value="<?= htmlspecialchars($old['universitas'] ?? '') ?>" required>
                    <p class="suic-error hidden" id="err-universitas">Universitas wajib diisi.</p>
                </div>
                <div>
                    <label class="suic-label" for="negara">Negara Asal <span class="text-orange-500">*</span></label>
                    <select id="negara" name="negara" class="suic-input" required>
                        <option value="" disabled <?= empty($old['negara']) ? 'selected' : '' ?>>-- Pilih Negara --</option>
                        <?php foreach($countries as $c): ?>
                        <option value="<?= $c ?>" <?= (($old['negara'] ?? '') === $c) ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="suic-error hidden" id="err-negara">Negara wajib dipilih.</p>
                </div>
                <div>
                    <label class="suic-label" for="nomor_hp">Nomor HP / WhatsApp <span class="text-orange-500">*</span></label>
                    <input type="tel" id="nomor_hp" name="nomor_hp" class="suic-input"
                           placeholder="+62 812 3456 7890"
                           value="<?= htmlspecialchars($old['nomor_hp'] ?? '') ?>" required>
                    <p class="suic-error hidden" id="err-hp">Nomor HP wajib diisi.</p>
                </div>
                <div class="md:col-span-2">
                    <label class="suic-label" for="email">Email Aktif <span class="text-orange-500">*</span></label>
                    <input type="email" id="email" name="email" class="suic-input"
                           placeholder="nama@email.com"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                    <p class="suic-error hidden" id="err-email">Format email tidak valid.</p>
                </div>
            </div>
        </div>

        <!-- Section 2: Spesifikasi Alat Perkebunan -->
        <div class="pt-2">
            <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2 pb-2 border-b border-gray-700">
                <span class="text-emerald-400">⚙️</span> Bagian 2: Spesifikasi Desain Alat Pengolahan Perkebunan
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="suic-label" for="judul_karya">Nama / Judul Inovasi Mesin <span class="text-orange-500">*</span></label>
                    <input type="text" id="judul_karya" name="judul_karya" class="suic-input"
                           placeholder="Contoh: Mesin Perontok & Pengupas Kulit Kopi Portabel Bertenaga Surya"
                           value="<?= htmlspecialchars($old['judul_karya'] ?? '') ?>" required>
                    <p class="suic-error hidden" id="err-judul">Nama inovasi mesin wajib diisi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="suic-label" for="plantation_commodity">Komoditas Perkebunan Sasaran <span class="text-orange-500">*</span></label>
                        <select id="plantation_commodity" name="plantation_commodity" class="suic-input" required>
                            <option value="">-- Pilih Komoditas --</option>
                            <option value="Kelapa Sawit" <?= (($old['plantation_commodity'] ?? '') === 'Kelapa Sawit') ? 'selected' : '' ?>>Kelapa Sawit</option>
                            <option value="Kopi" <?= (($old['plantation_commodity'] ?? '') === 'Kopi') ? 'selected' : '' ?>>Kopi</option>
                            <option value="Kakao / Cokelat" <?= (($old['plantation_commodity'] ?? '') === 'Kakao / Cokelat') ? 'selected' : '' ?>>Kakao / Cokelat</option>
                            <option value="Karet" <?= (($old['plantation_commodity'] ?? '') === 'Karet') ? 'selected' : '' ?>>Karet</option>
                            <option value="Nilam / Minyak Atsiri" <?= (($old['plantation_commodity'] ?? '') === 'Nilam / Minyak Atsiri') ? 'selected' : '' ?>>Nilam / Minyak Atsiri</option>
                            <option value="Pinang / Tebu" <?= (($old['plantation_commodity'] ?? '') === 'Pinang / Tebu') ? 'selected' : '' ?>>Pinang / Tebu</option>
                            <option value="Lainnya" <?= (($old['plantation_commodity'] ?? '') === 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="suic-label" for="plantation_stage">Tahap Pengolahan Pasca-Panen <span class="text-orange-500">*</span></label>
                        <select id="plantation_stage" name="plantation_stage" class="suic-input" required>
                            <option value="">-- Pilih Tahap --</option>
                            <option value="Pemanenan / Sortasi" <?= (($old['plantation_stage'] ?? '') === 'Pemanenan / Sortasi') ? 'selected' : '' ?>>Pemanenan / Sortasi</option>
                            <option value="Pengupasan / Pemisahan Kulit" <?= (($old['plantation_stage'] ?? '') === 'Pengupasan / Pemisahan Kulit') ? 'selected' : '' ?>>Pengupasan / Pemisahan Kulit</option>
                            <option value="Pengeringan / Pengering Surya" <?= (($old['plantation_stage'] ?? '') === 'Pengeringan / Pengering Surya') ? 'selected' : '' ?>>Pengeringan / Pengering Surya</option>
                            <option value="Pencacahan / Penggilingan / Ekstraksi" <?= (($old['plantation_stage'] ?? '') === 'Pencacahan / Penggilingan / Ekstraksi') ? 'selected' : '' ?>>Pencacahan / Penggilingan / Ekstraksi</option>
                            <option value="Pengemasan & Penyimpanan" <?= (($old['plantation_stage'] ?? '') === 'Pengemasan & Penyimpanan') ? 'selected' : '' ?>>Pengemasan & Penyimpanan</option>
                        </select>
                    </div>

                    <div>
                        <label class="suic-label" for="plantation_power">Sumber Tenaga / Penggerak <span class="text-orange-500">*</span></label>
                        <select id="plantation_power" name="plantation_power" class="suic-input" required>
                            <option value="">-- Pilih Penggerak --</option>
                            <option value="Motor Listrik" <?= (($old['plantation_power'] ?? '') === 'Motor Listrik') ? 'selected' : '' ?>>Motor Listrik</option>
                            <option value="Tenaga Surya (Solar PV)" <?= (($old['plantation_power'] ?? '') === 'Tenaga Surya (Solar PV)') ? 'selected' : '' ?>>Tenaga Surya (Solar PV)</option>
                            <option value="Manual / Mekanik Ergonomis" <?= (($old['plantation_power'] ?? '') === 'Manual / Mekanik Ergonomis') ? 'selected' : '' ?>>Manual / Mekanik Ergonomis</option>
                            <option value="Biomassa / Panas Limbah" <?= (($old['plantation_power'] ?? '') === 'Biomassa / Panas Limbah') ? 'selected' : '' ?>>Biomassa / Panas Limbah</option>
                            <option value="Hybrid" <?= (($old['plantation_power'] ?? '') === 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
                        </select>
                    </div>

                    <div>
                        <label class="suic-label" for="plantation_capacity">Estimasi Kapasitas Kerja</label>
                        <input type="text" id="plantation_capacity" name="plantation_capacity" class="suic-input"
                               placeholder="Contoh: 150 kg/jam atau 50 liter/proses"
                               value="<?= htmlspecialchars($old['plantation_capacity'] ?? '') ?>">
                    </div>
                </div>

                <div>
                    <label class="suic-label" for="deskripsi_karya">Uraian Cara Kerja & Nilai Efisiensi Alat <span class="text-orange-500">*</span></label>
                    <textarea id="deskripsi_karya" name="deskripsi_karya" rows="4" class="suic-input"
                              placeholder="Deskripsikan mekanisme kerja inovasi alat, efisiensi waktu, konsumsi energi, dan kemudahan pengoperasian bagi petani lokal..." required><?= htmlspecialchars($old['deskripsi_karya'] ?? '') ?></textarea>
                    <p class="suic-error hidden" id="err-deskripsi">Deskripsi alat wajib diisi.</p>
                </div>
            </div>
        </div>

        <!-- Section 3: Upload Dokumen -->
        <div class="pt-2">
            <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2 pb-2 border-b border-gray-700">
                <span class="text-emerald-400">📁</span> Bagian 3: Dokumen & Verifikasi Berkas
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="suic-label">Gambar Skematik / Dokumen Desain Mesin (Opsional)</label>
                    <label class="suic-file-label" for="file_proposal" id="label-proposal">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <span id="proposal-name">Klik untuk upload skematik / lembar konsep mesin</span>
                        <span class="text-xs text-gray-500">PDF — Maks. 10MB</span>
                    </label>
                    <input type="file" id="file_proposal" name="file_proposal" accept=".pdf"
                           onchange="previewFileCustom(this, 'proposal-name', 'label-proposal')">
                </div>

                <div>
                    <label class="suic-label">Surat Keterangan Aktif / KTM <span class="text-orange-500">*</span></label>
                    <label class="suic-file-label" for="file_surat" id="label-surat">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span id="surat-name">Klik untuk upload KTM atau Surat Aktif</span>
                        <span class="text-xs text-gray-500">PDF, JPG, PNG — Maks. 5MB</span>
                    </label>
                    <input type="file" id="file_surat" name="file_surat" accept=".pdf,.jpg,.jpeg,.png" required
                           onchange="previewFileCustom(this, 'surat-name', 'label-surat')">
                    <p class="suic-error hidden" id="err-surat">File KTM / Surat Aktif wajib diupload.</p>
                </div>

                <div>
                    <label class="suic-label">Bukti Follow Instagram SUIC <span class="text-orange-500">*</span></label>
                    <label class="suic-file-label" for="file_bukti_follow" id="label-bukti">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span id="bukti-name">Klik untuk upload tangkapan layar follow IG</span>
                        <span class="text-xs text-gray-500">JPG, PNG — Maks. 5MB</span>
                    </label>
                    <input type="file" id="file_bukti_follow" name="file_bukti_follow" accept=".jpg,.jpeg,.png" required
                           onchange="previewFileCustom(this, 'bukti-name', 'label-bukti')">
                    <p class="suic-error hidden" id="err-bukti">Bukti follow Instagram wajib diupload.</p>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-700">
            <button type="submit" id="submit-btn"
                    class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-600/20 text-base flex items-center justify-center gap-2">
                Daftar Plantation Product Processing Equipment
            </button>
            <p class="text-center text-xs text-gray-500 mt-2.5">
                Pendaftaran ini tidak dipungut biaya apapun (100% Gratis).
            </p>
        </div>
    </form>
    <?php endif; ?>

</div>
</div>

<script>
function previewFileCustom(input, nameId, labelId) {
    const nameEl  = document.getElementById(nameId);
    const labelEl = document.getElementById(labelId);
    if (input.files && input.files[0]) {
        nameEl.textContent = '✅ ' + input.files[0].name;
        labelEl.style.borderColor = '#10B981';
        labelEl.style.color       = '#10B981';
    }
}

var pForm = document.getElementById('plantation-form');
if (pForm) {
    pForm.addEventListener('submit', function(e) {
        let valid = true;
        function check(id, errId, condFn) {
            const el  = document.getElementById(id);
            const err = document.getElementById(errId);
            if (!el || !err) return;
            if (!condFn(el)) {
                err.classList.remove('hidden');
                el.classList.add('shake');
                el.addEventListener('animationend', function() { el.classList.remove('shake'); }, {once:true});
                if (valid) el.focus();
                valid = false;
            } else {
                err.classList.add('hidden');
            }
        }

        check('nama_lengkap',    'err-nama',        function(el) { return el.value.trim() !== ''; });
        check('nim',             'err-nim',         function(el) { return el.value.trim() !== ''; });
        check('universitas',     'err-universitas', function(el) { return el.value.trim() !== ''; });
        check('negara',          'err-negara',      function(el) { return el.value !== ''; });
        check('nomor_hp',        'err-hp',          function(el) { return el.value.trim() !== ''; });
        check('email',           'err-email',       function(el) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value.trim()); });
        check('judul_karya',     'err-judul',       function(el) { return el.value.trim() !== ''; });
        check('deskripsi_karya', 'err-deskripsi',   function(el) { return el.value.trim() !== ''; });
        check('file_surat',      'err-surat',       function(el) { return el.files && el.files.length > 0; });
        check('file_bukti_follow','err-bukti',      function(el) { return el.files && el.files.length > 0; });

        if (!valid) {
            e.preventDefault();
            return;
        }

        var btn = document.getElementById('submit-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Mengirimkan Pendaftaran...';
        }
    });
}
</script>
