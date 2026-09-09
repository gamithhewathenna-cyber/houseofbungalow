<?php
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php';
$spaces = blocks('space');

$hero_image_path = asset(setting('hero_image', 'assets/img/hero.jpg'));
$hero_video_path = setting('hero_video');
$hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($hero_video_path, PATHINFO_EXTENSION))] ?? 'video/mp4';
?>
<section class="hero"<?php if (!$hero_video_path): ?> style="background-image:url('<?= e($hero_image_path) ?>');"<?php endif; ?>>
  <?php if ($hero_video_path): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($hero_video_path)) ?>" type="<?= e($hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>

<!-- Intro ---------------------------------------------------------- -->
<section class="intro reveal">
  <div class="container">
    <div class="intro-inner">
      <h1><?= e(setting('intro_heading')) ?></h1>
      <p class="subheading"><?= e(setting('intro_subheading')) ?></p>
      <p class="address"><?= e(setting('intro_address')) ?></p>

      <p><?= e(setting('intro_p1')) ?></p>

      <p class="tight gap-top"><?= e(setting('intro_p2a')) ?></p>
      <p><?= e(setting('intro_p2b')) ?></p>

      <p class="gap-top"><?= e(setting('intro_p3')) ?></p>

      <div class="intro-actions">
        <a class="link-underline" href="<?= e(url(setting('intro_btn1_url','#'))) ?>"><?= e(setting('intro_btn1_label')) ?></a>
        <a class="link-underline" href="<?= e(url(setting('intro_btn2_url','#'))) ?>"><?= e(setting('intro_btn2_label')) ?></a>
      </div>
    </div>
  </div>
</section>

<!-- Building illustration ------------------------------------------ -->
<section class="building reveal">
  <div class="container">
    <img src="<?= e(asset(setting('building_image','assets/img/building.png'))) ?>" alt="House of Bungalow storefront illustration">
  </div>
</section>

<!-- Three Spaces --------------------------------------------------- -->
<section class="spaces reveal">
  <div class="container">
    <h2><?= e(setting('spaces_heading')) ?></h2>
    <p class="subheading"><?= e(setting('spaces_subheading')) ?></p>
    <div class="cards">
      <?php foreach ($spaces as $i => $s): ?>
        <a class="card reveal" style="transition-delay:<?= $i * 120 ?>ms" href="<?= e(url($s['link_url'] ?: '#')) ?>">
          <img src="<?= e(asset($s['image'])) ?>" alt="<?= e($s['title']) ?>">
          <span class="card-label"><?= e($s['title']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- The House Starts At The Door ----------------------------------- -->
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('door_heading')) ?></h2>
    <p><?= e(setting('door_p1')) ?></p>
    <p><?= e(setting('door_p2')) ?></p>
    <div class="btn-wrap">
      <a class="link-underline" href="<?= e(url(setting('door_btn_url','#'))) ?>"><?= e(setting('door_btn_label')) ?></a>
    </div>
  </div>
</section>

<!-- One Address. Every Mood ---------------------------------------- -->
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('mood_heading')) ?></h2>
    <p><?= e(setting('mood_p1')) ?></p>
    <p><?= e(setting('mood_p2')) ?></p>
  </div>
</section>

<!-- What's On ------------------------------------------------------- -->
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('whatson_heading')) ?></h2>
    <p><?= e(setting('whatson_p1')) ?></p>
    <p><?= e(setting('whatson_p2')) ?></p>
    <p><?= e(setting('whatson_p3')) ?></p>
    <div class="btn-wrap">
      <a class="link-underline" href="<?= e(url(setting('whatson_btn_url','#'))) ?>"><?= e(setting('whatson_btn_label')) ?></a>
    </div>
  </div>
</section>

<!-- Private Events -------------------------------------------------- -->
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('events_heading')) ?></h2>
    <p><?= e(setting('events_p1')) ?></p>
    <p><?= e(setting('events_p2')) ?></p>
    <p><?= e(setting('events_p3')) ?></p>
    <div class="btn-wrap">
      <a class="link-underline" href="<?= e(url(setting('events_btn_url','#'))) ?>"><?= e(setting('events_btn_label')) ?></a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
