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

$themenu_cats = blocks('mcup_themenu_cat');
$themenu_items_by_cat = [];
foreach ($themenu_cats as $cat) {
    $themenu_items_by_cat[$cat['id']] = blocks('mcup_themenu_item_' . $cat['id']);
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
 * Renders one menu column: every category stacked top to bottom with its
 * dishes underneath - used for the two-column "The Menu" section (Restaurant
 * menu in one column, Below menu in the other). A category's optional
 * "subtitle" shows as a small italic note under its heading (e.g. "Served
 * as an alternate drop.").
 */
function render_menu_stack(array $cats, array $itemsByCat): void
{
    if (!$cats) {
        echo '<p class="cup-menu-empty">Menu coming soon.</p>';
        return;
    }
    foreach ($cats as $i => $cat) {
        render_mini_accordion('cup-menu-category', 'cup-menu-category-heading', e($cat['title']), $i === 0, function () use ($cat, $itemsByCat) {
            if ($cat['subtitle']) {
                ?><p class="cup-menu-category-note"><?= e($cat['subtitle']) ?></p><?php
            }
            foreach ($itemsByCat[$cat['id']] as $item) {
                ?><p class="cup-menu-item"><?= e($item['title']) ?><?php if ($item['body']): ?>, <?= enl($item['body']) ?><?php endif; ?></p><?php
            }
        });
    }
}

/**
 * Small expand/collapse accordion shared by menu categories and beverage
 * packages: a clickable heading with a chevron that toggles the content
 * below it. `$wrapperClass` controls spacing, `$headingClass` controls the
 * heading's font styling (category headings vs beverage package headings).
 */
function render_mini_accordion(string $wrapperClass, string $headingClass, string $headingHtml, bool $openByDefault, callable $renderContent): void
{
    ?>
    <div class="<?= e($wrapperClass) ?> cup-mini-accordion<?= $openByDefault ? ' open' : '' ?>">
      <button type="button" class="cup-mini-accordion-toggle">
        <span class="<?= e($headingClass) ?>"><?= $headingHtml ?></span>
        <span class="cup-mini-accordion-chevron"><?php cup_icon('chevron-right'); ?></span>
      </button>
      <div class="cup-mini-accordion-panel">
        <div class="cup-mini-accordion-panel-inner">
          <?php $renderContent(); ?>
        </div>
      </div>
    </div>
    <?php
}

/**
 * Renders a simple bullet list for a setting whose value holds one drink
 * per line (used for the Beverage Packages block inside "The Menu" column).
 */
function render_bev_list(string $settingKey): void
{
    $lines = preg_split('/\r?\n/', trim(setting($settingKey)));
    ?>
    <ul class="cup-menu-bev-list">
      <?php foreach ($lines as $line): if (trim($line) === '') continue; ?>
        <li><?= e(trim($line)) ?></li>
      <?php endforeach; ?>
    </ul>
    <?php
}

/**
 * One full-width bordered expander button on the dark Restaurant/Below
 * menu section - click toggles the panel underneath it open/closed.
 */
function render_dark_expander(string $label, callable $renderContent): void
{
    ?>
    <div class="cup-dark-expander">
      <button type="button" class="cup-dark-expander-toggle">
        <span><?= e($label) ?></span>
        <span class="cup-dark-expander-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        </span>
      </button>
      <div class="cup-dark-expander-panel">
        <div class="cup-dark-expander-panel-inner">
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
      <?php if (setting('mcup_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('mcup_btn2_url', '#'))) ?>"><?= e(setting('mcup_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Melbourne Cup - dark expander menu: At The Restaurant / At Below -->
<?php if (section_visible('mcup_menus')): ?>
<section class="cup-dark-menu reveal">
  <div class="container">

    <?php if (section_visible('mcup_restaurant')): ?>
    <div class="cup-dark-offer">
      <p class="cup-dark-eyebrow"><?= e(trim(setting('mcup_rest_eyebrow', 'Melbourne Cup At') . ' ' . setting('mcup_rest_heading', 'The Restaurant'))) ?></p>
      <h2 class="cup-dark-heading"><?= e(setting('mcup_rest_heading', 'The Restaurant')) ?></h2>
      <?php $restSub = array_filter([setting('mcup_rest_price_eyebrow'), setting('mcup_rest_price'), setting('mcup_rest_price_note')], 'strlen'); ?>
      <?php if ($restSub): ?><p class="cup-dark-subtext"><?= e(implode(' · ', $restSub)) ?></p><?php endif; ?>

      <?php render_dark_expander(setting('mcup_rest_expander1_label', 'View Three-Course Menu'), function () { ?>
        <p class="cup-dark-dummy">Menu details coming soon.</p>
      <?php }); ?>
      <?php render_dark_expander(setting('mcup_rest_expander2_label', 'View Beverage Packages'), function () { ?>
        <p class="cup-dark-dummy">Beverage package details coming soon.</p>
      <?php }); ?>
    </div>
    <?php endif; ?>

    <?php if (section_visible('mcup_below')): ?>
    <div class="cup-dark-offer">
      <p class="cup-dark-eyebrow"><?= e(trim(setting('mcup_below_eyebrow', 'Melbourne Cup At') . ' ' . setting('mcup_below_heading', 'Below'))) ?></p>
      <h2 class="cup-dark-heading"><?= e(setting('mcup_below_heading', 'Below')) ?></h2>
      <?php $belowSub = array_filter([setting('mcup_below_price_eyebrow'), setting('mcup_below_price')], 'strlen'); ?>
      <?php if ($belowSub): ?><p class="cup-dark-subtext"><?= e(implode(' · ', $belowSub)) ?></p><?php endif; ?>

      <?php render_dark_expander(setting('mcup_below_expander1_label', 'View Canapé Menu'), function () { ?>
        <p class="cup-dark-dummy">Menu details coming soon.</p>
      <?php }); ?>
      <?php render_dark_expander(setting('mcup_below_expander2_label', 'View Drinks Package'), function () { ?>
        <p class="cup-dark-dummy">Drinks package details coming soon.</p>
      <?php }); ?>
    </div>
    <?php endif; ?>

  </div>
</section>
<?php endif; ?>

<script>
(function () {
  document.querySelectorAll('.cup-dark-expander-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.closest('.cup-dark-expander').classList.toggle('open');
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
