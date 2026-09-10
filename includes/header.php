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
<title><?= e($page_meta_title ?? setting('intro_heading', SITE_NAME)) ?> — <?= e(SITE_NAME) ?></title>
<meta name="description" content="<?= e($page_meta_description ?? setting('intro_p1')) ?>">
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

<header class="site-header" id="siteHeader">
  <div class="container">
    <a class="brand-logo" href="<?= e(url('index.php')) ?>" aria-label="<?= e(SITE_NAME) ?>">
      <img src="<?= e(asset(setting('logo_white', 'assets/img/logo-white.png'))) ?>" alt="<?= e(SITE_NAME) ?>">
    </a>
    <nav class="header-nav">
      <a href="<?= e(url(setting('nav_link_1_url','#'))) ?>"><?= e(setting('nav_link_1_label','VIP')) ?></a>
      <a href="<?= e(url(setting('nav_link_2_url','#'))) ?>"><?= e(setting('nav_link_2_label','RESERVE')) ?></a>
      <button class="nav-toggle" id="navToggle" aria-label="Menu" aria-expanded="false" aria-controls="mobileMenu">
        <span></span><span></span><span></span>
      </button>
    </nav>
  </div>
</header>

<nav class="mobile-menu" id="mobileMenu" aria-hidden="true">
  <div class="mobile-menu-links">
    <a href="<?= e(url('index.php')) ?>">HOME</a>
    <a href="<?= e(url('restaurant.php')) ?>">RESTAURANT &amp; MENUS</a>
    <a href="<?= e(url('below.php')) ?>">BELOW</a>
    <a href="<?= e(url('whatson.php')) ?>">WHAT'S ON</a>
    <a href="<?= e(url('brunch.php')) ?>">BRUNCH</a>
    <a href="<?= e(url('dinnerparty.php')) ?>">DINNER PARTY</a>
    <a href="<?= e(url('privateevents.php')) ?>">PRIVATE EVENTS</a>
    <a href="#">CAFÉ</a>
    <a href="#">FAQs</a>
  </div>
</nav>

<script>
(function () {
  var toggle = document.getElementById('navToggle');
  var menu = document.getElementById('mobileMenu');
  var header = document.getElementById('siteHeader');
  if (!toggle || !menu || !header) return;

  function setMenu(open) {
    menu.classList.toggle('is-open', open);
    header.classList.toggle('menu-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    menu.setAttribute('aria-hidden', open ? 'false' : 'true');
    document.body.classList.toggle('menu-open-lock', open);
  }

  toggle.addEventListener('click', function () {
    setMenu(!menu.classList.contains('is-open'));
  });

  menu.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () { setMenu(false); });
  });
})();
</script>
