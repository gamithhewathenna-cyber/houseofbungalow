<?php
/**
 * Routes a custom SEO slug (admin > SEO) to its real page file.
 * .htaccess sends any request that doesn't match a real file here as a
 * last resort, with the requested path in ?slug=.
 */
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '', '/');

$target = null;
if ($slug !== '') {
    try {
        $stmt = db()->prepare('SELECT page_file FROM seo_meta WHERE seo_slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
    } catch (Throwable $e) {
        // seo_meta table may not exist yet (migration not run) - treat as no match.
        $row = null;
    }
    if ($row && preg_match('/^[a-z0-9_-]+\.php$/i', $row['page_file'])) {
        $candidate = __DIR__ . '/' . $row['page_file'];
        if (is_file($candidate)) {
            $target = $candidate;
            define('CURRENT_PAGE_FILE', $row['page_file']);
        }
    }
}

if ($target === null) {
    http_response_code(404);
    include __DIR__ . '/includes/header.php';
    ?>
    <section class="page-hero-intro reveal">
      <div class="container">
        <h1>Page not found</h1>
        <p class="lede">The page you're looking for doesn't exist or may have moved.</p>
      </div>
    </section>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

require $target;
