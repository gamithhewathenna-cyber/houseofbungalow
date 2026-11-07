<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('el_heading', 'Express Lunch');
$page_meta_description = setting('el_p1');

include __DIR__ . '/includes/header.php';

$el_hero_image = asset(setting('el_hero_image', 'assets/img/cafe.jpg'));
$el_hero_video = setting('el_hero_video');
$el_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($el_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$gallery = blocks('el_gallery');
$gallery_rows = [];
$i = 0; $take3 = true;
while ($i < count($gallery)) {
    $take = $take3 ? 3 : 2;
    $gallery_rows[] = array_slice($gallery, $i, $take);
    $i += $take;
    $take3 = !$take3;
}

$menu_cats = blocks('el_menu_cat');
$menu_items_by_cat = [];
foreach ($menu_cats as $cat) {
    $menu_items_by_cat[$cat['id']] = blocks('el_menu_item_' . $cat['id']);
}

/**
 * Renders one menu category's rows, using each row's type (set via
 * admin > Express Lunch > Menu > Type column): a page title +
 * subtitle, an uppercase section heading, a plain line, a small
 * footnote, or a normal name + price + description.
 */
function render_el_menu_items(array $items): void
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
            if ($item['title']) {
                ?><p class="menu-line menu-line-strong"><?= e($item['title']) ?></p><?php
            }
            if ($item['body'] || $item['subtitle']) {
                ?><p class="menu-line"><?= e($item['body']) ?><?php if ($item['subtitle']): ?> <span class="price">$<?= e($item['subtitle']) ?></span><?php endif; ?></p><?php
            }
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
?>

<!-- Hero ------------------------------------------------------------- -->
<?php if (section_visible('el_hero')): ?>
<section class="hero"<?php if (!$el_hero_video): ?> style="background-image:url('<?= e($el_hero_image) ?>');"<?php endif; ?>>
  <?php if ($el_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($el_hero_video)) ?>" type="<?= e($el_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Intro -------------------------------------------------------------- -->
<?php if (section_visible('el_intro')): ?>
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('el_heading', 'Good Food. Great Value. Your Midday Escape.')) ?></h1>
    <p class="subheading"><?= e(setting('el_subheading')) ?></p>
    <p class="lede"><?= e(setting('el_lede1')) ?></p>
    <p class="lede"><?= e(setting('el_lede2')) ?></p>
    <p><?= enl(setting('el_p1')) ?></p>
    <?php if (setting('el_hours')): ?><p class="hours-line" style="text-align:center;"><?= eb(setting('el_hours')) ?></p><?php endif; ?>
    <div class="btn-wrap btn-wrap-row">
      <?php if (setting('el_btn1_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('el_btn1_url', '#'))) ?>"><?= e(setting('el_btn1_label')) ?></a>
      <?php endif; ?>
      <?php if (setting('el_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('el_btn2_url', '#'))) ?>"><?= e(setting('el_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
/**
 * Content sections below the Intro, in their admin-configurable order
 * (admin > Express Lunch > Section Order, admins only - Hero and Intro
 * always stay fixed at the top).
 */
$el_sections = [
    'el_gallery' => function () use ($gallery_rows): void {
        if (!$gallery_rows || !section_visible('el_gallery')) return;
        ?>
<section class="rest-gallery reveal">
  <div class="container">
    <div class="gallery-grid">
      <?php foreach ($gallery_rows as $row): ?>
        <div class="gallery-row gallery-row-<?= count($row) ?>">
          <?php foreach ($row as $g): ?>
            <div class="gallery-item"><img loading="lazy" decoding="async" src="<?= e(asset($g['image'])) ?>" alt="<?= e($g['title'] ?: SITE_NAME) ?>"></div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<hr class="section-divider">
<?php
    },
    'el_menu' => function () use ($menu_cats, $menu_items_by_cat): void {
        if (!section_visible('el_menu')) return;
        ?>
<section class="menu-dark menu-light menu-items-centered reveal">
  <div class="container">
    <h2 class="menu-dark-heading"><?= e(setting('el_menu_heading', 'Discover Our Menus')) ?></h2>
    <?php if (setting('el_menu_lede')): ?>
      <p class="menu-dark-lede"><?= enl(setting('el_menu_lede')) ?></p>
    <?php endif; ?>

    <?php if ($menu_cats): ?>
      <div class="menu-dark-tabs">
        <?php $first = true; foreach ($menu_cats as $cat): ?>
          <button type="button" class="menu-dark-tab-btn<?= $first ? ' active' : '' ?>" data-el-menu-tab="<?= (int) $cat['id'] ?>"><?= e($cat['title']) ?></button>
        <?php $first = false; endforeach; ?>
      </div>

      <div class="menu-dark-panel-wrap">
        <?php $first = true; foreach ($menu_cats as $cat): ?>
          <div class="menu-dark-panel<?= $first ? ' active' : '' ?>" data-el-menu-panel="<?= (int) $cat['id'] ?>">
            <?php render_el_menu_items($menu_items_by_cat[$cat['id']]); ?>
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
<?php
    },
    'el_guest' => function (): void {
        if (!section_visible('el_guest')) return;
        ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('el_guest_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('el_guest_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('el_guest_eyebrow', 'Make More Of Your Midday')) ?></p>
        <h2><?= e(setting('el_guest_heading')) ?></h2>
        <p><?= enl(setting('el_guest_p1')) ?></p>
        <p><?= enl(setting('el_guest_p2')) ?></p>
        <?php if (setting('el_guest_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('el_guest_btn_url', '#'))) ?>"><?= e(setting('el_guest_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php
    },
];
foreach (section_order('expresslunch', array_keys($el_sections)) as $el_key) {
    $el_sections[$el_key]();
}
?>

<script>
(function () {
  var tabs = document.querySelectorAll('[data-el-menu-tab]');
  var panels = document.querySelectorAll('[data-el-menu-panel]');
  if (!tabs.length) return;
  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (b) { b.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('active'); });
      btn.classList.add('active');
      var panel = document.querySelector('[data-el-menu-panel="' + btn.dataset.elMenuTab + '"]');
      if (panel) panel.classList.add('active');
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
