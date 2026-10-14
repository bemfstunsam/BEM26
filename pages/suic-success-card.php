<?php
/**
 * Komponen Notifikasi Sukses Pendaftaran & Konfirmasi WhatsApp Resmi
 * SUIC 2026 — International Design Competition (IDC)
 * BEM FST Universitas Samudra
 */
require_once __DIR__ . '/../config/suic_config.php';

$currentCategory = $compCategory ?? '';
$successData = suic_get_registration_success_data($currentCategory);

$waLink = suic_build_wa_link($successData);
$waMessage = suic_build_wa_message($successData);

$namaPeserta    = $successData['nama_lengkap'] ?? '';
$nimPeserta     = $successData['nim'] ?? '';
$univPeserta    = $successData['universitas'] ?? '';
$negaraPeserta  = $successData['negara'] ?? '';
$emailPeserta   = $successData['email'] ?? '';
$kategoriPeserta= $successData['kategori_idc'] ?? ($currentCategory ?: 'International Design Competition (IDC)');
$judulKarya     = $successData['judul_karya'] ?? '';
?>

<!-- ══ SUIC REGISTRATION SUCCESS & WHATSAPP CONFIRMATION CARD ══ -->
<div class="bg-gray-800/95 border border-emerald-500/40 rounded-2xl p-6 md:p-8 shadow-2xl shadow-emerald-950/40 text-center relative overflow-hidden mb-10">

    <!-- Decorative Top Accent -->
    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 via-green-400 to-emerald-600"></div>

    <!-- Success Checkmark Icon -->
    <div class="w-16 h-16 md:w-20 md:h-20 bg-emerald-500/20 border-2 border-emerald-500/40 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/20 animate-bounce">
        <svg class="h-8 w-8 md:h-10 md:w-10 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <!-- Main Success Heading -->
    <span class="inline-block bg-emerald-500/15 text-emerald-400 text-xs font-bold uppercase tracking-wider px-3.5 py-1 rounded-full border border-emerald-500/30 mb-2">
        Pendaftaran Berhasil Tersimpan ✓
    </span>
    <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-2 leading-tight">
        Selamat, Pendaftaran Anda Telah Berhasil! 🎉
    </h2>
    <p class="text-gray-300 text-sm max-w-xl mx-auto mb-6 leading-relaxed">
        Data pendaftaran Anda untuk kategori <strong class="text-emerald-400"><?= htmlspecialchars($kategoriPeserta, ENT_QUOTES, 'UTF-8') ?></strong> telah sukses diverifikasi di server dan tersimpan aman di database SUIC 2026.
    </p>

    <!-- Participant Details Summary Box (Only if participant data is available) -->
    <?php if ($namaPeserta !== '' || $emailPeserta !== ''): ?>
    <div class="bg-gray-900/90 border border-gray-700/80 rounded-xl p-5 mb-6 text-left max-w-xl mx-auto text-xs sm:text-sm">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-800">
            <span class="font-bold text-gray-200 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z" clip-rule="evenodd"/>
                </svg>
                Ringkasan Pendaftaran
            </span>
            <span class="text-[11px] bg-emerald-500/20 text-emerald-300 px-2.5 py-0.5 rounded-full font-semibold">
                Status: Tersimpan (Pending Review)
            </span>
        </div>

        <div class="space-y-2 text-gray-300">
            <?php if ($namaPeserta !== ''): ?>
            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-800/60">
                <span class="text-gray-400 font-medium">Nama Peserta:</span>
                <span class="font-semibold text-white"><?= htmlspecialchars($namaPeserta, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>

            <?php if ($nimPeserta !== ''): ?>
            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-800/60">
                <span class="text-gray-400 font-medium">NIM / Student ID:</span>
                <span class="text-gray-200"><?= htmlspecialchars($nimPeserta, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>

            <?php if ($univPeserta !== ''): ?>
            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-800/60">
                <span class="text-gray-400 font-medium">Universitas / Institusi:</span>
                <span class="text-gray-200"><?= htmlspecialchars($univPeserta, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>

            <?php if ($negaraPeserta !== ''): ?>
            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-800/60">
                <span class="text-gray-400 font-medium">Asal Negara:</span>
                <span class="text-gray-200"><?= htmlspecialchars($negaraPeserta, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>

            <?php if ($emailPeserta !== ''): ?>
            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-800/60">
                <span class="text-gray-400 font-medium">Email:</span>
                <span class="text-gray-200"><?= htmlspecialchars($emailPeserta, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-800/60">
                <span class="text-gray-400 font-medium">Kompetisi & Kategori:</span>
                <span class="text-emerald-400 font-semibold"><?= htmlspecialchars($kategoriPeserta, ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <?php if ($judulKarya !== ''): ?>
            <div class="flex flex-col sm:flex-row sm:justify-between py-1">
                <span class="text-gray-400 font-medium">Judul Karya / Desain:</span>
                <span class="text-gray-200 italic font-medium">"<?= htmlspecialchars($judulKarya, ENT_QUOTES, 'UTF-8') ?>"</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══ OFFICIAL WHATSAPP CONFIRMATION SECTION ══ -->
    <div class="bg-gradient-to-br from-emerald-950/60 to-gray-900 border border-emerald-500/40 rounded-xl p-5 md:p-6 mb-6 max-w-xl mx-auto text-center shadow-lg">
        <div class="flex items-center justify-center gap-2 mb-2 text-emerald-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
            </svg>
            <span class="font-extrabold text-sm uppercase tracking-wide">Tahap Konfirmasi Panitia</span>
        </div>
        <p class="text-gray-300 text-xs md:text-sm mb-4 leading-relaxed">
            Klik tombol di bawah ini untuk mengirimkan pesan konfirmasi otomatis ke WhatsApp resmi Panitia SUIC 2026 guna percepatan verifikasi berkas:
        </p>

        <!-- Primary WhatsApp Button -->
        <a href="<?= htmlspecialchars($waLink, ENT_QUOTES, 'UTF-8') ?>"
           target="_blank"
           rel="noopener noreferrer"
           id="whatsapp-confirm-btn"
           class="inline-flex items-center justify-center gap-3 w-full sm:w-auto px-6 py-3.5 bg-[#25D366] hover:bg-[#20ba59] active:bg-[#1caa52] text-white font-extrabold text-sm md:text-base rounded-xl transition-all duration-300 shadow-lg shadow-green-500/30 hover:shadow-green-500/50 hover:scale-[1.02] active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.791.808 2.791.808 3.182 0 5.768-2.587 5.768-5.766.001-3.182-2.585-5.794-5.768-5.794zm0-2.172c4.418 0 8 3.582 8 8 0 4.419-3.582 8-8 8-1.42 0-2.75-.371-3.905-1.018l-5.095 1.334 1.36-4.97c-.71-1.205-1.11-2.613-1.11-4.112 0-4.418 3.582-8 8-8z"/>
            </svg>
            <span>Konfirmasi melalui WhatsApp</span>
        </a>

        <!-- Informational Note -->
        <p class="text-[11px] text-gray-400 mt-3 italic">
            * Pesan verifikasi telah disiapkan secara otomatis. Anda cukup mengirimkan pesan tersebut saat aplikasi WhatsApp terbuka.
        </p>
    </div>

    <!-- Reassurance Guarantee -->
    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-3.5 mb-6 max-w-xl mx-auto text-left flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-blue-300/90 text-xs leading-relaxed">
            <strong>Catatan:</strong> Pendaftaran Anda tetap sah dan sudah tersimpan di sistem meskipun Anda belum atau tidak membuka WhatsApp. Konfirmasi via WhatsApp berfungsi mempercepat koordinasi dengan panitia pelaksana.
        </p>
    </div>

    <!-- Secondary Actions -->
    <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
        <a href="index.php?page=suic"
           class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs sm:text-sm font-semibold rounded-xl border border-gray-700 transition-colors">
            Lihat Kategori Kompetisi Lain
        </a>
        <a href="index.php?page=beranda"
           class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-xs sm:text-sm font-bold rounded-xl transition-colors shadow-lg shadow-orange-500/20">
            Kembali ke Beranda BEM
        </a>
    </div>

</div>
