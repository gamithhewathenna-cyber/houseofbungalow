<?php
require_once __DIR__ . '/functions.php';

if (setting('maintenance_mode', '0') === '1') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['admin'])) {
        http_response_code(503);
        header('Retry-After: 3600');
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(SITE_NAME) ?> — Down for Maintenance</title>
<style>
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
    background:<?= e(setting('theme_cream', '#F2E8DE')) ?>;color:<?= e(setting('theme_ink', '#545355')) ?>;
    font-family:Georgia,'Times New Roman',serif;text-align:center;padding:24px;}
</style>
</head>
<body>
  <div>
    <h1><?= e(SITE_NAME) ?></h1>
    <p>We're currently making some updates. Please check back shortly.</p>
  </div>
</body>
</html>
        <?php
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if (setting('seo_visible', '1') !== '1'): ?>
<meta name="robots" content="noindex,nofollow">
<?php endif; ?>
<title><?= e(setting('intro_heading', SITE_NAME)) ?> — <?= e(SITE_NAME) ?></title>
<meta name="description" content="<?= e(setting('intro_p1')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
<style>
:root{
  --cream:<?= e(setting('theme_cream', '#F2E8DE')) ?>;
  --ink:<?= e(setting('theme_ink', '#545355')) ?>;
  --ink-soft:<?= e(setting('theme_ink_soft', '#7d7873')) ?>;
  --ink-mute:<?= e(setting('theme_ink_mute', '#948f8a')) ?>;
  --maroon:<?= e(setting('theme_maroon', '#620E15')) ?>;
  --dark:<?= e(setting('theme_dark', '#0d0b0a')) ?>;
}
</style>
</head>
<body>

<header class="site-header">
  <div class="container">
    <a class="brand-logo" href="<?= e(url('index.php')) ?>" aria-label="<?= e(SITE_NAME) ?>">
      <img src="<?= e(asset(setting('logo_white', 'assets/img/logo-white.png'))) ?>" alt="<?= e(SITE_NAME) ?>">
    </a>
    <nav class="header-nav">
      <a href="<?= e(url(setting('nav_link_1_url','#'))) ?>"><?= e(setting('nav_link_1_label','VIP')) ?></a>
      <a href="<?= e(url(setting('nav_link_2_url','#'))) ?>"><?= e(setting('nav_link_2_label','RESERVE')) ?></a>
      <button class="nav-toggle" aria-label="Menu" onclick="return false;">
        <span></span><span></span><span></span>
      </button>
    </nav>
  </div>
</header>

<section class="hero" style="background-image:url('<?= e(asset(setting('hero_image','assets/img/hero.jpg'))) ?>');"></section>
