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
 */
function render_set_menu(array $cats, array $itemsByCat): void
{
    ?>
    <div class="set-menu">
      <?php foreach ($cats as $cat): ?>
        <div class="set-menu-category">
          <p class="set-menu-category-heading"><?= e($cat['title']) ?></p>
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

<!-- Melbourne Cup At The Restaurant --------------------------------------- -->
<?php if (section_visible('mcup_restaurant')): ?>
<section class="maroon-band reveal">
  <div class="container">
    <h2><?= e(setting('mcup_rest_heading', 'Melbourne Cup At The Restaurant')) ?></h2>
    <p class="subheading"><?= e(setting('mcup_rest_subheading')) ?></p>
    <p class="lede"><?= e(setting('mcup_rest_lede')) ?></p>
    <p><?= enl(setting('mcup_rest_p1')) ?></p>
    <p><?= enl(setting('mcup_rest_p2')) ?></p>
    <?php if (setting('mcup_rest_date')): ?><p class="hours-line"><?= e(setting('mcup_rest_date')) ?></p><?php endif; ?>
    <?php if (setting('mcup_rest_price')): ?><p class="hours-line" style="margin-top:6px;"><?= e(setting('mcup_rest_price')) ?></p><?php endif; ?>
    <?php if (setting('mcup_rest_price_note')): ?><p><?= e(setting('mcup_rest_price_note')) ?></p><?php endif; ?>
    <div class="btn-wrap-row">
      <?php if (setting('mcup_rest_btn_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('mcup_rest_btn_url', '#'))) ?>"><?= e(setting('mcup_rest_btn_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($rest_menu_cats): ?>
<section class="menu-section reveal">
  <div class="container">
    <h2>The Menu</h2>
    <?php render_set_menu($rest_menu_cats, $rest_menu_items_by_cat); ?>
  </div>
</section>
<?php endif; ?>

<section class="section reveal">
  <div class="container">
    <h2>Beverage Packages</h2>
    <?php if (setting('mcup_rest_bev_lede')): ?><p class="lede"><?= e(setting('mcup_rest_bev_lede')) ?></p><?php endif; ?>
    <?php if (setting('mcup_rest_bev_intro')): ?><p><?= enl(setting('mcup_rest_bev_intro')) ?></p><?php endif; ?>
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
    <?php if (setting('mcup_rest_bev_note1')): ?><p class="hours-footnote" style="margin-top:28px;"><?= e(setting('mcup_rest_bev_note1')) ?></p><?php endif; ?>
    <?php if (setting('mcup_rest_bev_note2')): ?><p class="hours-footnote"><?= enl(setting('mcup_rest_bev_note2')) ?></p><?php endif; ?>
    <div class="btn-wrap">
      <?php if (setting('mcup_rest_btn_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('mcup_rest_btn_url', '#'))) ?>"><?= e(setting('mcup_rest_btn_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Melbourne Cup At Below ------------------------------------------------- -->
<?php if (section_visible('mcup_below')): ?>
<section class="maroon-band reveal">
  <div class="container">
    <h2><?= e(setting('mcup_below_heading', 'Melbourne Cup At Below')) ?></h2>
    <p class="subheading"><?= e(setting('mcup_below_subheading')) ?></p>
    <p class="lede"><?= e(setting('mcup_below_lede')) ?></p>
    <p><?= enl(setting('mcup_below_p1')) ?></p>
    <p><?= enl(setting('mcup_below_p2')) ?></p>
    <?php if (setting('mcup_below_date')): ?><p class="hours-line"><?= e(setting('mcup_below_date')) ?></p><?php endif; ?>
    <?php if (setting('mcup_below_price')): ?><p class="hours-line" style="margin-top:6px;"><?= e(setting('mcup_below_price')) ?></p><?php endif; ?>
    <div class="btn-wrap-row">
      <?php if (setting('mcup_below_btn_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('mcup_below_btn_url', '#'))) ?>"><?= e(setting('mcup_below_btn_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($below_menu_cats): ?>
<section class="menu-section reveal">
  <div class="container">
    <h2>The Menu</h2>
    <?php render_set_menu($below_menu_cats, $below_menu_items_by_cat); ?>
  </div>
</section>
<?php endif; ?>

<section class="section reveal">
  <div class="container">
    <h2>The Drinks</h2>
    <?php if (setting('mcup_below_drinks_lede')): ?><p class="lede"><?= e(setting('mcup_below_drinks_lede', 'Champagne, cocktails and a considered selection of wines.')) ?></p><?php endif; ?>
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
    <div class="btn-wrap" style="margin-top:30px;">
      <?php if (setting('mcup_below_btn_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('mcup_below_btn_url', '#'))) ?>"><?= e(setting('mcup_below_btn_label')) ?></a>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
