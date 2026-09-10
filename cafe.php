<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('cafe_heading', 'Café');
$page_meta_description = setting('cafe_lede');

include __DIR__ . '/includes/header.php';

$cafe_hero_image = asset(setting('cafe_hero_image', 'assets/img/cafe.jpg'));
$cafe_hero_video = setting('cafe_hero_video');
$cafe_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($cafe_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';
?>

<!-- Hero ------------------------------------------------------------- -->
<section class="hero"<?php if (!$cafe_hero_video): ?> style="background-image:url('<?= e($cafe_hero_image) ?>');"<?php endif; ?>>
  <?php if ($cafe_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($cafe_hero_video)) ?>" type="<?= e($cafe_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>

<!-- Intro -------------------------------------------------------------- -->
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('cafe_heading', 'Café')) ?></h1>
    <p class="subheading"><?= e(setting('cafe_subheading')) ?></p>
    <p class="lede"><?= e(setting('cafe_lede')) ?></p>
    <p class="cafe-intro-p"><?= e(setting('cafe_p1')) ?></p>
    <p class="cafe-intro-p"><?= e(setting('cafe_p2')) ?></p>
  </div>
</section>

<!-- Gallery (3 fixed photos) ------------------------------------------- -->
<section class="section reveal">
  <div class="container">
    <div class="trio-grid">
      <div class="trio-item"><img src="<?= e(asset(setting('cafe_gallery_image1', 'assets/img/cafe.jpg'))) ?>" alt=""></div>
      <div class="trio-item"><img src="<?= e(asset(setting('cafe_gallery_image2', 'assets/img/restaurant.jpg'))) ?>" alt=""></div>
      <div class="trio-item"><img src="<?= e(asset(setting('cafe_gallery_image3', 'assets/img/building.png'))) ?>" alt=""></div>
    </div>
  </div>
</section>

<hr class="section-divider">

<!-- Hours ---------------------------------------------------------------- -->
<section class="hours-block reveal">
  <div class="container">
    <h2><?= e(setting('cafe_hours_heading', 'Coffee. Food. Foveaux Street.')) ?></h2>
    <?php if (setting('cafe_hours_p1')): ?><p class="hours-note"><?= e(setting('cafe_hours_p1')) ?></p><?php endif; ?>
    <?php if (setting('cafe_hours_p2')): ?><p class="hours-note"><?= e(setting('cafe_hours_p2')) ?></p><?php endif; ?>
    <?php if (setting('cafe_hours')): ?><p class="hours-line" style="margin-top:16px;"><?= eb(setting('cafe_hours')) ?></p><?php endif; ?>
    <?php if (setting('cafe_address')): ?><p class="hours-address"><?= e(setting('cafe_address')) ?></p><?php endif; ?>
    <div class="btn-wrap btn-wrap-row">
      <?php if (setting('cafe_btn1_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('cafe_btn1_url', '#'))) ?>"><?= e(setting('cafe_btn1_label')) ?></a>
      <?php endif; ?>
      <?php if (setting('cafe_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('cafe_btn2_url', '#'))) ?>"><?= e(setting('cafe_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- More Of The House ----------------------------------------------------- -->
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('cafe_final_heading', 'More Of The House')) ?></h2>
    <p><?= e(setting('cafe_final_p1')) ?></p>
    <p><?= e(setting('cafe_final_p2')) ?></p>
    <div class="btn-wrap btn-wrap-row">
      <?php if (setting('cafe_final_btn1_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('cafe_final_btn1_url', '#'))) ?>"><?= e(setting('cafe_final_btn1_label')) ?></a>
      <?php endif; ?>
      <?php if (setting('cafe_final_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('cafe_final_btn2_url', '#'))) ?>"><?= e(setting('cafe_final_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
