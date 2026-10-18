<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('press_heading', 'Press & Media');
$page_meta_description = setting('press_lede');

include __DIR__ . '/includes/header.php';

$press_hero_image = asset(setting('press_hero_image', 'assets/img/restaurant.jpg'));
$press_hero_video = setting('press_hero_video');
$press_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($press_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';
?>

<!-- Hero ------------------------------------------------------------- -->
<?php if (section_visible('press_hero')): ?>
<section class="hero"<?php if (!$press_hero_video): ?> style="background-image:url('<?= e($press_hero_image) ?>');"<?php endif; ?>>
  <?php if ($press_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($press_hero_video)) ?>" type="<?= e($press_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Intro -------------------------------------------------------------- -->
<?php if (section_visible('press_intro')): ?>
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('press_heading', 'Press & Media')) ?></h1>
    <?php if (setting('press_subheading')): ?><p class="subheading"><?= e(setting('press_subheading')) ?></p><?php endif; ?>
    <?php if (setting('press_lede')): ?><p class="lede"><?= enl(setting('press_lede')) ?></p><?php endif; ?>
    <?php if (setting('press_p1')): ?><p><?= enl(setting('press_p1')) ?></p><?php endif; ?>
    <?php if (setting('press_p2')): ?><p><?= enl(setting('press_p2')) ?></p><?php endif; ?>
    <?php if (setting('press_p3')): ?><p><?= enl(setting('press_p3')) ?></p><?php endif; ?>
    <?php if (setting('press_btn_label')): ?>
      <div class="btn-wrap">
        <a class="link-underline js-press-popup-trigger" href="#"><?= e(setting('press_btn_label', 'MEDIA ENQUIRIES')) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
