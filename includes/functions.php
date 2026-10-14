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
 * Whether a given section should be shown on the live site. Section
 * visibility is stored as a plain setting named "<key>_visible" (admin
 * tab: Section Visibility) — '0' hides it, anything else (including no
 * row at all, i.e. a page built before this feature existed) shows it.
 */
function section_visible(string $key): bool
{
    return setting($key . '_visible', '1') !== '0';
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
 * Escape a value, then turn **word** into <strong>word</strong>.
 * Escaping happens first, so this only ever introduces the <strong>
 * tags added here - safe even though the source text is admin-editable.
 */
function eb(?string $v): string
{
    return preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', e($v));
}

/**
 * Like eb(), but also turns line breaks into real <br> tags - both actual
 * newlines (from pressing Enter in the admin textarea) and a literal "<br>"
 * typed into the text. Escaping happens first, so this is safe even though
 * the source text is admin-editable.
 */
function enl(?string $v): string
{
    $escaped = preg_replace('#&lt;br\s*/?&gt;#i', '<br>', eb($v));
    return nl2br($escaped, false);
}

/**
 * Build a URL that respects BASE_URL. Absolute URLs and anchors pass through.
 * Strips a trailing ".php" (and "index.php" -> root) so every link site-wide
 * is a clean URL - matches the .htaccess rewrite that serves page.php for /page.
 */
function url(string $path): string
{
    if ($path === '' ) return '#';
    if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, '#') || str_starts_with($path, 'mailto:') || str_starts_with($path, 'tel:')) {
        return $path;
    }
    $suffix = '';
    $cut = strcspn($path, '?#');
    if ($cut < strlen($path)) {
        $suffix = substr($path, $cut);
        $path = substr($path, 0, $cut);
    }
    if ($path === 'index.php') {
        $path = '';
    } elseif (str_ends_with($path, '.php')) {
        $path = substr($path, 0, -4);
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/') . $suffix;
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
