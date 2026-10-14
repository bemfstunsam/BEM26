-- ============================================================
--  Migrasi: Tambah kolom kategori_idc pada suic_pendaftaran
--  International Design Competition (IDC) — SUIC 2026
--
--  CARA MENJALANKAN:
--    Opsi A (phpMyAdmin): Buka tab SQL, paste isi file ini, klik Go.
--    Opsi B (MySQL CLI) : mysql -u root bem_fst_unsam < migrate_add_idc_category.sql
--
--  CATATAN KEAMANAN DATA:
--    - Kolom ditambahkan dengan NULL DEFAULT agar data pendaftaran
--      lama yang belum memiliki kategori tidak rusak.
--    - Data lama TIDAK diberi kategori otomatis karena kategori
--      peserta lama tidak dapat diketahui tanpa konfirmasi penyelenggara.
--    - Pendaftaran baru WAJIB mengisi kategori (divalidasi di backend).
-- ============================================================

USE bem_fst_unsam;

-- Tambahkan kolom kategori_idc jika belum ada
-- Menggunakan ENUM untuk memastikan hanya nilai yang diizinkan tersimpan di level database
ALTER TABLE suic_pendaftaran
    ADD COLUMN IF NOT EXISTS kategori_idc ENUM(
        'Agricultural Drone Design',
        'Plantation Product Processing Equipment',
        'Fishery Product Processing Equipment',
        'Scientific Tools Design',
        'Product Design'
    ) NULL DEFAULT NULL
    AFTER email;

-- Verifikasi: tampilkan struktur kolom setelah migrasi
-- (Jalankan query ini secara terpisah untuk memverifikasi)
-- DESCRIBE suic_pendaftaran;

-- ============================================================
--  Jika ALTER TABLE IF NOT EXISTS tidak didukung versi MySQL,
--  gunakan prosedur berikut sebagai alternatif:
-- ============================================================
-- DELIMITER $$
-- CREATE PROCEDURE add_idc_column_if_missing()
-- BEGIN
--     IF NOT EXISTS (
--         SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
--         WHERE TABLE_SCHEMA = 'bem_fst_unsam'
--           AND TABLE_NAME   = 'suic_pendaftaran'
--           AND COLUMN_NAME  = 'kategori_idc'
--     ) THEN
--         ALTER TABLE suic_pendaftaran
--             ADD COLUMN kategori_idc ENUM(
--                 'Agricultural Drone Design',
--                 'Plantation Product Processing Equipment',
--                 'Fishery Product Processing Equipment',
--                 'Scientific Tools Design',
--                 'Product Design'
--             ) NULL DEFAULT NULL AFTER email;
--     END IF;
-- END$$
-- DELIMITER ;
-- CALL add_idc_column_if_missing();
-- DROP PROCEDURE IF EXISTS add_idc_column_if_missing;
