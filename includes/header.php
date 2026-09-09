<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(setting('intro_heading', SITE_NAME)) ?> — <?= e(SITE_NAME) ?></title>
<meta name="description" content="<?= e(setting('intro_p1')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
</head>
<body>

<header class="site-header">
  <div class="container">
    <a class="brand-logo" href="<?= e(url('index.php')) ?>" aria-label="<?= e(SITE_NAME) ?>">
      <img src="<?= e(asset('assets/img/logo-white.png')) ?>" alt="<?= e(SITE_NAME) ?>">
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
