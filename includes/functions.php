<?php
require_once __DIR__ . '/db.php';

/**
 * Load all key/value settings once into a static cache.
 */
function all_settings(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    $rows = db()->query('SELECT skey, svalue FROM settings')->fetchAll();
    foreach ($rows as $r) {
        $cache[$r['skey']] = $r['svalue'];
    }
    return $cache;
}

/**
 * Get a single setting value with an optional fallback.
 */
function setting(string $key, string $default = ''): string
{
    $s = all_settings();
    return isset($s[$key]) && $s[$key] !== null ? $s[$key] : $default;
}

/**
 * Fetch active blocks of a given type, ordered.
 */
function blocks(string $type): array
{
    $stmt = db()->prepare(
        'SELECT * FROM blocks WHERE block_type = ? AND active = 1 ORDER BY sort ASC, id ASC'
    );
    $stmt->execute([$type]);
    return $stmt->fetchAll();
}

/**
 * HTML-escape helper.
 */
function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/**
 * Build a URL that respects BASE_URL. Absolute URLs and anchors pass through.
 */
function url(string $path): string
{
    if ($path === '' ) return '#';
    if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, '#') || str_starts_with($path, 'mailto:') || str_starts_with($path, 'tel:')) {
        return $path;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Convert a stored asset path to a browser URL.
 */
function asset(string $path): string
{
    if ($path === '') return '';
    if (preg_match('#^(https?:)?//#i', $path)) return $path;
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
