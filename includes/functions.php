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
 * Every page.php file that has a custom SEO slug set (admin > SEO),
 * loaded once per request. Pages with no custom slug are left out, so
 * url() falls back to its default filename-based clean URL for them.
 */
function seo_slug_map(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    try {
        $rows = db()->query("SELECT page_file, seo_slug FROM seo_meta WHERE seo_slug != ''")->fetchAll();
        foreach ($rows as $r) {
            $cache[$r['page_file']] = $r['seo_slug'];
        }
    } catch (Throwable $e) {
        // seo_meta table may not exist yet (migration not run) - fail quietly.
    }
    return $cache;
}

/**
 * The SEO title/keyphrase/description/slug set for one page (admin > SEO),
 * keyed by its filename without ".php" (e.g. "whatson", "index"). Falls
 * back to all-empty values if nothing has been set for that page yet.
 */
function seo_meta_for(string $pageKey): array
{
    static $cache = [];
    if (isset($cache[$pageKey])) {
        return $cache[$pageKey];
    }
    $empty = ['seo_title' => '', 'seo_keyphrase' => '', 'seo_description' => '', 'seo_slug' => ''];
    try {
        $stmt = db()->prepare('SELECT seo_title, seo_keyphrase, seo_description, seo_slug FROM seo_meta WHERE page_key = ? LIMIT 1');
        $stmt->execute([$pageKey]);
        $row = $stmt->fetch();
        $cache[$pageKey] = $row ?: $empty;
    } catch (Throwable $e) {
        $cache[$pageKey] = $empty;
    }
    return $cache[$pageKey];
}

/**
 * Build a URL that respects BASE_URL. Absolute URLs and anchors pass through.
 * Strips a trailing ".php" (and "index.php" -> root) so every link site-wide
 * is a clean URL - matches the .htaccess rewrite that serves page.php for /page.
 * Uses a page's custom SEO slug (admin > SEO) instead, when one is set.
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
        $slugs = seo_slug_map();
        $path = $slugs[$path] ?? substr($path, 0, -4);
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/') . $suffix;
}

/**
 * The current request's scheme + host, e.g. "https://houseofbungalow.com.au"
 * with no trailing slash. Used to build absolute URLs (e.g. canonical tags).
 */
function site_origin(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
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
