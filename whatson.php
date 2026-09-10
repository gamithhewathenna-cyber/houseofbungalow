<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('wo_heading', "What's On");
$page_meta_description = setting('wo_lede');

include __DIR__ . '/includes/header.php';

$wo_hero_image = asset(setting('wo_hero_image', 'assets/img/cafe.jpg'));
$wo_hero_video = setting('wo_hero_video');
$wo_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($wo_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$faqs = blocks('whatson_faq');
?>

<!-- Hero ------------------------------------------------------------- -->
<section class="hero"<?php if (!$wo_hero_video): ?> style="background-image:url('<?= e($wo_hero_image) ?>');"<?php endif; ?>>
  <?php if ($wo_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($wo_hero_video)) ?>" type="<?= e($wo_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>

<!-- Intro -------------------------------------------------------------- -->
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('wo_heading', "What's On")) ?></h1>
    <p class="subheading"><?= e(setting('wo_subheading')) ?></p>
    <p class="lede"><?= e(setting('wo_lede')) ?></p>

    <div class="tag-buttons">
      <?php if (setting('wo_tag1_label')): ?><a class="tag-btn" href="<?= e(url(setting('wo_tag1_url', '#'))) ?>"><?= e(setting('wo_tag1_label')) ?></a><?php endif; ?>
      <?php if (setting('wo_tag2_label')): ?><a class="tag-btn" href="<?= e(url(setting('wo_tag2_url', '#'))) ?>"><?= e(setting('wo_tag2_label')) ?></a><?php endif; ?>
      <?php if (setting('wo_tag3_label')): ?><a class="tag-btn" href="<?= e(url(setting('wo_tag3_url', '#'))) ?>"><?= e(setting('wo_tag3_label')) ?></a><?php endif; ?>
    </div>
  </div>
</section>

<!-- Happy Hour ----------------------------------------------------------- -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('wo_happyhour_eyebrow', 'Happy Hour')) ?></p>
        <h2><?= e(setting('wo_happyhour_heading', 'INFORMATION')) ?></h2>
        <p><?= e(setting('wo_happyhour_p1')) ?></p>
        <p><?= e(setting('wo_happyhour_p2')) ?></p>
        <?php if (setting('wo_happyhour_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('wo_happyhour_btn_url', '#'))) ?>"><?= e(setting('wo_happyhour_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img src="<?= e(asset(setting('wo_happyhour_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('wo_happyhour_eyebrow')) ?>">
      </div>
    </div>
  </div>
</section>

<!-- Friday & Saturday Dinner Party ----------------------------------------- -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img src="<?= e(asset(setting('wo_friday_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('wo_friday_eyebrow')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('wo_friday_eyebrow', 'Friday & Saturday Dinner Party')) ?></p>
        <h2><?= e(setting('wo_friday_heading', 'INFORMATION')) ?></h2>
        <p><?= e(setting('wo_friday_p1')) ?></p>
        <p><?= e(setting('wo_friday_p2')) ?></p>
        <?php if (setting('wo_friday_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('wo_friday_btn_url', '#'))) ?>"><?= e(setting('wo_friday_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Weekend Bottomless Brunch --------------------------------------------- -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('wo_brunch_eyebrow', 'Weekend Bottomless Brunch')) ?></p>
        <h2><?= e(setting('wo_brunch_heading', 'INFORMATION')) ?></h2>
        <?php if (setting('wo_brunch_hours')): ?><p class="hours-line"><?= eb(setting('wo_brunch_hours')) ?></p><?php endif; ?>
        <p><?= e(setting('wo_brunch_p1')) ?></p>
        <?php if (setting('wo_brunch_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('wo_brunch_btn_url', '#'))) ?>"><?= e(setting('wo_brunch_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img src="<?= e(asset(setting('wo_brunch_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('wo_brunch_eyebrow')) ?>">
      </div>
    </div>
  </div>
</section>

<!-- See This Week's Line-Up (shared with the Below page) ------------------ -->
<?php include __DIR__ . '/includes/lineup-section.php'; ?>

<!-- FAQs -------------------------------------------------------------------- -->
<?php if ($faqs): ?>
<section class="faq-section reveal">
  <div class="container">
    <h2>FAQs</h2>
    <div class="faq-list">
      <?php foreach ($faqs as $i => $faq): ?>
        <div class="faq-item">
          <button type="button" class="faq-question" data-faq-index="<?= $i ?>">
            <span class="faq-chevron">▾</span>
            <span><?= e($faq['title']) ?></span>
          </button>
          <div class="faq-answer"><p><?= e($faq['body']) ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
(function () {
  document.querySelectorAll('.faq-question').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.faq-item');
      var isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(function (el) { el.classList.remove('open'); });
      if (!isOpen) item.classList.add('open');
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
