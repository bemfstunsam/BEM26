<?php
/**
 * config/db.php -- Database connection for BEM FST UNSAM
 *
 * Credentials are read from environment variables loaded by config/env.php.
 * Hardcoded values are NEVER used for credentials; only safe structural
 * defaults (host, port, charset) fall back to conventional defaults.
 */

require_once __DIR__ . '/env.php';

// ---- Helper: read a required env var, fail safely if missing ----
function _db_env(string $key, ?string $default = null): string {
    $val = getenv($key);
    if ($val !== false && trim($val) !== '') {
        return trim($val);
    }
    if ($default !== null) {
        return $default;
    }
    // Required variable is missing -- fail with a generic message (no stack trace in output)
    if (!headers_sent()) {
        http_response_code(500);
    }
    error_log("BEM FST UNSAM: Required environment variable \"{$key}\" is not set.");
    exit('Konfigurasi aplikasi belum selesai. Hubungi administrator.');
}

// ---- Configuration --------------------------------------------------
define('DB_HOST', _db_env('DB_HOST', 'localhost'));
define('DB_PORT', (int)_db_env('DB_PORT', '3306'));
define('DB_USER', _db_env('DB_USER'));          // Required -- no default
define('DB_PASS', _db_env('DB_PASSWORD', '')); // Empty string is valid (dev only)
define('DB_NAME', _db_env('DB_NAME'));          // Required -- no default

function getDBConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        error_log('BEM FST UNSAM: Database connection failed: ' . $conn->connect_error);
        if (!headers_sent()) {
            http_response_code(500);
        }
        exit('Layanan sementara tidak tersedia. Silakan coba beberapa saat lagi.');
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
