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
 * Low-level: POST one JSON:API payload to a Klaviyo endpoint. Returns
 * [success, message, decodedBody] - message is the real error detail
 * on failure (connection error, or Klaviyo's own HTTP status/response
 * body), or a short confirmation on success. Never throws.
 */
function klaviyo_api_request(string $apiKey, string $path, array $payload): array
{
    if ($apiKey === '') {
        return [false, 'No Klaviyo API key is set.', null];
    }
    if (!function_exists('curl_init')) {
        return [false, 'cURL is not available on this server.', null];
    }

    $ch = curl_init('https://a.klaviyo.com' . $path);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Klaviyo-API-Key ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
            'revision: 2024-10-15',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
    ]);
    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return [false, 'Connection error: ' . $curlError, null];
    }
    $decoded = $response !== false && $response !== '' ? json_decode($response, true) : null;
    if ($httpCode >= 200 && $httpCode < 300) {
        return [true, 'Sent successfully (HTTP ' . $httpCode . ').', $decoded];
    }
    $bodyExcerpt = $response !== false && $response !== '' ? substr($response, 0, 400) : '(no response body)';
    return [false, 'Klaviyo responded with HTTP ' . $httpCode . ': ' . $bodyExcerpt, $decoded];
}

/**
 * Sends one event + profile to Klaviyo's Events API. Returns
 * [success, message] in the same shape klaviyo_api_request() does.
 * $uniqueId (optional) makes Klaviyo treat repeat calls with the same
 * value + event name as the same event instead of creating duplicates
 * - pass the database row's own id (e.g. "private-events-42") so a
 * retried request can never double up in Klaviyo. Used by both every
 * live form on the site and the admin "Send Test Event" button.
 */
function klaviyo_send_event(string $apiKey, string $eventName, array $properties, array $profile, string $uniqueId = ''): array
{
    $attributes = [
        'properties' => $properties,
        'metric' => [
            'data' => [
                'type' => 'metric',
                'attributes' => ['name' => $eventName],
            ],
        ],
        'profile' => [
            'data' => [
                'type' => 'profile',
                'attributes' => $profile,
            ],
        ],
        'time' => gmdate('c'),
    ];
    if ($uniqueId !== '') {
        $attributes['unique_id'] = $uniqueId;
    }
    [$ok, $message] = klaviyo_api_request($apiKey, '/api/events/', [
        'data' => ['type' => 'event', 'attributes' => $attributes],
    ]);
    return [$ok, $message];
}

/**
 * Subscribes a profile to a Klaviyo list with marketing consent
 * (admin > Private Events > Klaviyo Integration > List ID). Requires
 * an email address - Klaviyo list subscriptions are email-consent
 * based, so profiles identified only by phone are skipped safely.
 */
function klaviyo_subscribe_profile_to_list(string $apiKey, string $listId, array $profile): array
{
    if (empty($profile['email'])) {
        return [false, 'No email address on this profile - skipped list subscription.'];
    }
    $profileAttrs = ['email' => $profile['email']];
    if (!empty($profile['first_name'])) $profileAttrs['first_name'] = $profile['first_name'];
    if (!empty($profile['last_name']))  $profileAttrs['last_name']  = $profile['last_name'];
    if (!empty($profile['phone_number'])) $profileAttrs['phone_number'] = $profile['phone_number'];
    $profileAttrs['subscriptions'] = [
        'email' => ['marketing' => ['consent' => 'SUBSCRIBED']],
    ];

    [$ok, $message] = klaviyo_api_request($apiKey, '/api/profile-subscription-bulk-create-jobs/', [
        'data' => [
            'type' => 'profile-subscription-bulk-create-job',
            'attributes' => [
                'profiles' => [
                    'data' => [[
                        'type' => 'profile',
                        'attributes' => $profileAttrs,
                    ]],
                ],
                'historical_import' => false,
            ],
            'relationships' => [
                'list' => [
                    'data' => ['type' => 'list', 'id' => $listId],
                ],
            ],
        ],
    ]);
    return [$ok, $message];
}

/**
 * High-level: call this once per successful website form submission.
 * Sends a single unified "Website Enquiry Submitted" event (so every
 * form's enquiries show up the same way in Klaviyo, distinguished by
 * the "Form Name" property) and, if a List ID is configured, also
 * subscribes the profile to that list. $dbId is the row's own id in
 * our database, used to build a stable Klaviyo unique_id so a retried
 * request never creates a duplicate event. Does nothing if no API key
 * is set. Never throws; logs failures (never the enquiry message
 * itself) and always returns quietly either way.
 */
function klaviyo_track_enquiry(string $formName, int $dbId, array $profile, array $extraProperties, string $message): void
{
    $apiKey = trim(setting('klaviyo_private_api_key'));
    if ($apiKey === '') {
        return;
    }

    $customerName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
    $properties = array_merge([
        'Form Name'      => $formName,
        'Customer Name'  => $customerName,
        'Email'          => $profile['email'] ?? '',
        'Phone'          => $profile['phone_number'] ?? '',
        'Message'        => $message,
        'Submitted At'   => gmdate('Y-m-d H:i:s') . ' UTC',
        'Website URL'    => site_origin(),
    ], $extraProperties);

    $uniqueId = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $formName)) . '-' . $dbId;
    [$sent, $resultMessage] = klaviyo_send_event($apiKey, 'Website Enquiry Submitted', $properties, $profile, $uniqueId);
    if (!$sent) {
        error_log('Klaviyo event sync failed (' . $formName . ', id ' . $dbId . '): ' . $resultMessage);
    }

    $listId = trim(setting('klaviyo_list_id'));
    if ($listId !== '') {
        [$subscribed, $subMessage] = klaviyo_subscribe_profile_to_list($apiKey, $listId, $profile);
        if (!$subscribed) {
            error_log('Klaviyo list subscribe failed (' . $formName . ', id ' . $dbId . '): ' . $subMessage);
        }
    }
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
 * The display order for a page's reorderable content sections (admin >
 * [page] > Section Order, admins only), as a list of section keys.
 * Stored as a single comma-separated setting named "<pageKey>_section_order".
 * Falls back to $defaultOrder if nothing has been saved yet. Any stored
 * key no longer in $defaultOrder is dropped, and any key in $defaultOrder
 * missing from storage (e.g. a newly added section) is appended at the
 * end - so reordering stays safe even as pages change over time.
 */
function section_order(string $pageKey, array $defaultOrder): array
{
    $stored = setting($pageKey . '_section_order');
    if ($stored === '') {
        return $defaultOrder;
    }
    $order = array_values(array_intersect(array_filter(array_map('trim', explode(',', $stored))), $defaultOrder));
    foreach ($defaultOrder as $key) {
        if (!in_array($key, $order, true)) {
            $order[] = $key;
        }
    }
    return $order;
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
 * Whether a given page should be shown on the live site (admin > Main
 * Pages). The homepage is always visible. Falls back to visible if the
 * page has no seo_meta row yet, or the column doesn't exist (migration
 * not run).
 */
function page_visible(string $pageKey): bool
{
    static $cache = [];
    if ($pageKey === 'index') {
        return true;
    }
    if (isset($cache[$pageKey])) {
        return $cache[$pageKey];
    }
    try {
        $stmt = db()->prepare('SELECT page_visible FROM seo_meta WHERE page_key = ? LIMIT 1');
        $stmt->execute([$pageKey]);
        $v = $stmt->fetchColumn();
        $cache[$pageKey] = $v === false ? true : ((int) $v === 1);
    } catch (Throwable $e) {
        $cache[$pageKey] = true;
    }
    return $cache[$pageKey];
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
