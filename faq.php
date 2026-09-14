<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('faq_heading', 'Frequently Asked Questions');
$page_meta_description = setting('faq_subheading');

include __DIR__ . '/includes/header.php';

$faq_hero_image = asset(setting('faq_hero_image', 'assets/img/cafe.jpg'));
$faq_hero_video = setting('faq_hero_video');
$faq_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($faq_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$faqs = blocks('faq_page_item');
?>

<!-- Hero ------------------------------------------------------------- -->
<section class="hero"<?php if (!$faq_hero_video): ?> style="background-image:url('<?= e($faq_hero_image) ?>');"<?php endif; ?>>
  <?php if ($faq_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($faq_hero_video)) ?>" type="<?= e($faq_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>

<!-- FAQs --------------------------------------------------------------- -->
<?php if ($faqs): ?>
<section class="faq-section reveal">
  <div class="container">
    <h1><?= e(setting('faq_heading', 'Frequently Asked Questions')) ?></h1>
    <p class="faq-subheading"><?= e(setting('faq_subheading', 'House Of Bungalow')) ?></p>
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
