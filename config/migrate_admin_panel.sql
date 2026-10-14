-- ============================================================
--  Migrasi: Admin Panel SUIC 2026 IDC
--  CARA MENJALANKAN:
--    Opsi A (phpMyAdmin): Buka tab SQL, paste file ini, klik Go.
--    Opsi B (MySQL CLI) : mysql -u root bem_fst_unsam < migrate_admin_panel.sql
--  CATATAN: Migrasi ini dijalankan otomatis oleh skrip implementasi.
-- ============================================================

USE bem_fst_unsam;

-- ── 1. Tambah kolom status pada suic_pendaftaran ─────────────
ALTER TABLE suic_pendaftaran
    ADD COLUMN IF NOT EXISTS status ENUM('pending','approved','rejected')
    NOT NULL DEFAULT 'pending' AFTER kategori_idc;

-- ── 2. Tambah peran dan aktif pada tabel admin ───────────────
ALTER TABLE admin
    ADD COLUMN IF NOT EXISTS peran ENUM('super_admin','admin')
    NOT NULL DEFAULT 'admin' AFTER nama;

ALTER TABLE admin
    ADD COLUMN IF NOT EXISTS aktif TINYINT(1) NOT NULL DEFAULT 1
    AFTER peran;

-- Upgrade admin seed ke super_admin
UPDATE admin SET peran = 'super_admin' WHERE username = 'admin';

-- ── 3. Buat tabel audit log ───────────────────────────────────
CREATE TABLE IF NOT EXISTS suic_audit_log (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT,
    admin_nama  VARCHAR(200) NOT NULL DEFAULT '',
    aksi        VARCHAR(100) NOT NULL,
    entitas     VARCHAR(100) NOT NULL DEFAULT '',
    entitas_id  INT,
    deskripsi   TEXT,
    ip_address  VARCHAR(45),
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_admin   (admin_id),
    INDEX idx_audit_entitas (entitas, entitas_id),
    INDEX idx_audit_waktu   (dibuat_pada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. Index performa untuk query admin panel ─────────────────
CREATE INDEX IF NOT EXISTS idx_suic_status   ON suic_pendaftaran(status);
CREATE INDEX IF NOT EXISTS idx_suic_kategori ON suic_pendaftaran(kategori_idc);
CREATE INDEX IF NOT EXISTS idx_suic_negara   ON suic_pendaftaran(negara);
CREATE INDEX IF NOT EXISTS idx_suic_created  ON suic_pendaftaran(created_at);

-- ── VERIFIKASI (jalankan secara terpisah) ─────────────────────
-- DESCRIBE suic_pendaftaran;
-- DESCRIBE admin;
-- SHOW TABLES;
