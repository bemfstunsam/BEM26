<?php
/**
 * env.php -- Lightweight .env loader for BEM FST UNSAM (no Composer required)
 *
 * Reads KEY=VALUE pairs from a .env file and populates $_ENV, $_SERVER,
 * and putenv() so that getenv() works uniformly across hosting environments.
 *
 * Usage:
 *   require_once __DIR__ . '/config/env.php';   // call once, at bootstrap
 *
 * Rules:
 *   - Lines beginning with # are comments.
 *   - Empty lines are ignored.
 *   - Quoted values ("value" or 'value') are supported; quotes are stripped.
 *   - Existing environment variables are NOT overwritten (safe for real env).
 *   - If .env does not exist, the loader is silently skipped (production
 *     servers may inject env vars directly via the web server / Docker).
 */

(static function (): void {
    $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

    if (!is_file($envFile) || !is_readable($envFile)) {
        return; // No .env file -- rely on server-provided environment variables
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and blank lines
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        // Must contain '='
        $eqPos = strpos($line, '=');
        if ($eqPos === false) {
            continue;
        }

        $key   = trim(substr($line, 0, $eqPos));
        $value = trim(substr($line, $eqPos + 1));

        // Validate key: alphanumeric + underscore only
        if (!preg_match('/^[A-Z_][A-Z0-9_]*$/i', $key)) {
            continue;
        }

        // Strip surrounding quotes (single or double)
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        // Inline comment removal for unquoted values
        // e.g.  DB_HOST=localhost  # this host
        if (strpos($value, ' #') !== false && $value[0] !== '"' && $value[0] !== "'") {
            $value = trim(explode(' #', $value, 2)[0]);
        }

        // Do NOT overwrite variables already set by the server / Docker / CLI
        if (getenv($key) === false && !isset($_ENV[$key]) && !isset($_SERVER[$key])) {
            putenv("{$key}={$value}");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
})();
