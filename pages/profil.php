<div class="py-16 md:py-24 bg-gray-900">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-white">Struktur Kepengurusan</h2>
            <p class="text-gray-400 mt-2">BEM FST UNSAM Periode 2026</p>
            <div class="w-24 h-1 bg-orange-bem mx-auto mt-4 rounded"></div>
        </div>

        <!-- Pengurus Inti -->
        <h3 class="text-2xl font-bold text-center text-orange-bem mb-8">Badan Pengurus Harian</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 justify-items-center mb-16">
            <?php
            $inti = [
                ["nama" => "Syahru Ramadhana",  "jabatan" => "Ketua Umum",       "foto" => "foto/syahru.jpg",  "pos" => "object-top"],
                ["nama" => "M. Tegar Al Fathan", "jabatan" => "Wakil Ketua Umum", "foto" => "foto/tegar.jpg",   "pos" => "object-top"],
                ["nama" => "Adriansyah Pratama", "jabatan" => "Sekretaris Umum",  "foto" => "https://placehold.co/400x400/1F2937/F97316?text=AP", "pos" => "object-center"],
                ["nama" => "Syafira Ramadhani",  "jabatan" => "Bendahara Umum",   "foto" => "foto/fira.jpg",    "pos" => "object-center"],
    
            ];
            foreach ($inti as $p) {
                $objPos = $p['pos'] ?? 'object-top';
                echo '<div class="bg-gray-800 rounded-lg shadow-lg text-center p-6 w-64 transform hover:-translate-y-2 transition-transform duration-300">
                        <img class="w-32 h-32 rounded-full mx-auto mb-4 border-4 border-gray-700 object-cover ' . $objPos . '" src="' . $p['foto'] . '" alt="Foto ' . $p['nama'] . '">
                        <h4 class="text-lg font-bold text-white">' . $p['nama'] . '</h4>
                        <p class="text-orange-bem">' . $p['jabatan'] . '</p>
                      </div>';
            }
            ?>
        </div>

        <?php
        $divisi = [
            "Bidang Humas" => [
                ["nama" => "Maulidan Nanda",           "jabatan" => "Ketua Bidang", "foto" => "foto/nanda.jpg", "pos" => "object-center"],
                ["nama" => "Ramada Alya Fitri",         "jabatan" => "Anggota",      "foto" => "foto/RamadaAlyaFitri.jpg", "pos" => "object-[50%_30%]"],
                ["nama" => "Rafi Aldana",               "jabatan" => "Anggota",      "foto" => "foto/rafi.jpg", "pos" => "object-center"],
                ["nama" => "Tiara Rizkina",             "jabatan" => "Anggota",      "foto" => "https://placehold.co/400x400/1F2937/F97316?text=TR"],
                ["nama" => "Nazwa Ananda",              "jabatan" => "Anggota",      "foto" => "https://placehold.co/400x400/1F2937/F97316?text=NA"],
                ["nama" => "Petrus Rambo Siregar",      "jabatan" => "Anggota",      "foto" => "foto/petrus.jpg", "pos" => "object-[50%_30%]"],
                ["nama" => "Virly Gusfahmi",            "jabatan" => "Anggota",      "foto" => "foto/virly.jpg", "pos" => "object-center"],
                ["nama" => "Fransiska Yongtina Marbun", "jabatan" => "Anggota",      "foto" => "foto/siska.jpg", "pos" => "object-center"],
            ],
            "Bidang Riset dan Teknologi" => [
                ["nama" => "Adit Hidayat", "jabatan" => "Ketua Bidang", "foto" => "foto/adit.jpg"],
                ["nama" => "Arya Arfanda", "jabatan" => "Anggota",      "foto" => "foto/arya.jpg"],
                ["nama" => "Edi Susanto",  "jabatan" => "Anggota",      "foto" => "foto/edi.jpg"],
                
            ],
            "Bidang Minat dan Bakat" => [
                ["nama" => "Kristopel Sitorus",   "jabatan" => "Ketua Bidang", "foto" => "foto/kristopel.jpg", "pos" => "object-center"],
                ["nama" => "Ahmad Irgi Syahrezi", "jabatan" => "Anggota",      "foto" => "foto/irgi.jpg", "pos" => "object-[50%_30%]"],
                ["nama" => "Muhammad Fahril",     "jabatan" => "Anggota",      "foto" => "foto/mfahril.jpg", "pos" => "object-center"],
                ["nama" => "Cinta Wiridtanaya",   "jabatan" => "Anggota",      "foto" => "foto/cinta.jpg", "pos" => "object-center"],
                ["nama" => "Melva Dwiyanti",      "jabatan" => "Anggota",      "foto" => "foto/melva.jpg", "pos" => "object-center"],
                ["nama" => "Quwin Noor",          "jabatan" => "Anggota",      "foto" => "foto/quwin.jpg", "pos" => "object-[50%_30%]"],
            ],
            "Bidang Agama" => [
                ["nama" => "Syahas Bullah",                   "jabatan" => "Ketua Bidang", "foto" => "foto/syahas.jpg", "pos" => "object-[50%_30%]"],
                ["nama" => "Indah Naizila",                   "jabatan" => "Anggota",      "foto" => "foto/indah.jpg", "pos" => "object-[50%_30%]"],
                ["nama" => "Maulaya Pasha Candilla",          "jabatan" => "Anggota",      "foto" => "foto/maulaya.jpg", "pos" => "object-center"],
                ["nama" => "Laura Selvina Sri Miranda Aruan", "jabatan" => "Anggota",      "foto" => "foto/laura.jpg", "pos" => "object-center"],
                
            ],
            "Bidang Kominfo" => [
                ["nama" => "M. Ihsan Ramadhani",      "jabatan" => "Ketua Bidang", "foto" => "foto/ihsan.jpg", "pos" => "object-[50%_30%]"],
                ["nama" => "Alya Salsabila",           "jabatan" => "Anggota",      "foto" => "foto/alyas.jpg", "pos" => "object-center"],
                ["nama" => "Nathasya Farani Liandini", "jabatan" => "Anggota",      "foto" => "foto/nathasya.jpg", "pos" => "object-[50%_30%]"],
                ["nama" => "Siti Cahaya Kamila Putri", "jabatan" => "Anggota",      "foto" => "foto/cahaya.jpg", "pos" => "object-[50%_30%]"],
                ["nama" => "Muhammad Fitra Fuadi",     "jabatan" => "Anggota",      "foto" => "https://placehold.co/400x400/1F2937/F97316?text=MF"],
                ["nama" => "Chairu Aisy Alsa",         "jabatan" => "Anggota",      "foto" => "foto/chairu.jpg", "pos" => "object-center"],
            ],
            "Bidang Kesekretariatan" => [
                ["nama" => "Alvani",  "jabatan" => "Ketua Bidang", "foto" => "https://placehold.co/400x400/1F2937/F97316?text=AL"],
                ["nama" => "Al Amin", "jabatan" => "Anggota",      "foto" => "https://placehold.co/400x400/1F2937/F97316?text=AA"],
            ],
        ];

        foreach ($divisi as $nama_divisi => $anggota) {
            echo '<h3 class="text-2xl font-bold text-center text-orange-bem mb-8 mt-16">' . $nama_divisi . '</h3>';
            echo '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 justify-items-center">';
            foreach ($anggota as $p) {
                $objPos = $p['pos'] ?? 'object-center'; // Default di tengah
                echo '<div class="bg-gray-800 rounded-lg shadow-lg text-center p-6 w-56 transform hover:-translate-y-2 transition-transform duration-300">
                        <img class="w-28 h-28 rounded-full mx-auto mb-4 border-4 border-gray-700 object-cover ' . $objPos . '" src="' . $p['foto'] . '" alt="Foto ' . $p['nama'] . '">
                        <h4 class="text-base font-bold text-white leading-tight">' . $p['nama'] . '</h4>
                        <p class="text-orange-bem text-sm mt-1">' . $p['jabatan'] . '</p>
                      </div>';
            }
            echo '</div>';
        }
        ?>

        <!-- Kontak & Alamat -->
        <div class="mt-24">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-white">Kontak Kami</h2>
                <div class="w-24 h-1 bg-orange-bem mx-auto mt-4 rounded"></div>
            </div>
            <div class="max-w-4xl mx-auto bg-gray-800 p-8 rounded-xl shadow-lg text-center">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="text-xl font-bold text-orange-bem mb-4">Media Sosial & Email</h4>
                        <p class="mb-2"><strong class="text-gray-300">Instagram:</strong> <a href="https://www.instagram.com/Bemfstunsam" target="_blank" class="text-gray-400 hover:text-orange-bem">@Bemfstunsam</a></p>
                        <p class="mb-2"><strong class="text-gray-300">TikTok:</strong> <a href="https://www.tiktok.com/@Bemfstunsam" target="_blank" class="text-gray-400 hover:text-orange-bem">@Bemfstunsam</a></p>
                        <p class="mb-2"><strong class="text-gray-300">Email:</strong> <a href="mailto:bemteknikunsam.aceh@gmail.com" class="text-gray-400 hover:text-orange-bem">bemteknikunsam.aceh@gmail.com</a></p>
                        <p class="mb-2"><strong class="text-gray-300">YouTube:</strong> <span class="text-gray-400">BEM FAKULTAS SAINS DAN TEKNOLOGI</span></p>
                        <p class="mb-2"><strong class="text-gray-300">LinkedIn:</strong> <span class="text-gray-400">BEM FST UNSAM</span></p>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-orange-bem mb-4">Lokasi Sekretariat</h4>
                        <p class="text-gray-400 mb-4">Fakultas Sains dan Teknologi, Universitas Samudra, Langsa, Aceh.</p>
                        <a href="https://maps.google.com/?cid=16456346123908801610&entry=gps" target="_blank" class="inline-block bg-orange-bem text-white font-bold py-2 px-6 rounded-full hover:bg-orange-bem-dark transition-all duration-300 shadow-orange">
                            Lihat di Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
