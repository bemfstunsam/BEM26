<?php
/**
 * Master Portal & Router Pendaftaran SUIC 2026
 * International Design Competition (IDC)
 *
 * Mengarahkan ke formulir spesifik untuk 5 kompetisi TOR:
 * 1. Agricultural Drone Design
 * 2. Plantation Product Processing Equipment
 * 3. Fishery Product Processing Equipment
 * 4. Scientific Tools Design
 * 5. Product Design
 */
require_once __DIR__ . '/../config/suic_config.php';

$comp = trim($_GET['comp'] ?? '');

// Jika ada parameter kompetisi, include halaman/form spesifik
switch ($comp) {
    case 'drone':
    case 'agricultural-drone':
    case 'Agricultural Drone Design':
        include __DIR__ . '/suic-drone.php';
        return;

    case 'plantation':
    case 'plantation-equipment':
    case 'Plantation Product Processing Equipment':
        include __DIR__ . '/suic-plantation.php';
        return;

    case 'fishery':
    case 'fishery-equipment':
    case 'Fishery Product Processing Equipment':
        include __DIR__ . '/suic-fishery.php';
        return;

    case 'science':
    case 'scientific-tools':
    case 'Scientific Tools Design':
        include __DIR__ . '/suic-science.php';
        return;

    case 'product':
    case 'product-design':
    case 'Product Design':
        include __DIR__ . '/suic-product.php';
        return;
}

$status = $_GET['status'] ?? '';

// Definisi 5 kompetisi resmi IDC SUIC 2026
$competitions = [
    [
        'slug'       => 'drone',
        'page'       => 'suic-drone',
        'title'      => 'Agricultural Drone Design',
        'sub'        => 'Desain Drone Pertanian',
        'icon'       => '🚁',
        'badge'      => 'Kategori 1 • Agroteknologi',
        'color'      => 'orange',
        'border'     => 'border-orange-500/30 hover:border-orange-500',
        'bg_glow'    => 'hover:shadow-orange-500/10',
        'accent_bg'  => 'bg-orange-500/15',
        'accent_text'=> 'text-orange-400',
        'btn_class'  => 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/20 text-white',
        'desc'       => 'Perancangan drone multi-rotor atau hybrid VTOL untuk otomatisasi dan presisi sektor pertanian modern (penyemprotan presisi, pemetaan NDVI, penyebaran benih, dan monitoring kesehatan tanaman).',
        'features'   => [
            'Konfigurasi Drone & Estimasi Muatan (Payload)',
            'Misi Pemetaan & Penyemprotan Presisi',
            'Desain Arsitektur & Durasi Terbang',
        ],
    ],
    [
        'slug'       => 'plantation',
        'page'       => 'suic-plantation',
        'title'      => 'Plantation Product Processing Equipment',
        'sub'        => 'Desain Alat Pengolahan Hasil Perkebunan',
        'icon'       => '🌿',
        'badge'      => 'Kategori 2 • Mesin & Perkebunan',
        'color'      => 'emerald',
        'border'     => 'border-emerald-500/30 hover:border-emerald-500',
        'bg_glow'    => 'hover:shadow-emerald-500/10',
        'accent_bg'  => 'bg-emerald-500/15',
        'accent_text'=> 'text-emerald-400',
        'btn_class'  => 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/20 text-white',
        'desc'       => 'Perancangan alat/mesin pasca-panen komoditas perkebunan (sawit, kopi, kakao, karet, nilam, pinang, dll.) yang hemat energi, meningkatkan higienitas, dan memberikan nilai tambah ekonomi.',
        'features'   => [
            'Kustomisasi Komoditas Sasaran (Sawit, Kopi, Kakao, dll.)',
            'Tahap Pengolahan (Kupas, Keringkan, Ekstraksi)',
            'Pilihan Penggerak: Motor Listrik, Surya, Mekanik',
        ],
    ],
    [
        'slug'       => 'fishery',
        'page'       => 'suic-fishery',
        'title'      => 'Fishery Product Processing Equipment',
        'sub'        => 'Desain Alat Pengolahan Hasil Perikanan',
        'icon'       => '🐟',
        'badge'      => 'Kategori 3 • Maritim & Kelautan',
        'color'      => 'cyan',
        'border'     => 'border-cyan-500/30 hover:border-cyan-500',
        'bg_glow'    => 'hover:shadow-cyan-500/10',
        'accent_bg'  => 'bg-cyan-500/15',
        'accent_text'=> 'text-cyan-400',
        'btn_class'  => 'bg-cyan-600 hover:bg-cyan-500 shadow-cyan-600/20 text-white',
        'desc'       => 'Perancangan alat dan peralatan pasca-tangkap perikanan laut & tawar (pengasapan higienis, pengering ikan surya, mesin pelet pakan ikan, cold-chain portabel kapal nelayan, dan olahan limbah).',
        'features'   => [
            'Solusi Pengasapan, Cold-Chain, & Pengeringan',
            'Material Standar Food-Grade (SUS304)',
            'Kapasitas Produksi & Efisiensi UMKM Nelayan',
        ],
    ],
    [
        'slug'       => 'science',
        'page'       => 'suic-science',
        'title'      => 'Scientific Tools Design',
        'sub'        => 'Desain Alat atau Perangkat Sains',
        'icon'       => '🔬',
        'badge'      => 'Kategori 4 • Sains & Instrumentasi',
        'color'      => 'purple',
        'border'     => 'border-purple-500/30 hover:border-purple-500',
        'bg_glow'    => 'hover:shadow-purple-500/10',
        'accent_bg'  => 'bg-purple-500/15',
        'accent_text'=> 'text-purple-400',
        'btn_class'  => 'bg-purple-600 hover:bg-purple-500 shadow-purple-600/20 text-white',
        'desc'       => 'Perancangan instrumen laboratorium atau alat ukur sains (sensor IoT terpadu, spektrometer portabel, penguji kualitas air/tanah presisi, inkubator lab) berbasis teknologi berdaya guna.',
        'features'   => [
            'Domain Bidang: Fisika, Kimia, Biologi, Sensor IoT',
            'Parameter Pengukuran & Batas Sensitivitas',
            'Platform Kontroler & Skematik Alat',
        ],
    ],
    [
        'slug'       => 'product',
        'page'       => 'suic-product',
        'title'      => 'Product Design',
        'sub'        => 'Desain Produk Konsumer & Industri',
        'icon'       => '📦',
        'badge'      => 'Kategori 5 • Desain Produk & Inovasi',
        'color'      => 'amber',
        'border'     => 'border-amber-500/30 hover:border-amber-500',
        'bg_glow'    => 'hover:shadow-amber-500/10',
        'accent_bg'  => 'bg-amber-500/15',
        'accent_text'=> 'text-amber-400',
        'btn_class'  => 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/20 text-gray-950 font-bold',
        'desc'       => 'Perancangan produk industri komersial bernilai estetika tinggi, ergonomis, ramah lingkungan (material ramah lingkungan/daur ulang), berpusat pada pengguna, dan siap diproduksi massal.',
        'features'   => [
            'Kategori Produk: Gadget, Home Living, Kemasan, Medis',
            'Analisis Target Pasar & User Persona',
            'Material Berkelanjutan & Estetika Ergonomis',
        ],
    ],
];
?>

<div class="bg-gray-900 min-h-screen py-16 md:py-20">
<div class="container mx-auto px-4 md:px-6 max-w-5xl">

    <!-- ══ HEADER UTAMA PORTAL ══ -->
    <div class="text-center mb-12">
        <span class="inline-block bg-orange-500/15 text-orange-400 text-xs font-bold uppercase tracking-widest px-4 py-1.5 rounded-full border border-orange-500/30 mb-4">
            🏆 SUIC 2026 • 100% FREE REGISTRATION
        </span>
        <h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight">
            International Design Competition<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-amber-400 to-yellow-400">
                SUIC 2026
            </span>
        </h1>
        <p class="text-gray-400 mt-4 text-sm md:text-base max-w-2xl mx-auto leading-relaxed">
            Pilih kategori kompetisi di bawah ini untuk mengisi formulir pendaftaran khusus sesuai dengan bidang inovasi dan Terms of Reference (TOR) Anda.
        </p>
        <div class="w-20 h-1 bg-orange-500 mx-auto mt-6 rounded-full"></div>
    </div>

    <?php if ($status === 'success'): ?>
        <?php include __DIR__ . '/suic-success-card.php'; ?>
    <?php endif; ?>

    <!-- ══ RESOURCE LINKS (TOR & SUBMISSION) ══ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-12">
        <!-- TOR / Buku Panduan -->
        <a href="#" target="_blank" rel="noopener noreferrer"
           class="group flex items-center gap-4 bg-gray-800 border border-gray-700 hover:border-orange-500 rounded-2xl px-5 py-4 transition-all duration-300 hover:shadow-lg hover:shadow-orange-500/10">
            <div class="w-12 h-12 rounded-xl bg-orange-500/15 flex items-center justify-center flex-shrink-0 group-hover:bg-orange-500/25 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-sm">Buku Panduan / TOR Resmi</p>
                <p class="text-gray-400 text-xs mt-0.5">Ketentuan lengkap 5 kategori kompetisi SUIC 2026</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 group-hover:text-orange-400 ml-auto transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>

        <!-- Penyerahan Design -->
        <a href="#" target="_blank" rel="noopener noreferrer"
           class="group flex items-center gap-4 bg-gray-800 border border-gray-700 hover:border-blue-500 rounded-2xl px-5 py-4 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10">
            <div class="w-12 h-12 rounded-xl bg-blue-500/15 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-500/25 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-sm">Portal Penyerahan Karya</p>
                <p class="text-gray-400 text-xs mt-0.5">Link unggah berkas karya / proposal akhir</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 group-hover:text-blue-400 ml-auto transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
    </div>

    <!-- ══ DAFTAR 5 KATEGORI KOMPETISI (DEDICATED CARDS & LINKS) ══ -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg md:text-xl font-extrabold text-white flex items-center gap-2">
                <span>🎯</span> Pilih Kategori Kompetisi Anda:
            </h2>
            <span class="text-xs text-gray-400">Tersedia 5 Formulir Khusus</span>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <?php foreach ($competitions as $c): ?>
            <div class="bg-gray-800/80 border <?= $c['border'] ?> rounded-2xl p-6 md:p-7 transition-all duration-300 hover:shadow-xl <?= $c['bg_glow'] ?> flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-start gap-4 md:max-w-2xl">
                    <div class="w-14 h-14 <?= $c['accent_bg'] ?> rounded-2xl flex items-center justify-center text-3xl flex-shrink-0 shadow-md">
                        <?= $c['icon'] ?>
                    </div>
                    <div>
                        <span class="inline-block <?= $c['accent_bg'] ?> <?= $c['accent_text'] ?> text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full mb-1.5">
                            <?= $c['badge'] ?>
                        </span>
                        <h3 class="text-xl font-bold text-white leading-snug">
                            <?= htmlspecialchars($c['title']) ?>
                        </h3>
                        <p class="text-gray-400 text-xs font-medium mt-0.5">
                            <?= htmlspecialchars($c['sub']) ?>
                        </p>
                        <p class="text-gray-300 text-xs md:text-sm mt-2.5 leading-relaxed">
                            <?= htmlspecialchars($c['desc']) ?>
                        </p>

                        <!-- Highlights -->
                        <div class="flex flex-wrap gap-2 mt-3.5">
                            <?php foreach ($c['features'] as $f): ?>
                            <span class="inline-flex items-center gap-1 text-[11px] bg-gray-900 text-gray-300 px-2.5 py-1 rounded-lg border border-gray-700/60">
                                <span class="<?= $c['accent_text'] ?>">✓</span> <?= htmlspecialchars($f) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Tombol Buka Form Spesifik -->
                <div class="flex flex-col sm:flex-row md:flex-col gap-2 flex-shrink-0">
                    <a href="index.php?page=<?= $c['page'] ?>"
                       class="px-6 py-3 rounded-xl font-bold text-sm text-center transition-all <?= $c['btn_class'] ?> shadow-lg flex items-center justify-center gap-2">
                        Buka Formulir <?= $c['icon'] ?>
                    </a>
                    <a href="index.php?page=suic&comp=<?= $c['slug'] ?>"
                       class="px-4 py-2 rounded-xl text-xs text-center font-medium text-gray-400 hover:text-white bg-gray-900/60 hover:bg-gray-900 border border-gray-700 transition-colors">
                        Link Alternatif (?comp=<?= $c['slug'] ?>)
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Informasi Tambahan -->
    <div class="mt-12 bg-gray-950/60 border border-gray-800 rounded-2xl p-6 text-center text-xs text-gray-500">
        <p class="text-gray-400 font-semibold mb-1">Ketentuan Pendaftaran:</p>
        <p>Setiap peserta hanya dapat mendaftar pada satu kategori atau mendaftar kategori lain dengan karya desain yang berbeda. Semua berkas diverifikasi oleh panitia SUIC 2026.</p>
    </div>

</div>
</div>
