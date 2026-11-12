<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('wo_heading', "What's On");
$page_meta_description = setting('wo_lede');

include __DIR__ . '/includes/header.php';

$wo_hero_image = asset(setting('wo_hero_image', 'assets/img/cafe.jpg'));
$wo_hero_video = setting('wo_hero_video');
$wo_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($wo_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$faqs = blocks('whatson_faq');
$features = blocks('wo_feature');
$livelateCards = blocks('wo_livelate_card');
?>

<!-- Hero ------------------------------------------------------------- -->
<?php if (section_visible('wo_hero')): ?>
<section class="hero"<?php if (!$wo_hero_video): ?> style="background-image:url('<?= e($wo_hero_image) ?>');"<?php endif; ?>>
  <?php if ($wo_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($wo_hero_video)) ?>" type="<?= e($wo_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Intro -------------------------------------------------------------- -->
<?php if (section_visible('wo_intro')): ?>
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('wo_heading', "What's On")) ?></h1>
    <p class="subheading"><?= e(setting('wo_subheading')) ?></p>
    <p class="lede"><?= enl(setting('wo_lede')) ?></p>

    <div class="tag-buttons">
      <?php if (setting('wo_tag2_label')): ?><a class="tag-btn" href="<?= e(url(setting('wo_tag2_url', '#'))) ?>"><?= e(setting('wo_tag2_label')) ?></a><?php endif; ?>
      <?php if (setting('wo_tag3_label')): ?><a class="tag-btn" href="<?= e(url(setting('wo_tag3_url', '#'))) ?>"><?= e(setting('wo_tag3_label')) ?></a><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
/**
 * Content sections below the Intro, in their admin-configurable order
 * (admin > What's On > Section Order, admins only - Hero and Intro
 * always stay fixed at the top).
 */
$wo_sections = [
    'wo_features' => function () use ($features): void {
        if (!section_visible('wo_features')) return;
        foreach ($features as $i => $feature): ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <?php if ($i % 2 === 0): ?>
        <div class="split-media">
          <img loading="lazy" decoding="async" src="<?= e(asset($feature['image'] ?: 'assets/img/restaurant.jpg')) ?>" alt="<?= e($feature['title']) ?>">
        </div>
      <?php endif; ?>
      <div class="split-copy">
        <?php if ($feature['subtitle']): ?><p class="eyebrow"><?= e($feature['subtitle']) ?></p><?php endif; ?>
        <h2><?= e($feature['title']) ?></h2>
        <?php foreach (array_filter(array_map('trim', preg_split('/\n\s*\n/', (string) $feature['body']))) as $para): ?>
          <p><?= enl($para) ?></p>
        <?php endforeach; ?>
        <?php if ($feature['link_url2']): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url($feature['link_url'] ?: '#')) ?>"><?= e($feature['link_url2']) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <?php if ($i % 2 !== 0): ?>
        <div class="split-media">
          <img loading="lazy" decoding="async" src="<?= e(asset($feature['image'] ?: 'assets/img/restaurant.jpg')) ?>" alt="<?= e($feature['title']) ?>">
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php
        endforeach;
    },
    'wo_happyhour' => function (): void {
        if (!section_visible('wo_happyhour')) return;
        ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('wo_happyhour_eyebrow', 'Happy Hour')) ?></p>
        <h2><?= e(setting('wo_happyhour_heading', 'INFORMATION')) ?></h2>
        <p><?= enl(setting('wo_happyhour_p1')) ?></p>
        <p><?= enl(setting('wo_happyhour_p2')) ?></p>
        <?php if (setting('wo_happyhour_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('wo_happyhour_btn_url', '#'))) ?>"><?= e(setting('wo_happyhour_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('wo_happyhour_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('wo_happyhour_eyebrow')) ?>">
      </div>
    </div>
  </div>
</section>
<?php
    },
    'wo_friday' => function (): void {
        if (!section_visible('wo_friday')) return;
        ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('wo_friday_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('wo_friday_eyebrow')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('wo_friday_eyebrow', 'Friday & Saturday Dinner Party')) ?></p>
        <h2><?= e(setting('wo_friday_heading', 'INFORMATION')) ?></h2>
        <p><?= enl(setting('wo_friday_p1')) ?></p>
        <p><?= enl(setting('wo_friday_p2')) ?></p>
        <?php if (setting('wo_friday_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('wo_friday_btn_url', '#'))) ?>"><?= e(setting('wo_friday_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php
    },
    'wo_brunch' => function (): void {
        if (!section_visible('wo_brunch')) return;
        ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('wo_brunch_eyebrow', 'Weekend Bottomless Brunch')) ?></p>
        <h2><?= e(setting('wo_brunch_heading', 'INFORMATION')) ?></h2>
        <?php if (setting('wo_brunch_hours')): ?><p class="hours-line"><?= eb(setting('wo_brunch_hours')) ?></p><?php endif; ?>
        <p><?= enl(setting('wo_brunch_p1')) ?></p>
        <?php if (setting('wo_brunch_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('wo_brunch_btn_url', '#'))) ?>"><?= e(setting('wo_brunch_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('wo_brunch_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('wo_brunch_eyebrow')) ?>">
      </div>
    </div>
  </div>
</section>
<?php
    },
    'wo_livelate' => function () use ($livelateCards): void {
        if (!section_visible('wo_livelate')) return;
        ?>
<section class="section reveal">
  <div class="container">
    <h2><?= e(setting('wo_livelate_heading', 'Live & Late')) ?></h2>
    <?php if (setting('wo_livelate_p1')): ?><p><?= enl(setting('wo_livelate_p1')) ?></p><?php endif; ?>

    <?php if ($livelateCards): ?>
      <div class="livelate-cards">
        <?php foreach ($livelateCards as $card): ?>
          <div class="livelate-card">
            <div class="livelate-card-media">
              <?php if ($card['image']): ?>
                <img loading="lazy" decoding="async" src="<?= e(asset($card['image'])) ?>" alt="<?= e($card['title']) ?>">
                <span class="livelate-card-tint"></span>
              <?php else: ?>
                <div class="livelate-card-placeholder">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M9 9l-2-1M17 10l-2 1M9 15l-2 1M17 14l-2-1"/></svg>
                  <span>ARTIST PHOTO</span>
                </div>
              <?php endif; ?>
            </div>
            <div class="livelate-card-info">
              <h3 class="livelate-card-title"><?= e($card['title']) ?></h3>
              <?php if ($card['subtitle']): ?><p class="livelate-card-day"><?= e($card['subtitle']) ?></p><?php endif; ?>
              <?php if ($card['body']): ?><p class="livelate-card-time"><?= e($card['body']) ?></p><?php endif; ?>
              <?php if ($card['link_url']): ?><p class="livelate-card-artist"><?= e($card['link_url']) ?></p><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (setting('wo_livelate_btn_label')): ?>
      <div class="btn-wrap">
        <a class="link-underline" href="<?= e(url(setting('wo_livelate_btn_url', '#lineup-section'))) ?>"><?= e(setting('wo_livelate_btn_label')) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php
    },
    'wo_lineup' => function (): void {
        if (!section_visible('wo_lineup')) return;
        include __DIR__ . '/includes/lineup-section.php';
    },
    'wo_faq' => function () use ($faqs): void {
        if (!$faqs || !section_visible('wo_faq')) return;
        ?>
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
<?php
    },
];
foreach (section_order('whatson', array_keys($wo_sections)) as $wo_key) {
    $wo_sections[$wo_key]();
}
?>

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
