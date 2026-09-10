<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('pe_heading', 'Private Events');
$page_meta_description = setting('pe_lede');

include __DIR__ . '/includes/header.php';

$pe_hero_image = asset(setting('pe_hero_image', 'assets/img/below.jpg'));
$pe_hero_video = setting('pe_hero_video');
$pe_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($pe_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$cards = blocks('pe_event_card');
?>

<!-- Hero ------------------------------------------------------------- -->
<section class="hero"<?php if (!$pe_hero_video): ?> style="background-image:url('<?= e($pe_hero_image) ?>');"<?php endif; ?>>
  <?php if ($pe_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($pe_hero_video)) ?>" type="<?= e($pe_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>

<!-- Intro -------------------------------------------------------------- -->
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('pe_heading', 'Private Events')) ?></h1>
    <p class="subheading"><?= e(setting('pe_subheading')) ?></p>
    <p class="lede"><?= e(setting('pe_lede')) ?></p>
    <p><?= e(setting('pe_p1')) ?></p>
    <p><?= e(setting('pe_p2')) ?></p>
    <p><?= e(setting('pe_p3')) ?></p>
    <div class="btn-wrap">
      <?php if (setting('pe_btn_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('pe_btn_url', '#'))) ?>"><?= e(setting('pe_btn_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Event Cards (Restaurant Events / Below Events) --------------------- -->
<?php if ($cards): ?>
<section class="events-cards reveal">
  <div class="events-grid">
    <?php foreach ($cards as $c): ?>
      <div class="event-card">
        <div class="event-card-media">
          <img src="<?= e(asset($c['image'])) ?>" alt="<?= e($c['title']) ?>">
        </div>
        <div class="event-card-body">
          <h3><?= e($c['title']) ?></h3>
          <?php if ($c['subtitle']): ?><p class="event-card-tagline"><?= e($c['subtitle']) ?></p><?php endif; ?>
          <?php if ($c['body']): ?><p><?= e($c['body']) ?></p><?php endif; ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url($c['link_url'] ?: '#')) ?>">ENQUIRE</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<hr class="footer-rule">

<!-- Make The House Yours ------------------------------------------------ -->
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('pe_final_heading', 'Make The House Yours')) ?></h2>
    <p><?= e(setting('pe_final_p1')) ?></p>
    <?php if (setting('pe_final_btn_label')): ?>
      <div class="btn-wrap">
        <a class="link-underline" href="<?= e(url(setting('pe_final_btn_url', '#'))) ?>"><?= e(setting('pe_final_btn_label')) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
