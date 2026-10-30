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

$below_menu_cats = blocks('below_menu_cat');
$below_menu_items_by_cat = [];
foreach ($below_menu_cats as $cat) {
    $below_menu_items_by_cat[$cat['id']] = blocks('below_menu_item_' . $cat['id']);
}

/**
 * Renders one menu category's rows inside its "View ..." expander, using
 * each row's type (set via admin > Below > Menu > Type column): a page
 * title + subtitle, an uppercase section heading, a plain line, a small
 * footnote, or a normal name + price + description.
 */
function render_below_menu_items(array $items): void
{
    if (!$items) {
        ?><p class="cup-dark-dummy">Menu details coming soon.</p><?php
        return;
    }
    foreach ($items as $item) {
        $itemType = $item['link_url'];
        if ($itemType === 'title') {
            ?><h3 class="menu-page-title"><?= e($item['title']) ?></h3><?php
            if ($item['body']) { ?><p class="menu-page-subtitle"><?= e($item['body']) ?></p><?php }
        } elseif ($itemType === 'heading') {
            ?><h4 class="menu-heading"><?= e($item['title']) ?></h4><?php
        } elseif ($itemType === 'line') {
            ?><p class="menu-line"><?= e($item['body']) ?><?php if ($item['subtitle']): ?> <span class="price">$<?= e($item['subtitle']) ?></span><?php endif; ?></p><?php
        } elseif ($itemType === 'note') {
            ?><p class="menu-note"><?= e($item['body']) ?></p><?php
        } else {
            ?>
            <div class="menu-item">
              <div class="menu-item-title">
                <span><?= e($item['title']) ?></span>
                <?php if ($item['subtitle']): ?><span class="price">$<?= e($item['subtitle']) ?></span><?php endif; ?>
              </div>
              <?php if ($item['body']): ?><div class="menu-item-desc"><?= enl($item['body']) ?></div><?php endif; ?>
            </div>
            <?php
        }
    }
}

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
<?php if (section_visible('below_hero')): ?>
<section class="hero"<?php if (!$below_hero_video): ?> style="background-image:url('<?= e($below_hero_image) ?>');"<?php endif; ?>>
  <?php if ($below_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($below_hero_video)) ?>" type="<?= e($below_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
  <?php if (setting('below_opentable_embed')): ?>
    <div class="opentable-overlay"><?= setting('below_opentable_embed') ?></div>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Intro (maroon band) ----------------------------------------------- -->
<?php if (section_visible('below_intro')): ?>
<section class="maroon-band reveal">
  <div class="container">
    <h1><?= e(setting('below_heading', 'Below')) ?></h1>
    <p class="subheading"><?= e(setting('below_subheading')) ?></p>
    <p class="lede"><?= enl(setting('below_lede')) ?></p>
    <p><?= enl(setting('below_intro_p1')) ?></p>
    <p><?= enl(setting('below_intro_p2')) ?></p>
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
<?php endif; ?>

<!-- From Cocktails To Late Night --------------------------------------- -->
<?php if (section_visible('below_cocktails')): ?>
<section class="section reveal">
  <div class="container">
    <h2><?= e(setting('below_cocktails_heading', 'From Cocktails To Late Night')) ?></h2>
    <div class="cocktails-copy">
      <p><?= enl(setting('below_cocktails_p1')) ?></p>
      <p><?= enl(setting('below_cocktails_p2')) ?></p>
      <p class="tight"><?= enl(setting('below_cocktails_p3')) ?></p>
      <?php if (setting('below_cocktails_hours')): ?><p class="section-hours"><?= eb(setting('below_cocktails_hours')) ?></p><?php endif; ?>
    </div>

    <div class="trio-grid">
      <div class="trio-item"><img loading="lazy" decoding="async" src="<?= e(asset(setting('below_cocktails_image1', 'assets/img/below.jpg'))) ?>" alt=""></div>
      <div class="trio-item"><img loading="lazy" decoding="async" src="<?= e(asset(setting('below_cocktails_image2', 'assets/img/below.jpg'))) ?>" alt=""></div>
      <div class="trio-item"><img loading="lazy" decoding="async" src="<?= e(asset(setting('below_cocktails_image3', 'assets/img/below.jpg'))) ?>" alt=""></div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Menu --------------------------------------------------------------- -->
<?php if (section_visible('below_menu')): ?>
<section class="menu-dark reveal">
  <div class="container">
    <h2 class="menu-dark-heading"><?= e(setting('below_menu_heading', 'Discover Our Menus')) ?></h2>
    <?php if (setting('below_menu_lede')): ?>
      <p class="menu-dark-lede"><?= enl(setting('below_menu_lede')) ?></p>
    <?php endif; ?>

    <?php if ($below_menu_cats): ?>
      <div class="menu-dark-tabs">
        <?php $first = true; foreach ($below_menu_cats as $cat): ?>
          <button type="button" class="menu-dark-tab-btn<?= $first ? ' active' : '' ?>" data-below-menu-tab="<?= (int) $cat['id'] ?>"><?= e($cat['title']) ?></button>
        <?php $first = false; endforeach; ?>
      </div>

      <div class="menu-dark-panel-wrap">
        <?php $first = true; foreach ($below_menu_cats as $cat): ?>
          <div class="menu-dark-panel<?= $first ? ' active' : '' ?>" data-below-menu-panel="<?= (int) $cat['id'] ?>">
            <?php render_below_menu_items($below_menu_items_by_cat[$cat['id']]); ?>
            <?php if ($cat['link_url']): ?>
              <div class="menu-dark-download-wrap">
                <a class="tag-btn" href="<?= e(asset($cat['link_url'])) ?>" target="_blank" rel="noopener" download>DOWNLOAD MENU</a>
              </div>
            <?php endif; ?>
          </div>
        <?php $first = false; endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Music (maroon band) ------------------------------------------------ -->
<?php if (section_visible('below_music')): ?>
<section class="maroon-band reveal">
  <div class="container">
    <h2><?= e(setting('below_music_heading', 'Music')) ?></h2>
    <p class="lede"><?= enl(setting('below_music_lede')) ?></p>

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
      <?php if (setting('below_music_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('below_music_btn2_url', '#'))) ?>"><?= e(setting('below_music_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- See This Week's Line-Up (shared with the What's On page) ----------- -->
<?php if (section_visible('below_lineup')): ?>
<?php include __DIR__ . '/includes/lineup-section.php'; ?>
<?php endif; ?>

<!-- Guest Artists -------------------------------------------------------- -->
<?php if (section_visible('below_guest')): ?>
<section class="split-section guest-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('below_guest_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('below_guest_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('below_guest_eyebrow', 'Guest Artists')) ?></p>
        <h2><?= e(setting('below_guest_heading')) ?></h2>
        <p><?= enl(setting('below_guest_p1')) ?></p>
        <p><?= enl(setting('below_guest_p2')) ?></p>
        <?php if (setting('below_guest_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('below_guest_btn_url', '#'))) ?>"><?= e(setting('below_guest_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- VIP Tables ------------------------------------------------------------- -->
<?php if (section_visible('below_vip')): ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('below_vip_eyebrow', 'VIP Tables')) ?></p>
        <h2><?= e(setting('below_vip_heading')) ?></h2>
        <p><?= enl(setting('below_vip_p1')) ?></p>
        <p><?= enl(setting('below_vip_p2')) ?></p>
        <?php if (setting('below_vip_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline js-vipbottle-popup-trigger" href="#"><?= e(setting('below_vip_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('below_vip_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('below_vip_heading')) ?>">
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Below Hours -------------------------------------------------------------- -->
<?php if (section_visible('below_hours')): ?>
<section class="hours-block reveal">
  <div class="container">
    <h2><?= e(setting('below_hours_heading', 'Below Hours')) ?></h2>
    <?php if (setting('below_hours_days')): ?><p class="hours-note" style="margin-bottom:10px;"><?= e(setting('below_hours_days')) ?></p><?php endif; ?>
    <p class="hours-line"><strong><?= e(setting('below_hours_cocktail_label', 'Cocktail Lounge')) ?></strong> <?= e(setting('below_hours_cocktail')) ?></p>
    <p class="hours-line"><strong><?= e(setting('below_hours_dj_label', 'DJs')) ?></strong> <?= e(setting('below_hours_dj')) ?></p>
    <?php if (setting('below_hours_note')): ?><p class="hours-footnote" style="margin-top:14px;"><?= e(setting('below_hours_note')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- FAQs ------------------------------------------------------------------------ -->
<?php if ($faqs && section_visible('below_faq')): ?>
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
          <div class="faq-answer"><p><?= enl($faq['body']) ?></p></div>
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

<script>
(function () {
  var tabs = document.querySelectorAll('.menu-dark-tab-btn');
  var panels = document.querySelectorAll('.menu-dark-panel');
  if (!tabs.length) return;
  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (b) { b.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('active'); });
      btn.classList.add('active');
      var panel = document.querySelector('.menu-dark-panel[data-below-menu-panel="' + btn.dataset.belowMenuTab + '"]');
      if (panel) panel.classList.add('active');
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
