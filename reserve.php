<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('reserve_hero_heading', 'Reserve');
$page_meta_description = setting('reserve_hero_p1');

include __DIR__ . '/includes/header.php';

$reserve_hero_image = asset(setting('reserve_hero_image', 'assets/img/cafe.jpg'));
$reserve_hero_video = setting('reserve_hero_video');
$reserve_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($reserve_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';
?>

<!-- Hero (with centered overlay card) --------------------------------- -->
<section class="hero"<?php if (!$reserve_hero_video): ?> style="background-image:url('<?= e($reserve_hero_image) ?>');"<?php endif; ?>>
  <?php if ($reserve_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($reserve_hero_video)) ?>" type="<?= e($reserve_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
  <div class="hero-overlay-card">
    <h1><?= e(setting('reserve_hero_heading', 'Reserve Your Night At The House.')) ?></h1>
    <p class="subheading"><?= e(setting('reserve_hero_subheading')) ?></p>
    <p><?= e(setting('reserve_hero_p1')) ?></p>
    <p><?= e(setting('reserve_hero_p2')) ?></p>
    <?php if (setting('reserve_hero_btn_label')): ?>
      <div class="btn-wrap">
        <a class="link-underline" href="<?= e(url(setting('reserve_hero_btn_url', '#happyhour'))) ?>"><?= e(setting('reserve_hero_btn_label')) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Reservations heading ------------------------------------------------ -->
<section class="section reveal">
  <div class="container">
    <h2><?= e(setting('reserve_heading', 'Reservations')) ?></h2>
  </div>
</section>

<!-- Friday & Saturday ----------------------------------------------------- -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('reserve_fridaysat_eyebrow', 'Friday & Saturday')) ?></p>
        <h2><?= e(setting('reserve_fridaysat_heading')) ?></h2>
        <p><?= e(setting('reserve_fridaysat_p1')) ?></p>
        <p><?= e(setting('reserve_fridaysat_p2')) ?></p>
        <?php if (setting('reserve_fridaysat_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('reserve_fridaysat_btn_url', '#'))) ?>"><?= e(setting('reserve_fridaysat_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img src="<?= e(asset(setting('reserve_fridaysat_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('reserve_fridaysat_heading')) ?>">
      </div>
    </div>
  </div>
</section>

<!-- Happy Hour -------------------------------------------------------------- -->
<section id="happyhour" class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img src="<?= e(asset(setting('reserve_happyhour_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('reserve_happyhour_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('reserve_happyhour_eyebrow', 'Happy Hour')) ?></p>
        <h2><?= e(setting('reserve_happyhour_heading')) ?></h2>
        <p><?= e(setting('reserve_happyhour_p1')) ?></p>
        <?php if (setting('reserve_happyhour_hours1')): ?><p class="hours-line"><?= eb(setting('reserve_happyhour_hours1')) ?></p><?php endif; ?>
        <?php if (setting('reserve_happyhour_hours2')): ?><p class="hours-line"><?= eb(setting('reserve_happyhour_hours2')) ?></p><?php endif; ?>
        <?php if (setting('reserve_happyhour_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('reserve_happyhour_btn_url', '#'))) ?>"><?= e(setting('reserve_happyhour_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- VIP Tables ------------------------------------------------------------------ -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('reserve_vip_eyebrow', 'VIP Tables')) ?></p>
        <h2><?= e(setting('reserve_vip_heading')) ?></h2>
        <p><?= e(setting('reserve_vip_p1')) ?></p>
        <p><?= e(setting('reserve_vip_p2')) ?></p>
        <?php if (setting('reserve_vip_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('reserve_vip_btn_url', '#'))) ?>"><?= e(setting('reserve_vip_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img src="<?= e(asset(setting('reserve_vip_image', 'assets/img/hero.jpg'))) ?>" alt="<?= e(setting('reserve_vip_heading')) ?>">
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
