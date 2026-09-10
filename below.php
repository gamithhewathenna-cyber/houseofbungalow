<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('below_heading', 'Below');
$page_meta_description = setting('below_lede');

include __DIR__ . '/includes/header.php';

$below_hero_image = asset(setting('below_hero_image', 'assets/img/below.jpg'));
$below_hero_video = setting('below_hero_video');
$below_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($below_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$nights   = blocks('below_night');
$faqs     = blocks('below_faq');

// Group nights into rows of 3 then 2, same masonry-row pattern as the gallery.
$night_rows = [];
$i = 0; $take3 = true;
while ($i < count($nights)) {
    $take = $take3 ? 3 : 2;
    $night_rows[] = array_slice($nights, $i, $take);
    $i += $take;
    $take3 = !$take3;
}
?>

<!-- Hero ------------------------------------------------------------- -->
<section class="hero"<?php if (!$below_hero_video): ?> style="background-image:url('<?= e($below_hero_image) ?>');"<?php endif; ?>>
  <?php if ($below_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($below_hero_video)) ?>" type="<?= e($below_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>

<!-- Intro (maroon band) ----------------------------------------------- -->
<section class="maroon-band reveal">
  <div class="container">
    <h1><?= e(setting('below_heading', 'Below')) ?></h1>
    <p class="subheading"><?= e(setting('below_subheading')) ?></p>
    <p class="lede"><?= e(setting('below_lede')) ?></p>
    <p><?= e(setting('below_intro_p1')) ?></p>
    <p><?= e(setting('below_intro_p2')) ?></p>
    <div class="btn-wrap btn-wrap-row">
      <?php if (setting('below_intro_btn1_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('below_intro_btn1_url', '#'))) ?>"><?= e(setting('below_intro_btn1_label')) ?></a>
      <?php endif; ?>
      <?php if (setting('below_intro_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('below_intro_btn2_url', '#'))) ?>"><?= e(setting('below_intro_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- From Cocktails To Late Night --------------------------------------- -->
<section class="section reveal">
  <div class="container">
    <h2><?= e(setting('below_cocktails_heading', 'From Cocktails To Late Night')) ?></h2>
    <div class="cocktails-copy">
      <p><?= eb(setting('below_cocktails_p1')) ?></p>
      <p><?= eb(setting('below_cocktails_p2')) ?></p>
      <p class="tight"><?= eb(setting('below_cocktails_p3')) ?></p>
      <?php if (setting('below_cocktails_hours')): ?><p class="section-hours"><?= eb(setting('below_cocktails_hours')) ?></p><?php endif; ?>
    </div>

    <div class="trio-grid">
      <div class="trio-item"><img src="<?= e(asset(setting('below_cocktails_image1', 'assets/img/below.jpg'))) ?>" alt=""></div>
      <div class="trio-item"><img src="<?= e(asset(setting('below_cocktails_image2', 'assets/img/below.jpg'))) ?>" alt=""></div>
      <div class="trio-item"><img src="<?= e(asset(setting('below_cocktails_image3', 'assets/img/below.jpg'))) ?>" alt=""></div>
    </div>
  </div>
</section>

<!-- Music (maroon band) ------------------------------------------------ -->
<section class="maroon-band reveal">
  <div class="container">
    <h2><?= e(setting('below_music_heading', 'Music')) ?></h2>
    <p class="lede"><?= e(setting('below_music_lede')) ?></p>

    <?php if ($night_rows): ?>
      <div class="night-grid">
        <?php foreach ($night_rows as $row): ?>
          <div class="night-row night-row-<?= count($row) ?>">
            <?php foreach ($row as $n): ?>
              <div class="night-item">
                <div class="night-item-title"><?= e($n['title']) ?></div>
                <?php if ($n['body']): ?><div class="night-item-desc"><?= e($n['body']) ?></div><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="btn-wrap btn-wrap-row">
      <?php if (setting('below_music_btn1_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('below_music_btn1_url', '#'))) ?>"><?= e(setting('below_music_btn1_label')) ?></a>
      <?php endif; ?>
      <?php if (setting('below_music_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('below_music_btn2_url', '#'))) ?>"><?= e(setting('below_music_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- See This Week's Line-Up (shared with the What's On page) ----------- -->
<?php include __DIR__ . '/includes/lineup-section.php'; ?>

<!-- Guest Artists -------------------------------------------------------- -->
<section class="split-section guest-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img src="<?= e(asset(setting('below_guest_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('below_guest_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('below_guest_eyebrow', 'Guest Artists')) ?></p>
        <h2><?= e(setting('below_guest_heading')) ?></h2>
        <p><?= e(setting('below_guest_p1')) ?></p>
        <p><?= e(setting('below_guest_p2')) ?></p>
        <?php if (setting('below_guest_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('below_guest_btn_url', '#'))) ?>"><?= e(setting('below_guest_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- VIP Tables ------------------------------------------------------------- -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('below_vip_eyebrow', 'VIP Tables')) ?></p>
        <h2><?= e(setting('below_vip_heading')) ?></h2>
        <p><?= e(setting('below_vip_p1')) ?></p>
        <p><?= e(setting('below_vip_p2')) ?></p>
        <?php if (setting('below_vip_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('below_vip_btn_url', '#'))) ?>"><?= e(setting('below_vip_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img src="<?= e(asset(setting('below_vip_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('below_vip_heading')) ?>">
      </div>
    </div>
  </div>
</section>

<!-- Below Hours -------------------------------------------------------------- -->
<section class="hours-block reveal">
  <div class="container">
    <h2><?= e(setting('below_hours_heading', 'Below Hours')) ?></h2>
    <?php if (setting('below_hours_days')): ?><p class="hours-note" style="margin-bottom:10px;"><?= e(setting('below_hours_days')) ?></p><?php endif; ?>
    <p class="hours-line"><strong><?= e(setting('below_hours_cocktail_label', 'Cocktail Lounge')) ?></strong> <?= e(setting('below_hours_cocktail')) ?></p>
    <p class="hours-line"><strong><?= e(setting('below_hours_dj_label', 'DJs')) ?></strong> <?= e(setting('below_hours_dj')) ?></p>
    <?php if (setting('below_hours_note')): ?><p class="hours-footnote" style="margin-top:14px;"><?= e(setting('below_hours_note')) ?></p><?php endif; ?>
  </div>
</section>

<!-- FAQs ------------------------------------------------------------------------ -->
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
