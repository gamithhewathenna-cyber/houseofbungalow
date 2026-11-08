<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('br_heading', 'Reserve Your Night At The House.');
$page_meta_description = setting('br_subheading');

include __DIR__ . '/includes/header.php';

$br_hero_video = setting('br_hero_video');
$br_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($br_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';
?>

<!-- Hero (single static slide, reusing the same overlay-card look as Reserve) -->
<?php if (section_visible('br_hero')): ?>
<section class="hero hero-slider">
  <?php if ($br_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($br_hero_video)) ?>" type="<?= e($br_hero_video_mime) ?>">
    </video>
  <?php else: ?>
    <div class="hero-slide active" style="background-image:url('<?= e(asset(setting('br_hero_image', 'assets/img/below.jpg'))) ?>');"></div>
  <?php endif; ?>
  <div class="hero-overlay-card active">
    <?php if (setting('br_heading')): ?><h1><?= e(setting('br_heading', 'Reserve Your Night At The House.')) ?></h1><?php endif; ?>
    <?php if (setting('br_subheading')): ?><p class="subheading"><?= e(setting('br_subheading')) ?></p><?php endif; ?>
    <?php if (setting('br_body')): ?><p><?= enl(setting('br_body')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Reservations heading + widget (same OpenTable embed as the Below page, centered) -->
<?php if (section_visible('br_widget')): ?>
<section class="section reveal">
  <div class="container">
    <h2><?= e(setting('br_section_heading', 'Below Reservations')) ?></h2>
    <?php if (setting('below_opentable_embed')): ?>
      <div class="opentable-center"><?= setting('below_opentable_embed') ?></div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Looking for Restaurant reservations? -->
<?php if (section_visible('br_cross') && setting('br_cross_heading')): ?>
<section class="section reveal">
  <div class="container">
    <h2><?= e(setting('br_cross_heading', 'Looking for Restaurant reservations?')) ?></h2>
    <?php if (setting('br_cross_btn_label')): ?>
      <div class="btn-wrap">
        <a class="link-underline" href="<?= e(url(setting('br_cross_btn_url', 'restaurantreservations.php'))) ?>"><?= e(setting('br_cross_btn_label')) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
