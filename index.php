<?php
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php';
$spaces = blocks('space');

$hero_image_path = asset(setting('hero_image', 'assets/img/hero.jpg'));
$hero_video_path = setting('hero_video');
$hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($hero_video_path, PATHINFO_EXTENSION))] ?? 'video/mp4';
?>
<?php if (section_visible('hero')): ?>
<section class="hero"<?php if (!$hero_video_path): ?> style="background-image:url('<?= e($hero_image_path) ?>');"<?php endif; ?>>
  <?php if ($hero_video_path): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($hero_video_path)) ?>" type="<?= e($hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Intro ---------------------------------------------------------- -->
<?php if (section_visible('intro')): ?>
<section class="intro reveal">
  <div class="container">
    <div class="intro-inner">
      <h1><?= e(setting('intro_heading')) ?></h1>
      <p class="subheading"><?= e(setting('intro_subheading')) ?></p>
      <p class="address"><?= e(setting('intro_address')) ?></p>

      <p><?= enl(setting('intro_p1')) ?></p>

      <p class="tight gap-top"><?= enl(setting('intro_p2a')) ?></p>
      <p><?= enl(setting('intro_p2b')) ?></p>

      <p class="gap-top"><?= enl(setting('intro_p3')) ?></p>

      <div class="intro-actions">
        <a class="link-underline" href="<?= e(url(setting('intro_btn1_url','#'))) ?>"><?= e(setting('intro_btn1_label')) ?></a>
        <a class="link-underline js-booking-popup-trigger" href="<?= e(url(setting('intro_btn2_url','#'))) ?>"><?= e(setting('intro_btn2_label')) ?></a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
/**
 * Content sections below the Intro, in their admin-configurable order
 * (admin > Home Page > Section Order, admins only - Hero and Intro
 * always stay fixed at the top).
 */
$home_sections = [
    'building' => function (): void {
        if (!section_visible('building')) return;
        ?>
<section class="building reveal">
  <div class="container">
    <img loading="lazy" decoding="async" src="<?= e(asset(setting('building_image','assets/img/building.png'))) ?>" alt="House of Bungalow storefront illustration">
  </div>
</section>
<?php
    },
    'spaces' => function () use ($spaces): void {
        if (!section_visible('spaces')) return;
        ?>
<section class="spaces reveal">
  <div class="container">
    <h2><?= e(setting('spaces_heading')) ?></h2>
    <p class="subheading"><?= enl(setting('spaces_subheading')) ?></p>
    <div class="cards">
      <?php foreach ($spaces as $i => $s): ?>
        <a class="card reveal" style="transition-delay:<?= $i * 120 ?>ms" href="<?= e(url($s['link_url'] ?: '#')) ?>">
          <img loading="lazy" decoding="async" src="<?= e(asset($s['image'])) ?>" alt="<?= e($s['title']) ?>">
          <span class="card-label"><?= e($s['title']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
    },
    'door' => function (): void {
        if (!section_visible('door')) return;
        ?>
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('door_heading')) ?></h2>
    <p><?= enl(setting('door_p1')) ?></p>
    <p><?= enl(setting('door_p2')) ?></p>
    <div class="btn-wrap">
      <a class="link-underline" href="<?= e(url(setting('door_btn_url','#'))) ?>"><?= e(setting('door_btn_label')) ?></a>
    </div>
  </div>
</section>
<?php
    },
    'mood' => function (): void {
        if (!section_visible('mood')) return;
        ?>
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('mood_heading')) ?></h2>
    <p><?= enl(setting('mood_p1')) ?></p>
    <p><?= enl(setting('mood_p2')) ?></p>
  </div>
</section>
<?php
    },
    'whatson' => function (): void {
        if (!section_visible('whatson')) return;
        ?>
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('whatson_heading')) ?></h2>
    <p><?= enl(setting('whatson_p1')) ?></p>
    <p><?= enl(setting('whatson_p2')) ?></p>
    <p><?= enl(setting('whatson_p3')) ?></p>
    <div class="btn-wrap">
      <a class="link-underline" href="<?= e(url(setting('whatson_btn_url','#'))) ?>"><?= e(setting('whatson_btn_label')) ?></a>
    </div>
  </div>
</section>
<?php
    },
    'events' => function (): void {
        if (!section_visible('events')) return;
        ?>
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('events_heading')) ?></h2>
    <p><?= enl(setting('events_p1')) ?></p>
    <p><?= enl(setting('events_p2')) ?></p>
    <p><?= enl(setting('events_p3')) ?></p>
    <div class="btn-wrap">
      <a class="link-underline" href="<?= e(url(setting('events_btn_url','#'))) ?>"><?= e(setting('events_btn_label')) ?></a>
    </div>
  </div>
</section>
<?php
    },
];
foreach (section_order('home', array_keys($home_sections)) as $home_key) {
    $home_sections[$home_key]();
}
?>

<?php include __DIR__ . '/includes/promo-banner-popup.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
