<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('cafe_heading', 'Café');
$page_meta_description = setting('cafe_lede');

include __DIR__ . '/includes/header.php';

$cafe_hero_image = asset(setting('cafe_hero_image', 'assets/img/cafe.jpg'));
$cafe_hero_video = setting('cafe_hero_video');
$cafe_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($cafe_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$gallery = blocks('cafe_gallery');
$gallery_rows = [];
$i = 0; $take3 = true;
while ($i < count($gallery)) {
    $take = $take3 ? 3 : 2;
    $gallery_rows[] = array_slice($gallery, $i, $take);
    $i += $take;
    $take3 = !$take3;
}
?>

<!-- Hero ------------------------------------------------------------- -->
<?php if (section_visible('cafe_hero')): ?>
<section class="hero"<?php if (!$cafe_hero_video): ?> style="background-image:url('<?= e($cafe_hero_image) ?>');"<?php endif; ?>>
  <?php if ($cafe_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($cafe_hero_video)) ?>" type="<?= e($cafe_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Intro -------------------------------------------------------------- -->
<?php if (section_visible('cafe_intro')): ?>
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('cafe_heading', 'Café')) ?></h1>
    <p class="subheading"><?= e(setting('cafe_subheading')) ?></p>
    <p class="lede"><?= e(setting('cafe_lede')) ?></p>
    <p class="cafe-intro-p"><?= enl(setting('cafe_p1')) ?></p>
    <p class="cafe-intro-p"><?= enl(setting('cafe_p2')) ?></p>
  </div>
</section>
<?php endif; ?>

<!-- Gallery ------------------------------------------------------------------ -->
<?php if ($gallery && section_visible('cafe_gallery')): ?>
<section class="rest-gallery reveal">
  <div class="container">
    <div class="gallery-grid">
      <?php foreach ($gallery_rows as $row): ?>
        <div class="gallery-row gallery-row-<?= count($row) ?>">
          <?php foreach ($row as $g): ?>
            <div class="gallery-item">
              <img src="<?= e(asset($g['image'])) ?>" alt="<?= e($g['title'] ?: SITE_NAME) ?>">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<hr class="section-divider">
<?php endif; ?>

<!-- Hours ---------------------------------------------------------------- -->
<?php if (section_visible('cafe_hours')): ?>
<section class="hours-block reveal">
  <div class="container">
    <h2><?= e(setting('cafe_hours_heading', 'Coffee. Food. Foveaux Street.')) ?></h2>
    <?php if (setting('cafe_hours_p1')): ?><p class="hours-note"><?= enl(setting('cafe_hours_p1')) ?></p><?php endif; ?>
    <?php if (setting('cafe_hours_p2')): ?><p class="hours-note"><?= enl(setting('cafe_hours_p2')) ?></p><?php endif; ?>
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
<?php endif; ?>

<!-- More Of The House ----------------------------------------------------- -->
<?php if (section_visible('cafe_final')): ?>
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('cafe_final_heading', 'More Of The House')) ?></h2>
    <p><?= enl(setting('cafe_final_p1')) ?></p>
    <p><?= enl(setting('cafe_final_p2')) ?></p>
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
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
