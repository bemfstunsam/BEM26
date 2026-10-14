-- ============================================================
--  Database Setup: BEM FST UNSAM
--  Jalankan file ini sekali melalui phpMyAdmin atau MySQL CLI
-- ============================================================

-- Buat database
CREATE DATABASE IF NOT EXISTS bem_fst_unsam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bem_fst_unsam;

-- ============================================================
--  Tabel: berita
-- ============================================================
CREATE TABLE IF NOT EXISTS berita (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    judul       VARCHAR(300)    NOT NULL,
    deskripsi   TEXT            NOT NULL,
    kategori    VARCHAR(100)    NOT NULL DEFAULT 'Umum',
    tag         VARCHAR(100)    NOT NULL DEFAULT '',
    tanggal     DATE            NOT NULL,
    status      ENUM('aktif','draft') NOT NULL DEFAULT 'aktif',
    dibuat_pada TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    diupdate_pada TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  Tabel: berita_gambar  (relasi 1-banyak dengan berita)
-- ============================================================
CREATE TABLE IF NOT EXISTS berita_gambar (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    berita_id   INT             NOT NULL,
    nama_file   VARCHAR(300)    NOT NULL,
    urutan      INT             NOT NULL DEFAULT 0,
    FOREIGN KEY (berita_id) REFERENCES berita(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  Tabel: admin  (untuk panel admin)
-- ============================================================
CREATE TABLE IF NOT EXISTS admin (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(100)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,
    nama        VARCHAR(200)    NOT NULL,
    dibuat_pada TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  Data Awal: admin (ubah password segera setelah setup via admin panel)
-- ============================================================
INSERT IGNORE INTO admin (username, password, nama) VALUES 
('admin', '$2y$10$zSanRJik8CsZZgTyT6AQ4.MpDpwqzzZ/ntxP7O.dGeVekMk5hAa6W', 'Administrator BEM FST');

-- ============================================================
--  Data Awal: berita
-- ============================================================
INSERT INTO berita (id, judul, deskripsi, kategori, tag, tanggal, status) VALUES
(1,
 'Festival Sains dan Teknologi Universitas Samudra Berlangsung Meriah',
 'Langsa – BEM FST UNSAM menyelenggarakan Festival Sains dan Teknologi yang berlangsung meriah dengan partisipasi aktif mahasiswa, dosen, serta pelajar dari berbagai sekolah di Aceh. Kegiatan ini menjadi wadah kolaborasi, inovasi, dan apresiasi terhadap perkembangan ilmu pengetahuan dan teknologi di lingkungan kampus. Festival menghadirkan beragam agenda menarik, mulai dari pameran proyek penelitian mahasiswa, lomba inovasi teknologi, seminar ilmiah, hingga workshop praktis di bidang informatika, teknik, dan sains terapan. Setiap stan menampilkan karya kreatif yang mencerminkan kemampuan mahasiswa dalam menjawab tantangan teknologi masa kini.',
 'Event', 'Inovasi', '2025-09-15', 'aktif'),

(2,
 'Malam Keakraban (MAKRAB) FST UNSAM',
 'Malam Keakraban Mahasiswa Fakultas Sains dan Teknologi berlangsung dengan penuh semangat dan kebersamaan. Kegiatan ini menjadi wadah untuk mempererat tali silaturahmi antar mahasiswa lintas angkatan melalui berbagai rangkaian acara seperti perkenalan, penampilan seni, dan sesi keakraban. Suasana hangat dan penuh kekeluargaan terasa sepanjang kegiatan, mencerminkan solidaritas dan semangat kolaborasi mahasiswa Fakultas Sains dan Teknologi Universitas Samudra.',
 'Event', 'Kebersamaan', '2025-08-30', 'aktif'),

(3,
 'Donasi Korban Kebakaran di Langsa Kota',
 'BEM FST Universitas Samudra melaksanakan aksi kemanusiaan melalui penyaluran bantuan kepada masyarakat yang tertimpa musibah kebakaran di Jalan Panglima Polem, Gampong Jawa, belakang Lapas, Langsa Kota. Aksi ini merupakan bentuk kepedulian nyata mahasiswa FST dalam meringankan beban sesama yang kehilangan tempat tinggal dan harta benda. Bantuan yang disalurkan berupa bahan pokok, pakaian layak pakai, serta dukungan moril kepada para korban.',
 'Donasi', 'Sosial', '2025-06-08', 'aktif'),

(4,
 'OPEN RECRUITMENT SANGGAR SENI FST',
 'Punya bakat seni atau ingin mengasah kreativitas bersama teman-teman yang solid? Saatnya bergabung dengan Sanggar Seni Fakultas Sains dan Teknologi Universitas Samudra! Kami membuka kesempatan bagi seluruh mahasiswa FST untuk menunjukkan potensi terbaik dan menjadi bagian dari keluarga besar seni FST. Jangan ragu untuk mengekspresikan dirimu di sini. Pendaftaran dapat dilakukan melalui tautan: forms.gle/heAeUPZTVirUF44PA. Untuk informasi lebih lanjut, hubungi Contact Person: Mandrius (0822-7572-0470), Fachri (0895-3653-25883), atau Alya (0838-5591-807). Let\'s join now!!',
 'Rekrutmen', 'Seni', '2025-10-20', 'aktif');

-- ============================================================
--  Data Awal: berita_gambar
-- ============================================================
INSERT INTO berita_gambar (berita_id, nama_file, urutan) VALUES
-- FST (id=1)
(1, 'fberita/fst3.jpg', 1),
(1, 'fberita/fst1.jpg', 2),
(1, 'fberita/fst2.jpg', 3),
(1, 'fberita/fst4.jpg', 4),
(1, 'fberita/fst5.jpg', 5),
(1, 'fberita/fst6.jpg', 6),
(1, 'fberita/fst7.jpg', 7),
-- MAKRAB (id=2)
(2, 'fberita/makrab2.jpg', 1),
(2, 'fberita/makrab1.jpg', 2),
(2, 'fberita/makrab.jpg',  3),
(2, 'fberita/makrab3.jpg', 4),
-- Donasi (id=3)
(3, 'fberita/donasi.jpg',  1),
(3, 'fberita/donasi1.jpg', 2),
(3, 'fberita/donasi2.jpg', 3),
(3, 'fberita/donasi3.jpg', 4),
(3, 'fberita/donasi4.jpg', 5),
(3, 'fberita/donasi6.jpg', 6),
-- Sanggar (id=4)
(4, 'fberita/sanggar.jpg', 1);
