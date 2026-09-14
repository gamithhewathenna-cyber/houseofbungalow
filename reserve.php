<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('reserve_hero_heading', 'Reserve');
$page_meta_description = setting('reserve_hero_p1');

include __DIR__ . '/includes/header.php';

$reserve_slides = blocks('reserve_hero_slide');
if (!$reserve_slides) {
    $reserve_slides = [['image' => 'assets/img/cafe.jpg']];
}
?>

<!-- Hero slider (with centered overlay card) --------------------------- -->
<section class="hero hero-slider">
  <?php foreach ($reserve_slides as $i => $slide): ?>
    <div class="hero-slide<?= $i === 0 ? ' active' : '' ?>" style="background-image:url('<?= e(asset($slide['image'])) ?>');"></div>
  <?php endforeach; ?>
  <?php if (count($reserve_slides) > 1): ?>
    <button type="button" class="hero-arrow hero-arrow-prev" aria-label="Previous photo">&#8249;</button>
    <button type="button" class="hero-arrow hero-arrow-next" aria-label="Next photo">&#8250;</button>
  <?php endif; ?>
  <div class="hero-overlay-card">
    <h1><?= e(setting('reserve_hero_heading', 'Reserve Your Night At The House.')) ?></h1>
    <p class="subheading"><?= e(setting('reserve_hero_subheading')) ?></p>
    <p><?= e(setting('reserve_hero_p1')) ?></p>
    <p><?= e(setting('reserve_hero_p2')) ?></p>
    <?php if (setting('reserve_hero_btn_label')): ?>
      <div class="btn-wrap">
        <a class="link-underline" href="<?= e(url(setting('reserve_hero_btn_url', '#happyhour'))) ?>"><?= e(setting('reserve_hero_btn_label')) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if (count($reserve_slides) > 1): ?>
<script>
(function () {
  var slides = document.querySelectorAll('.hero-slider .hero-slide');
  var prev = document.querySelector('.hero-slider .hero-arrow-prev');
  var next = document.querySelector('.hero-slider .hero-arrow-next');
  if (!slides.length) return;
  var current = 0;
  function show(index) {
    slides[current].classList.remove('active');
    current = (index + slides.length) % slides.length;
    slides[current].classList.add('active');
  }
  if (prev) prev.addEventListener('click', function () { show(current - 1); });
  if (next) next.addEventListener('click', function () { show(current + 1); });
})();
</script>
<?php endif; ?>

<!-- Reservations heading ------------------------------------------------ -->
<section class="section reveal">
  <div class="container">
    <h2><?= e(setting('reserve_heading', 'Reservations')) ?></h2>
  </div>
</section>

<!-- Friday & Saturday ----------------------------------------------------- -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('reserve_fridaysat_eyebrow', 'Friday & Saturday')) ?></p>
        <h2><?= e(setting('reserve_fridaysat_heading')) ?></h2>
        <p><?= e(setting('reserve_fridaysat_p1')) ?></p>
        <p><?= e(setting('reserve_fridaysat_p2')) ?></p>
        <?php if (setting('reserve_fridaysat_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('reserve_fridaysat_btn_url', '#'))) ?>"><?= e(setting('reserve_fridaysat_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img src="<?= e(asset(setting('reserve_fridaysat_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('reserve_fridaysat_heading')) ?>">
      </div>
    </div>
  </div>
</section>

<!-- Happy Hour -------------------------------------------------------------- -->
<section id="happyhour" class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img src="<?= e(asset(setting('reserve_happyhour_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('reserve_happyhour_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('reserve_happyhour_eyebrow', 'Happy Hour')) ?></p>
        <h2><?= e(setting('reserve_happyhour_heading')) ?></h2>
        <p><?= e(setting('reserve_happyhour_p1')) ?></p>
        <?php if (setting('reserve_happyhour_hours1')): ?><p class="hours-line"><?= eb(setting('reserve_happyhour_hours1')) ?></p><?php endif; ?>
        <?php if (setting('reserve_happyhour_hours2')): ?><p class="hours-line"><?= eb(setting('reserve_happyhour_hours2')) ?></p><?php endif; ?>
        <?php if (setting('reserve_happyhour_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('reserve_happyhour_btn_url', '#'))) ?>"><?= e(setting('reserve_happyhour_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- VIP Tables ------------------------------------------------------------------ -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('reserve_vip_eyebrow', 'VIP Tables')) ?></p>
        <h2><?= e(setting('reserve_vip_heading')) ?></h2>
        <p><?= e(setting('reserve_vip_p1')) ?></p>
        <p><?= e(setting('reserve_vip_p2')) ?></p>
        <?php if (setting('reserve_vip_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('reserve_vip_btn_url', '#'))) ?>"><?= e(setting('reserve_vip_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img src="<?= e(asset(setting('reserve_vip_image', 'assets/img/hero.jpg'))) ?>" alt="<?= e(setting('reserve_vip_heading')) ?>">
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
