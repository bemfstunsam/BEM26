-- ============================================================
-- Migration: Add specific competition fields to suic_pendaftaran
-- Mendukung kustomisasi formulir spesifik untuk 5 kategori TOR IDC
-- ============================================================
USE bem_fst_unsam;

ALTER TABLE suic_pendaftaran
    ADD COLUMN IF NOT EXISTS judul_karya VARCHAR(300) NULL AFTER kategori_idc,
    ADD COLUMN IF NOT EXISTS deskripsi_karya TEXT NULL AFTER judul_karya,
    ADD COLUMN IF NOT EXISTS data_spesifik LONGTEXT NULL AFTER deskripsi_karya,
    ADD COLUMN IF NOT EXISTS file_proposal VARCHAR(255) NULL AFTER file_bukti_follow;
