<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('faq_heading', 'Frequently Asked Questions');
$page_meta_description = setting('faq_subheading');

include __DIR__ . '/includes/header.php';

$faq_hero_image = asset(setting('faq_hero_image', 'assets/img/cafe.jpg'));

$faqs = blocks('faq_page_item');
?>

<!-- Hero ------------------------------------------------------------- -->
<section class="hero" style="background-image:url('<?= e($faq_hero_image) ?>');"></section>

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
          <div class="faq-answer">
            <p><?= e($faq['body']) ?></p>
            <?php if ($faq['subtitle'] === '1' && $faq['link_url2'] && $faq['link_url']): ?>
              <div class="btn-wrap">
                <a class="link-underline" href="<?= e(url($faq['link_url'])) ?>"><?= e($faq['link_url2']) ?></a>
              </div>
            <?php endif; ?>
          </div>
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
