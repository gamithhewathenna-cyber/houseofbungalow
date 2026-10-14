<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('mcup_heading', 'Melbourne Cup At House Of Bungalow');
$page_meta_description = setting('mcup_lede');

include __DIR__ . '/includes/header.php';

$mcup_hero_image = asset(setting('mcup_hero_image', 'assets/img/restaurant.jpg'));
$mcup_hero_video = setting('mcup_hero_video');
$mcup_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($mcup_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$rest_menu_cats = blocks('mcup_rest_menu_cat');
$rest_menu_items_by_cat = [];
foreach ($rest_menu_cats as $cat) {
    $rest_menu_items_by_cat[$cat['id']] = blocks('mcup_rest_menu_item_' . $cat['id']);
}

$below_menu_cats = blocks('mcup_below_menu_cat');
$below_menu_items_by_cat = [];
foreach ($below_menu_cats as $cat) {
    $below_menu_items_by_cat[$cat['id']] = blocks('mcup_below_menu_item_' . $cat['id']);
}

/** Small inline icon set used by the Cup Day menu browser. */
function cup_icon(string $name): void
{
    $icons = [
        'fork'          => '<path d="M6 2v8a2 2 0 0 0 4 0V2M8 2v20M18 2c-2 1.5-2 4-2 6 0 2.5 1 3 2 3.5V22"/>',
        'glass'         => '<path d="M5 3h14l-2.2 9.2a4.8 4.8 0 0 1-9.6 0L5 3Z"/><path d="M12 15v6M8 21h8"/>',
        'canape'        => '<path d="M12 21s7-6 7-13a7 7 0 0 0-7-7 7 7 0 0 0-7 7c0 7 7 13 7 13Z"/><path d="M12 12v9"/>',
        'cocktail'      => '<path d="M4 4h16l-8 9-8-9Z"/><path d="M12 13v7M8 20h8"/>',
        'chevron-right' => '<path d="M9 6l6 6-6 6"/>',
        'arrow'         => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($icons[$name] ?? '') . '</svg>';
}

/**
 * Renders the "Menu" browser tab: a left-hand list of course categories and
 * a right-hand detail panel, one category visible at a time (JS-toggled).
 * A category's optional "subtitle" shows as a small italic note under its
 * heading (e.g. "Served as an alternate drop.").
 */
function render_menu_browser(array $cats, array $itemsByCat, string $footnoteSettingKey = ''): void
{
    if (!$cats) {
        return;
    }
    ?>
    <div class="cup-browser-menu">
      <div class="cup-browser-menu-nav">
        <?php foreach ($cats as $i => $cat): ?>
          <button type="button" class="cup-browser-cat-btn<?= $i === 0 ? ' active' : '' ?>" data-cat="<?= $cat['id'] ?>">
            <span><?= e($cat['title']) ?></span>
            <?php cup_icon('chevron-right'); ?>
          </button>
        <?php endforeach; ?>
      </div>
      <div class="cup-browser-menu-detail">
        <?php foreach ($cats as $i => $cat): ?>
          <div class="cup-browser-cat-panel<?= $i === 0 ? ' active' : '' ?>" data-cat-panel="<?= $cat['id'] ?>">
            <h3><?= e($cat['title']) ?></h3>
            <?php if ($cat['subtitle']): ?><p class="cup-browser-cat-note"><?= e($cat['subtitle']) ?></p><?php endif; ?>
            <hr class="cup-browser-divider">
            <?php foreach ($itemsByCat[$cat['id']] as $item): ?>
              <p class="cup-browser-item"><?= e($item['title']) ?></p>
              <?php if ($item['body']): ?><p class="cup-browser-item-desc"><?= enl($item['body']) ?></p><?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if ($footnoteSettingKey && setting($footnoteSettingKey)): ?>
      <p class="cup-browser-menu-footnote"><?= enl(setting($footnoteSettingKey)) ?></p>
    <?php endif; ?>
    <?php
}

/** One "label | value" row inside a beverage/drinks card. */
function render_bev_row(string $label, string $value): void
{
    if ($value === '') {
        return;
    }
    ?>
    <div class="cup-bev-row">
      <span class="cup-bev-row-label"><?= e($label) ?></span>
      <span class="cup-bev-row-value"><?= enl($value) ?></span>
    </div>
    <?php
}

/**
 * Renders the rows for a beverage package whose setting value holds one
 * "Label | Value" pair per line (admin-editable as plain text).
 */
function render_bev_rows(string $settingKey): void
{
    $lines = preg_split('/\r?\n/', trim(setting($settingKey)));
    foreach ($lines as $line) {
        if (trim($line) === '') {
            continue;
        }
        $parts = explode('|', $line, 2);
        render_bev_row(trim($parts[0] ?? ''), trim($parts[1] ?? ''));
    }
}
?>

<!-- Hero ------------------------------------------------------------- -->
<?php if (section_visible('mcup_hero')): ?>
<section class="hero"<?php if (!$mcup_hero_video): ?> style="background-image:url('<?= e($mcup_hero_image) ?>');"<?php endif; ?>>
  <?php if ($mcup_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($mcup_hero_video)) ?>" type="<?= e($mcup_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Top Intro - dual CTA ------------------------------------------------- -->
<?php if (section_visible('mcup_intro')): ?>
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('mcup_heading', 'Melbourne Cup At House Of Bungalow')) ?></h1>
    <p class="subheading"><?= e(setting('mcup_subheading')) ?></p>
    <p class="lede"><?= e(setting('mcup_lede')) ?></p>
    <p><?= enl(setting('mcup_p1')) ?></p>
    <p><?= enl(setting('mcup_p2')) ?></p>
    <p><?= enl(setting('mcup_p3')) ?></p>
    <?php if (setting('mcup_date')): ?><p class="hours-line" style="text-align:center;"><?= e(setting('mcup_date')) ?></p><?php endif; ?>
    <div class="btn-wrap btn-wrap-row">
      <?php if (setting('mcup_btn1_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('mcup_btn1_url', '#'))) ?>"><?= e(setting('mcup_btn1_label')) ?></a>
      <?php endif; ?>
      <?php if (setting('mcup_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('mcup_btn2_url', '#'))) ?>"><?= e(setting('mcup_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Melbourne Cup At The Restaurant - Beverage Packages (Menu temporarily hidden) -->
<?php if (section_visible('mcup_restaurant')): ?>
<section class="cup-offer-v2 reveal">
  <div class="container">
    <div class="cup-offer-v2-head">
      <p class="cup-offer-v2-eyebrow"><?= e(setting('mcup_rest_eyebrow', 'Melbourne Cup At')) ?></p>
      <h2><?= e(setting('mcup_rest_heading', 'The Restaurant')) ?></h2>
      <p class="cup-offer-v2-subheading"><?= e(setting('mcup_rest_subheading')) ?></p>
      <p class="cup-offer-v2-lede"><?= e(setting('mcup_rest_lede')) ?></p>
      <p class="cup-offer-v2-copy"><?= enl(setting('mcup_rest_p1')) ?></p>
      <p class="cup-offer-v2-copy"><?= enl(setting('mcup_rest_p2')) ?></p>
      <?php if (setting('mcup_rest_date')): ?><p class="cup-offer-v2-date"><?= e(setting('mcup_rest_date')) ?></p><?php endif; ?>
      <hr class="cup-offer-v2-rule">
      <?php if (setting('mcup_rest_price_eyebrow')): ?><p class="cup-offer-v2-price-eyebrow"><?= e(setting('mcup_rest_price_eyebrow')) ?></p><?php endif; ?>
      <?php if (setting('mcup_rest_price')): ?><p class="cup-offer-v2-price"><?= e(setting('mcup_rest_price')) ?></p><?php endif; ?>
      <?php if (setting('mcup_rest_price_note')): ?><p class="cup-offer-v2-price-note"><?= e(setting('mcup_rest_price_note')) ?></p><?php endif; ?>
    </div>

    <div class="cup-browser-panel active" data-browser-panel="bev">
      <?php if (setting('mcup_rest_bev_intro')): ?><p class="cup-bev-intro"><?= enl(setting('mcup_rest_bev_intro')) ?></p><?php endif; ?>
      <div class="cup-bev-grid-v2">
        <div class="cup-bev-card">
          <p class="cup-bev-card-label"><?= e(setting('mcup_rest_bev_standard_heading', 'Standard')) ?></p>
          <?php if (setting('mcup_rest_bev_standard_price')): ?><p class="cup-bev-card-price"><?= e(setting('mcup_rest_bev_standard_price')) ?></p><?php endif; ?>
          <?php render_bev_rows('mcup_rest_bev_standard_items'); ?>
        </div>
        <div class="cup-bev-card">
          <p class="cup-bev-card-label"><?= e(setting('mcup_rest_bev_premium_heading', 'Premium')) ?></p>
          <?php if (setting('mcup_rest_bev_premium_price')): ?><p class="cup-bev-card-price"><?= e(setting('mcup_rest_bev_premium_price')) ?></p><?php endif; ?>
          <?php render_bev_rows('mcup_rest_bev_premium_items'); ?>
        </div>
      </div>
      <?php if (setting('mcup_rest_bev_note1')): ?><p class="cup-bev-footnote"><?= e(setting('mcup_rest_bev_note1')) ?></p><?php endif; ?>
    </div>

    <?php if (setting('mcup_rest_btn_label')): ?>
      <div class="cup-offer-v2-btn-wrap">
        <a class="cup-offer-v2-btn" href="<?= e(url(setting('mcup_rest_btn_url', '#'))) ?>">
          <?= e(setting('mcup_rest_btn_label')) ?>
          <?php cup_icon('arrow'); ?>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Melbourne Cup At Below - Drinks Package (Menu temporarily hidden) -->
<?php if (section_visible('mcup_below')): ?>
<section class="cup-offer-v2 reveal">
  <div class="container">
    <div class="cup-offer-v2-head">
      <p class="cup-offer-v2-eyebrow"><?= e(setting('mcup_below_eyebrow', 'Melbourne Cup At')) ?></p>
      <h2><?= e(setting('mcup_below_heading', 'Below')) ?></h2>
      <p class="cup-offer-v2-subheading"><?= e(setting('mcup_below_subheading')) ?></p>
      <p class="cup-offer-v2-lede"><?= e(setting('mcup_below_lede')) ?></p>
      <p class="cup-offer-v2-copy"><?= enl(setting('mcup_below_p1')) ?></p>
      <p class="cup-offer-v2-copy"><?= enl(setting('mcup_below_p2')) ?></p>
      <?php if (setting('mcup_below_date')): ?><p class="cup-offer-v2-date"><?= e(setting('mcup_below_date')) ?></p><?php endif; ?>
      <hr class="cup-offer-v2-rule">
      <?php if (setting('mcup_below_price_eyebrow')): ?><p class="cup-offer-v2-price-eyebrow"><?= e(setting('mcup_below_price_eyebrow')) ?></p><?php endif; ?>
      <?php if (setting('mcup_below_price')): ?><p class="cup-offer-v2-price"><?= e(setting('mcup_below_price')) ?></p><?php endif; ?>
    </div>

    <div class="cup-browser-panel active" data-browser-panel="bev">
      <?php if (setting('mcup_below_drinks_lede')): ?><p class="cup-bev-intro"><?= e(setting('mcup_below_drinks_lede', 'Champagne, cocktails and a considered selection of wines.')) ?></p><?php endif; ?>
      <div class="cup-bev-grid-v2 single">
        <div class="cup-bev-card">
          <?php
            render_bev_row('Champagne', setting('mcup_below_drinks_champagne_items'));
            render_bev_row('White Wine', setting('mcup_below_drinks_white_items'));
            render_bev_row('Rosé', setting('mcup_below_drinks_rose_items'));
            render_bev_row('Red Wine', setting('mcup_below_drinks_red_items'));
            render_bev_row('Beer', setting('mcup_below_drinks_beer_items'));
            render_bev_row('Signature Cocktails', setting('mcup_below_drinks_cocktails_items'));
          ?>
        </div>
      </div>
    </div>

    <?php if (setting('mcup_below_btn_label')): ?>
      <div class="cup-offer-v2-btn-wrap">
        <a class="cup-offer-v2-btn" href="<?= e(url(setting('mcup_below_btn_url', '#'))) ?>">
          <?= e(setting('mcup_below_btn_label')) ?>
          <?php cup_icon('arrow'); ?>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Two Experiences. One House. ------------------------------------------- -->
<?php if (section_visible('mcup_summary')): ?>
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('mcup_summary_heading', 'Two Experiences. One House.')) ?></h2>
    <p class="subheading" style="text-align:center;"><?= e(setting('mcup_summary_subheading')) ?></p>
    <p><?= enl(setting('mcup_summary_p1')) ?></p>
    <?php if (setting('mcup_summary_date')): ?><p class="hours-line" style="text-align:center;"><?= e(setting('mcup_summary_date')) ?></p><?php endif; ?>

    <div class="cup-summary-grid">
      <div class="cup-summary-card">
        <h3>The Restaurant</h3>
        <?php if (setting('mcup_summary_rest_detail1')): ?><p><?= e(setting('mcup_summary_rest_detail1')) ?></p><?php endif; ?>
        <?php if (setting('mcup_summary_rest_detail2')): ?><p><?= e(setting('mcup_summary_rest_detail2')) ?></p><?php endif; ?>
        <div class="btn-wrap">
          <?php if (setting('mcup_summary_rest_btn_label')): ?>
            <a class="link-underline" href="<?= e(url(setting('mcup_summary_rest_btn_url', '#'))) ?>"><?= e(setting('mcup_summary_rest_btn_label')) ?></a>
          <?php endif; ?>
        </div>
      </div>
      <div class="cup-summary-card">
        <h3>Below</h3>
        <?php if (setting('mcup_summary_below_detail1')): ?><p><?= e(setting('mcup_summary_below_detail1')) ?></p><?php endif; ?>
        <div class="btn-wrap">
          <?php if (setting('mcup_summary_below_btn_label')): ?>
            <a class="link-underline" href="<?= e(url(setting('mcup_summary_below_btn_url', '#'))) ?>"><?= e(setting('mcup_summary_below_btn_label')) ?></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
(function () {
  document.querySelectorAll('.cup-offer-v2').forEach(function (offer) {
    var browserTabs = offer.querySelectorAll('.cup-browser-tab');
    browserTabs.forEach(function (btn) {
      btn.addEventListener('click', function () {
        browserTabs.forEach(function (b) { b.classList.remove('active'); });
        offer.querySelectorAll('.cup-browser-panel').forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        var panel = offer.querySelector('.cup-browser-panel[data-browser-panel="' + btn.dataset.browserTab + '"]');
        if (panel) panel.classList.add('active');
      });
    });

    var catBtns = offer.querySelectorAll('.cup-browser-cat-btn');
    catBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        catBtns.forEach(function (b) { b.classList.remove('active'); });
        offer.querySelectorAll('.cup-browser-cat-panel').forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        var panel = offer.querySelector('.cup-browser-cat-panel[data-cat-panel="' + btn.dataset.cat + '"]');
        if (panel) panel.classList.add('active');
      });
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
