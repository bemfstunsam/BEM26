<?php
require_once __DIR__ . '/../config/db.php';

$conn = getDBConnection();

$sql = "
    SELECT 
        b.id, b.judul, b.deskripsi, b.kategori, b.tag,
        DATE_FORMAT(b.tanggal, '%d %M %Y') AS tanggal_format
    FROM berita b
    WHERE b.status = 'aktif'
    ORDER BY b.tanggal DESC
";

$result = $conn->query($sql);
$news_data = [];

$bulan_en = ['January','February','March','April','May','June',
             'July','August','September','October','November','December'];
$bulan_id = ['Januari','Februari','Maret','April','Mei','Juni',
             'Juli','Agustus','September','Oktober','November','Desember'];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $stmt = $conn->prepare("SELECT nama_file FROM berita_gambar WHERE berita_id = ? ORDER BY urutan ASC");
        $stmt->bind_param('i', $row['id']);
        $stmt->execute();
        $img_result = $stmt->get_result();
        $images = [];
        while ($img = $img_result->fetch_assoc()) {
            $images[] = $img['nama_file'];
        }
        $stmt->close();

        $news_data[] = [
            'id'          => $row['id'],
            'date'        => str_replace($bulan_en, $bulan_id, $row['tanggal_format']),
            'title'       => $row['judul'],
            'description' => $row['deskripsi'],
            'images'      => $images,
            'category'    => $row['kategori'],
            'tag'         => $row['tag'],
        ];
    }
}
$conn->close();

// Kategori unik untuk filter
$kategori_list = array_unique(array_column($news_data, 'category'));
?>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style:none; scrollbar-width:none; scroll-behavior:smooth; }

    /* Card hover lift */
    .news-card { transition: transform .3s ease, box-shadow .3s ease; }
    .news-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(249,115,22,.15); }

    /* Modal */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 1000;
        background: rgba(0,0,0,.85); backdrop-filter: blur(6px);
        display: flex; align-items: center; justify-content: center; padding: 1rem;
        opacity: 0; pointer-events: none; transition: opacity .3s;
    }
    .modal-overlay.open { opacity: 1; pointer-events: all; }
    .modal-box {
        background: #1F2937; border-radius: 1.25rem; border: 1px solid #374151;
        max-width: 900px; width: 100%; max-height: 90vh; overflow-y: auto;
        transform: translateY(30px); transition: transform .3s;
        scrollbar-width: thin; scrollbar-color: #F97316 #1F2937;
    }
    .modal-overlay.open .modal-box { transform: translateY(0); }

    /* Filter active */
    .filter-btn.active { background:#F97316; color:#fff; border-color:#F97316; }

    /* Hero image fade dots */
    .hero-dot { width:8px; height:8px; border-radius:50%; background:rgba(255,255,255,.35); transition:background .3s; cursor:pointer; }
    .hero-dot.active { background:#F97316; }

    /* Category color map */
    .cat-event    { background:rgba(249,115,22,.12); color:#F97316; border-color:rgba(249,115,22,.25); }
    .cat-donasi   { background:rgba(59,130,246,.12); color:#60A5FA; border-color:rgba(59,130,246,.25); }
    .cat-rekrutmen{ background:rgba(16,185,129,.12); color:#34D399; border-color:rgba(16,185,129,.25); }
    .cat-default  { background:rgba(156,163,175,.12); color:#9CA3AF; border-color:rgba(156,163,175,.25); }
</style>

<div class="bg-gray-900 min-h-screen">

    <!-- ══════════════════════════════════════════════
         HEADER SECTION
    ══════════════════════════════════════════════ -->
    <div class="pt-16 pb-10 text-center px-4">
        <p class="text-orange-bem text-sm font-bold uppercase tracking-widest mb-3">BEM FST UNSAM</p>
        <h2 class="text-4xl md:text-6xl font-extrabold text-white tracking-tight">Berita <span class="text-orange-bem">&</span> Informasi</h2>
        <p class="text-gray-400 mt-4 text-lg max-w-xl mx-auto">Ikuti perkembangan terbaru kegiatan dan program BEM FST Universitas Samudra.</p>
        <div class="w-20 h-1 bg-orange-bem mx-auto mt-6 rounded-full"></div>
    </div>

    <?php if (empty($news_data)): ?>
    <!-- KOSONG -->
    <div class="text-center py-32 px-4">
        <div class="w-24 h-24 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
            </svg>
        </div>
        <p class="text-gray-500 text-xl">Belum ada berita yang tersedia.</p>
    </div>

    <?php else: ?>

    <div class="container mx-auto px-4 md:px-6 pb-24">

        <!-- ══════════════════════════════════════════════
             HERO — Berita Terbaru (index 0)
        ══════════════════════════════════════════════ -->
        <?php $hero = $news_data[0]; $hid = 'hero'; ?>
        <div class="max-w-6xl mx-auto mb-16">
            <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-gray-700 group cursor-pointer"
                 onclick="openModal(<?= $hero['id'] ?>)">

                <!-- Gambar Hero dengan slider dots -->
                <div id="<?= $hid ?>-slider" class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide h-72 md:h-[520px]">
                    <?php if (!empty($hero['images'])): foreach($hero['images'] as $img): ?>
                        <div class="w-full h-full flex-shrink-0 snap-center">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($hero['title']) ?>"
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </div>
                    <?php endforeach; else: ?>
                        <div class="w-full h-full flex-shrink-0 bg-gray-800 flex items-center justify-center">
                            <span class="text-gray-500">Tidak ada gambar</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/60 to-transparent pointer-events-none"></div>

                <!-- Badge terbaru -->
                <div class="absolute top-5 left-5 flex gap-2">
                    <span class="bg-orange-bem text-white text-xs font-black uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg">
                        🔥 Terbaru
                    </span>
                    <span class="text-xs font-black uppercase tracking-widest px-3 py-1.5 rounded-full border
                        <?= match(strtolower($hero['category'])) {
                            'event'     => 'cat-event',
                            'donasi'    => 'cat-donasi',
                            'rekrutmen' => 'cat-rekrutmen',
                            default     => 'cat-default'
                        } ?>">
                        <?= htmlspecialchars($hero['category']) ?>
                    </span>
                </div>

                <!-- Dots navigasi gambar -->
                <?php if (count($hero['images']) > 1): ?>
                <div class="absolute top-5 right-5 flex gap-1.5">
                    <?php foreach($hero['images'] as $di => $img): ?>
                    <span class="hero-dot <?= $di===0?'active':'' ?>"></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Teks di bawah -->
                <div class="absolute bottom-0 left-0 right-0 p-6 md:p-10">
                    <p class="text-gray-400 text-sm mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <?= htmlspecialchars($hero['date']) ?>
                    </p>
                    <h3 class="text-2xl md:text-4xl font-extrabold text-white leading-tight mb-3 group-hover:text-orange-bem transition-colors">
                        <?= htmlspecialchars($hero['title']) ?>
                    </h3>
                    <p class="text-gray-300 text-base md:text-lg line-clamp-2 max-w-3xl">
                        <?= htmlspecialchars(mb_substr($hero['description'], 0, 160)) ?>...
                    </p>
                    <div class="mt-5 flex items-center gap-3">
                        <span class="inline-flex items-center gap-2 bg-orange-bem hover:bg-orange-600 text-white font-bold px-5 py-2.5 rounded-full text-sm transition-colors shadow-lg">
                            Baca Selengkapnya
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </span>
                        <span class="text-gray-400 text-sm">#<?= htmlspecialchars($hero['tag']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════
             FILTER KATEGORI
        ══════════════════════════════════════════════ -->
        <?php if (count($news_data) > 1): ?>
        <div class="max-w-6xl mx-auto mb-10 flex flex-wrap gap-3 items-center">
            <span class="text-gray-400 text-sm font-semibold mr-2">Filter:</span>
            <button onclick="filterNews('semua')"
                    class="filter-btn active text-sm font-bold px-5 py-2 rounded-full border border-gray-600 text-gray-300 hover:border-orange-bem transition-all"
                    data-filter="semua">
                Semua
            </button>
            <?php foreach($kategori_list as $kat): ?>
            <button onclick="filterNews('<?= strtolower(htmlspecialchars($kat)) ?>')"
                    class="filter-btn text-sm font-bold px-5 py-2 rounded-full border border-gray-600 text-gray-300 hover:border-orange-bem transition-all"
                    data-filter="<?= strtolower(htmlspecialchars($kat)) ?>">
                <?= htmlspecialchars($kat) ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- ══════════════════════════════════════════════
             GRID CARD — Berita Lainnya (index 1+)
        ══════════════════════════════════════════════ -->
        <div id="news-grid" class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach(array_slice($news_data, 1) as $news): ?>
            <?php $thumb = !empty($news['images']) ? $news['images'][0] : ''; ?>
            <div class="news-card bg-gray-800 rounded-2xl overflow-hidden border border-gray-700 hover:border-orange-bem/50 cursor-pointer flex flex-col"
                 data-category="<?= strtolower(htmlspecialchars($news['category'])) ?>"
                 onclick="openModal(<?= $news['id'] ?>)">

                <!-- Thumbnail -->
                <div class="relative h-52 overflow-hidden bg-gray-700">
                    <?php if ($thumb): ?>
                    <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($news['title']) ?>"
                         class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <?php endif; ?>

                    <!-- Badge kategori -->
                    <div class="absolute top-3 left-3">
                        <span class="text-xs font-black uppercase tracking-wider px-3 py-1 rounded-full border
                            <?= match(strtolower($news['category'])) {
                                'event'     => 'cat-event',
                                'donasi'    => 'cat-donasi',
                                'rekrutmen' => 'cat-rekrutmen',
                                default     => 'cat-default'
                            } ?>">
                            <?= htmlspecialchars($news['category']) ?>
                        </span>
                    </div>

                    <!-- Jumlah foto -->
                    <?php if (count($news['images']) > 1): ?>
                    <div class="absolute bottom-3 right-3 bg-black/60 backdrop-blur-sm text-white text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <?= count($news['images']) ?> foto
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Konten Card -->
                <div class="p-6 flex flex-col flex-1">
                    <p class="text-xs text-gray-500 mb-2 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <?= htmlspecialchars($news['date']) ?>
                    </p>
                    <h4 class="text-white font-bold text-lg leading-snug mb-3 line-clamp-2 group-hover:text-orange-bem">
                        <?= htmlspecialchars($news['title']) ?>
                    </h4>
                    <p class="text-gray-400 text-sm leading-relaxed line-clamp-3 flex-1">
                        <?= htmlspecialchars($news['description']) ?>
                    </p>
                    <div class="mt-4 pt-4 border-t border-gray-700/50 flex items-center justify-between">
                        <span class="text-xs text-gray-500 bg-gray-700/60 px-3 py-1 rounded-full">#<?= htmlspecialchars($news['tag']) ?></span>
                        <span class="text-orange-bem text-sm font-bold flex items-center gap-1">
                            Baca
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div><!-- /container -->
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL DETAIL BERITA
══════════════════════════════════════════════════════════════ -->
<div id="news-modal" class="modal-overlay" onclick="closeModalOutside(event)">
    <div class="modal-box" id="modal-box">
        <!-- Loading spinner sementara data dimuat -->
        <div id="modal-loading" class="flex items-center justify-center py-24">
            <div class="w-10 h-10 border-4 border-orange-bem border-t-transparent rounded-full animate-spin"></div>
        </div>
        <!-- Konten aktual diisi JS -->
        <div id="modal-content" class="hidden"></div>
    </div>
</div>

<!-- Data berita untuk JS modal -->
<script>
const newsData = <?= json_encode($news_data, JSON_UNESCAPED_UNICODE) ?>;

// ── MODAL ──────────────────────────────────────────────────────
function openModal(id) {
    const news = newsData.find(n => n.id == id);
    if (!news) return;

    const modal   = document.getElementById('news-modal');
    const loading = document.getElementById('modal-loading');
    const content = document.getElementById('modal-content');

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    loading.classList.remove('hidden');
    content.classList.add('hidden');

    // Gambar gallery HTML
    let galleryHTML = '';
    if (news.images && news.images.length > 0) {
        const slides = news.images.map((img, i) => `
            <div class="w-full h-full flex-shrink-0 snap-center">
                <img src="${img}" alt="${news.title}" class="w-full h-full object-cover">
            </div>`).join('');

        const hasMultiple = news.images.length > 1;

        const dots = hasMultiple
            ? `<div class="flex justify-center gap-2 mt-3">
                ${news.images.map((_,i)=>`<span class="modal-dot w-2 h-2 rounded-full cursor-pointer transition-all ${i===0?'bg-orange-500 w-4':'bg-gray-600'}" data-idx="${i}"></span>`).join('')}
               </div>`
            : '';

        // Tombol panah kiri & kanan + counter foto
        const navButtons = hasMultiple ? `
            <button id="btn-prev" onclick="slideModal(-1)"
                class="absolute left-3 top-1/2 -translate-y-1/2 z-10
                       w-10 h-10 rounded-full bg-black/60 hover:bg-orange-500
                       text-white flex items-center justify-center
                       transition-all duration-200 backdrop-blur-sm border border-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button id="btn-next" onclick="slideModal(1)"
                class="absolute right-3 top-1/2 -translate-y-1/2 z-10
                       w-10 h-10 rounded-full bg-black/60 hover:bg-orange-500
                       text-white flex items-center justify-center
                       transition-all duration-200 backdrop-blur-sm border border-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div id="slide-counter"
                class="absolute top-3 right-3 bg-black/60 backdrop-blur-sm
                       text-white text-xs font-semibold px-3 py-1 rounded-full border border-white/10">
                1 / ${news.images.length}
            </div>` : '';

        galleryHTML = `
            <div class="relative">
                <div id="modal-slider" class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide h-64 md:h-96">
                    ${slides}
                </div>
                ${navButtons}
                ${dots}
            </div>`;
    }

    // Warna kategori
    const catClass = {event:'cat-event', donasi:'cat-donasi', rekrutmen:'cat-rekrutmen'}[news.category?.toLowerCase()] || 'cat-default';

    content.innerHTML = `
        <div>
            ${galleryHTML}
            <div class="p-6 md:p-8">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="text-xs font-black uppercase tracking-wider px-3 py-1 rounded-full border ${catClass}">${news.category}</span>
                    <span class="text-xs text-gray-500 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        ${news.date}
                    </span>
                    <span class="text-xs text-gray-500 bg-gray-700/50 px-3 py-1 rounded-full ml-auto">#${news.tag}</span>
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-white leading-tight mb-5">${news.title}</h2>
                <div class="w-12 h-1 bg-orange-500 rounded mb-5"></div>
                <p class="text-gray-300 leading-relaxed text-base whitespace-pre-line">${news.description}</p>
            </div>
            <div class="px-6 pb-6">
                <button onclick="closeModal()"
                    class="w-full bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 rounded-xl transition-colors">
                    Tutup
                </button>
            </div>
        </div>`;

    setTimeout(() => {
        loading.classList.add('hidden');
        content.classList.remove('hidden');

        // Dots & counter sinkron dengan slider
        const slider = document.getElementById('modal-slider');
        if (slider) {
            const dots    = content.querySelectorAll('.modal-dot');
            const counter = document.getElementById('slide-counter');
            const total   = news.images.length;

            // Fungsi update tampilan dots & counter
            function updateNav(idx) {
                dots.forEach((d, i) => {
                    d.classList.toggle('bg-orange-500', i === idx);
                    d.classList.toggle('w-4', i === idx);
                    d.classList.toggle('bg-gray-600', i !== idx);
                    d.classList.toggle('w-2', i !== idx);
                });
                if (counter) counter.textContent = `${idx + 1} / ${total}`;
            }

            // Klik dots → geser slider
            dots.forEach(dot => {
                dot.addEventListener('click', () => {
                    const idx = parseInt(dot.dataset.idx);
                    slider.scrollTo({ left: slider.clientWidth * idx, behavior: 'smooth' });
                });
            });

            // Scroll → update dots & counter
            slider.addEventListener('scroll', () => {
                const idx = Math.round(slider.scrollLeft / slider.clientWidth);
                updateNav(idx);
            });
        }
    }, 200);
}

function closeModal() {
    document.getElementById('news-modal').classList.remove('open');
    document.body.style.overflow = '';
}
function closeModalOutside(e) {
    if (e.target.id === 'news-modal') closeModal();
}

// Tombol panah geser foto
function slideModal(dir) {
    const slider = document.getElementById('modal-slider');
    if (!slider) return;
    const idx = Math.round(slider.scrollLeft / slider.clientWidth);
    const total = slider.children.length;
    const next = Math.max(0, Math.min(total - 1, idx + dir));
    slider.scrollTo({ left: slider.clientWidth * next, behavior: 'smooth' });
}

// Keyboard: panah kiri/kanan geser foto, Esc tutup modal
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
    if (e.key === 'ArrowLeft')  slideModal(-1);
    if (e.key === 'ArrowRight') slideModal(1);
});

// ── FILTER ────────────────────────────────────────────────────
function filterNews(kat) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.filter === kat);
    });
    document.querySelectorAll('#news-grid .news-card').forEach(card => {
        const match = kat === 'semua' || card.dataset.category === kat;
        card.style.display = match ? '' : 'none';
    });
}
</script>