<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('rr_heading', 'Reserve Your Night At The House.');
$page_meta_description = setting('rr_subheading');

include __DIR__ . '/includes/header.php';
?>

<!-- Hero (single static slide, reusing the same overlay-card look as Reserve) -->
<?php if (section_visible('rr_hero')): ?>
<section class="hero hero-slider">
  <div class="hero-slide active" style="background-image:url('<?= e(asset(setting('rr_hero_image', 'assets/img/restaurant.jpg'))) ?>');"></div>
  <div class="hero-overlay-card active">
    <?php if (setting('rr_heading')): ?><h1><?= e(setting('rr_heading', 'Reserve Your Night At The House.')) ?></h1><?php endif; ?>
    <?php if (setting('rr_subheading')): ?><p class="subheading"><?= e(setting('rr_subheading')) ?></p><?php endif; ?>
    <?php if (setting('rr_body')): ?><p><?= enl(setting('rr_body')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Reservations heading + widget (same OpenTable embed as the Restaurant page, centered) -->
<?php if (section_visible('rr_widget')): ?>
<section class="section reveal">
  <div class="container">
    <h2><?= e(setting('rr_section_heading', 'Restaurant Reservations')) ?></h2>
    <?php if (setting('rest_opentable_embed')): ?>
      <div class="opentable-center"><?= setting('rest_opentable_embed') ?></div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Looking for Below? -->
<?php if (section_visible('rr_below') && setting('rr_below_heading')): ?>
<section class="section reveal">
  <div class="container">
    <h2><?= e(setting('rr_below_heading', 'Looking for Below?')) ?></h2>
    <?php if (setting('rr_below_btn_label')): ?>
      <div class="btn-wrap">
        <a class="link-underline" href="<?= e(url(setting('rr_below_btn_url', 'below.php'))) ?>"><?= e(setting('rr_below_btn_label')) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
