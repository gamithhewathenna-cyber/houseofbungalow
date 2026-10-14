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

/**
 * Renders a stacked (non-tabbed) set-menu: every category shown at once,
 * each with its dishes underneath - used for the Restaurant/Below fixed
 * Cup Day menus, which aren't user-selectable like the à la carte menus.
 * A category's optional "subtitle" is shown as a small italic note under
 * its heading (e.g. "Served as an alternate drop.").
 */
function render_set_menu(array $cats, array $itemsByCat): void
{
    ?>
    <div class="set-menu">
      <?php foreach ($cats as $cat): ?>
        <div class="set-menu-category">
          <p class="set-menu-category-heading"><?= e($cat['title']) ?></p>
          <?php if ($cat['subtitle']): ?><p class="set-menu-category-note"><?= e($cat['subtitle']) ?></p><?php endif; ?>
          <?php foreach ($itemsByCat[$cat['id']] as $item): ?>
            <div class="menu-item">
              <div class="menu-item-title">
                <span><?= e($item['title']) ?></span>
              </div>
              <?php if ($item['body']): ?><div class="menu-item-desc"><?= enl($item['body']) ?></div><?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Renders a short admin-editable list (one item per line) as bullet points.
 */
function render_bev_list(string $settingKey): void
{
    $lines = preg_split('/\r?\n/', trim(setting($settingKey)));
    ?>
    <ul>
      <?php foreach ($lines as $line): if (trim($line) === '') continue; ?>
        <li><?= e(trim($line)) ?></li>
      <?php endforeach; ?>
    </ul>
    <?php
}

/** Small inline icon set used by the Cup Day accordion headers. */
function cup_icon(string $name): void
{
    $icons = [
        'fork'     => '<path d="M6 2v8a2 2 0 0 0 4 0V2M8 2v20M18 2c-2 1.5-2 4-2 6 0 2.5 1 3 2 3.5V22"/>',
        'glass'    => '<path d="M5 3h14l-2.2 9.2a4.8 4.8 0 0 1-9.6 0L5 3Z"/><path d="M12 15v6M8 21h8"/>',
        'canape'   => '<path d="M12 21s7-6 7-13a7 7 0 0 0-7-7 7 7 0 0 0-7 7c0 7 7 13 7 13Z"/><path d="M12 12v9"/>',
        'cocktail' => '<path d="M4 4h16l-8 9-8-9Z"/><path d="M12 13v7M8 20h8"/>',
        'chevron'  => '<path d="M6 9l6 6 6-6"/>',
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . ($icons[$name] ?? '') . '</svg>';
}

/** One collapsible accordion section inside a Cup Day offer card. */
function render_cup_accordion(string $icon, string $label, bool $openByDefault, callable $renderContent): void
{
    ?>
    <div class="cup-accordion<?= $openByDefault ? ' open' : '' ?>">
      <button type="button" class="cup-accordion-header">
        <span class="cup-accordion-icon"><?php cup_icon($icon); ?></span>
        <span class="cup-accordion-label"><?= e($label) ?></span>
        <span class="cup-accordion-chevron"><?php cup_icon('chevron'); ?></span>
      </button>
      <div class="cup-accordion-panel">
        <div class="cup-accordion-panel-inner">
          <?php $renderContent(); ?>
        </div>
      </div>
    </div>
    <?php
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

<!-- Melbourne Cup - Restaurant vs Below, side by side with expandable menus -->
<?php if (section_visible('mcup_restaurant') || section_visible('mcup_below')): ?>
<section class="cup-compare reveal">
  <div class="container">
    <div class="cup-compare-grid">

      <?php if (section_visible('mcup_restaurant')): ?>
      <div class="cup-offer">
        <div class="cup-offer-media">
          <img src="<?= e(asset(setting('mcup_rest_image', 'assets/img/restaurant.jpg'))) ?>" alt="">
        </div>
        <div class="cup-offer-body">
          <p class="cup-offer-eyebrow"><?= e(setting('mcup_rest_eyebrow', 'Melbourne Cup At')) ?></p>
          <h2><?= e(setting('mcup_rest_heading', 'The Restaurant')) ?></h2>
          <p class="cup-offer-subheading"><?= e(setting('mcup_rest_subheading')) ?></p>
          <p class="cup-offer-lede"><?= e(setting('mcup_rest_lede')) ?></p>
          <p><?= enl(setting('mcup_rest_p1')) ?></p>
          <p><?= enl(setting('mcup_rest_p2')) ?></p>
          <?php if (setting('mcup_rest_date')): ?><p class="cup-offer-date"><?= e(setting('mcup_rest_date')) ?></p><?php endif; ?>
          <?php if (setting('mcup_rest_price')): ?>
            <p class="cup-offer-price">
              <?= e(setting('mcup_rest_price')) ?>
            </p>
          <?php endif; ?>
          <?php if (setting('mcup_rest_price_note')): ?><p class="cup-offer-price-note"><?= e(setting('mcup_rest_price_note')) ?></p><?php endif; ?>
          <?php if (setting('mcup_rest_btn_label')): ?>
            <a class="cup-offer-btn" href="<?= e(url(setting('mcup_rest_btn_url', '#'))) ?>">
              <?= e(setting('mcup_rest_btn_label')) ?>
              <?php cup_icon('arrow'); ?>
            </a>
          <?php endif; ?>
        </div>

        <?php if ($rest_menu_cats): render_cup_accordion('fork', 'The Menu', true, function () use ($rest_menu_cats, $rest_menu_items_by_cat) { ?>
          <?php render_set_menu($rest_menu_cats, $rest_menu_items_by_cat); ?>
          <?php if (setting('mcup_rest_bev_note2')): ?><p class="cup-accordion-note"><?= enl(setting('mcup_rest_bev_note2')) ?></p><?php endif; ?>
        <?php }); endif; ?>

        <?php render_cup_accordion('glass', 'Beverage Packages', false, function () { ?>
          <?php if (setting('mcup_rest_bev_intro')): ?><p class="cup-accordion-intro"><?= enl(setting('mcup_rest_bev_intro')) ?></p><?php endif; ?>
          <div class="bev-grid">
            <div class="bev-package">
              <p class="bev-package-heading"><?= e(setting('mcup_rest_bev_standard_heading', 'Standard')) ?></p>
              <?php render_bev_list('mcup_rest_bev_standard_items'); ?>
            </div>
            <div class="bev-package">
              <p class="bev-package-heading"><?= e(setting('mcup_rest_bev_premium_heading', 'Premium')) ?></p>
              <?php render_bev_list('mcup_rest_bev_premium_items'); ?>
            </div>
          </div>
          <?php if (setting('mcup_rest_bev_note1')): ?><p class="cup-accordion-note" style="margin-top:20px;"><?= e(setting('mcup_rest_bev_note1')) ?></p><?php endif; ?>
        <?php }); ?>
      </div>
      <?php endif; ?>

      <?php if (section_visible('mcup_below')): ?>
      <div class="cup-offer">
        <div class="cup-offer-media">
          <img src="<?= e(asset(setting('mcup_below_image', 'assets/img/below.jpg'))) ?>" alt="">
        </div>
        <div class="cup-offer-body">
          <p class="cup-offer-eyebrow"><?= e(setting('mcup_below_eyebrow', 'Melbourne Cup At')) ?></p>
          <h2><?= e(setting('mcup_below_heading', 'Below')) ?></h2>
          <p class="cup-offer-subheading"><?= e(setting('mcup_below_subheading')) ?></p>
          <p class="cup-offer-lede"><?= e(setting('mcup_below_lede')) ?></p>
          <p><?= enl(setting('mcup_below_p1')) ?></p>
          <p><?= enl(setting('mcup_below_p2')) ?></p>
          <?php if (setting('mcup_below_date')): ?><p class="cup-offer-date"><?= e(setting('mcup_below_date')) ?></p><?php endif; ?>
          <?php if (setting('mcup_below_price')): ?>
            <p class="cup-offer-price">
              <?= e(setting('mcup_below_price')) ?>
            </p>
          <?php endif; ?>
          <?php if (setting('mcup_below_btn_label')): ?>
            <a class="cup-offer-btn" href="<?= e(url(setting('mcup_below_btn_url', '#'))) ?>">
              <?= e(setting('mcup_below_btn_label')) ?>
              <?php cup_icon('arrow'); ?>
            </a>
          <?php endif; ?>
        </div>

        <?php if ($below_menu_cats): render_cup_accordion('canape', 'The Canapé Menu', true, function () use ($below_menu_cats, $below_menu_items_by_cat) { ?>
          <?php render_set_menu($below_menu_cats, $below_menu_items_by_cat); ?>
        <?php }); endif; ?>

        <?php render_cup_accordion('cocktail', 'The Drinks Package', false, function () { ?>
          <?php if (setting('mcup_below_drinks_lede')): ?><p class="cup-accordion-intro"><?= e(setting('mcup_below_drinks_lede', 'Champagne, cocktails and a considered selection of wines.')) ?></p><?php endif; ?>
          <div class="bev-grid drinks-grid">
            <div class="bev-package">
              <p class="bev-package-heading">Champagne</p>
              <?php render_bev_list('mcup_below_drinks_champagne_items'); ?>
            </div>
            <div class="bev-package">
              <p class="bev-package-heading">White Wine</p>
              <?php render_bev_list('mcup_below_drinks_white_items'); ?>
            </div>
            <div class="bev-package">
              <p class="bev-package-heading">Rosé</p>
              <?php render_bev_list('mcup_below_drinks_rose_items'); ?>
            </div>
            <div class="bev-package">
              <p class="bev-package-heading">Red Wine</p>
              <?php render_bev_list('mcup_below_drinks_red_items'); ?>
            </div>
            <div class="bev-package">
              <p class="bev-package-heading">Beer</p>
              <?php render_bev_list('mcup_below_drinks_beer_items'); ?>
            </div>
            <div class="bev-package">
              <p class="bev-package-heading">Signature Cocktails</p>
              <?php render_bev_list('mcup_below_drinks_cocktails_items'); ?>
            </div>
          </div>
        <?php }); ?>
      </div>
      <?php endif; ?>

    </div>
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
  document.querySelectorAll('.cup-accordion-header').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var acc = btn.closest('.cup-accordion');
      acc.classList.toggle('open');
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
