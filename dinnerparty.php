<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('dp_heading', 'Dinner Party At The House');
$page_meta_description = setting('dp_p1');

include __DIR__ . '/includes/header.php';

$dp_hero_image = asset(setting('dp_hero_image', 'assets/img/below.jpg'));
$dp_hero_video = setting('dp_hero_video');
$dp_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($dp_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$gallery = blocks('dp_gallery');
$guestCards = blocks('dp_guest_card');
$gallery_rows = [];
$i = 0; $take3 = true;
while ($i < count($gallery)) {
    $take = $take3 ? 3 : 2;
    $gallery_rows[] = array_slice($gallery, $i, $take);
    $i += $take;
    $take3 = !$take3;
}

$menu_cats = blocks('dp_menu_cat');
$menu_items_by_cat = [];
foreach ($menu_cats as $cat) {
    $menu_items_by_cat[$cat['id']] = blocks('dp_menu_item_' . $cat['id']);
}

/**
 * Renders one menu category's rows, using each row's type (set via
 * admin > Dinner Party > Menu > Type column): a page title + subtitle,
 * an uppercase section heading, a plain line, a small footnote, or a
 * normal name + price + description.
 */
function render_dp_menu_items(array $items): void
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
<?php if (section_visible('dp_hero')): ?>
<section class="hero"<?php if (!$dp_hero_video): ?> style="background-image:url('<?= e($dp_hero_image) ?>');"<?php endif; ?>>
  <?php if ($dp_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($dp_hero_video)) ?>" type="<?= e($dp_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Intro -------------------------------------------------------------- -->
<?php if (section_visible('dp_intro')): ?>
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('dp_heading', 'Dinner Party At The House')) ?></h1>
    <p class="subheading"><?= e(setting('dp_subheading')) ?></p>
    <p class="lede"><?= e(setting('dp_lede1')) ?></p>
    <p class="lede"><?= e(setting('dp_lede2')) ?></p>
    <p><?= enl(setting('dp_p1')) ?></p>
    <?php if (setting('dp_p2')): ?><p><?= enl(setting('dp_p2')) ?></p><?php endif; ?>
    <?php if (setting('dp_hours')): ?><p class="hours-line" style="text-align:center;"><?= eb(setting('dp_hours')) ?></p><?php endif; ?>
    <div class="btn-wrap btn-wrap-row">
      <?php if (setting('dp_btn1_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('dp_btn1_url', '#'))) ?>"><?= e(setting('dp_btn1_label')) ?></a>
      <?php endif; ?>
      <?php if (setting('dp_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('dp_btn2_url', '#'))) ?>"><?= e(setting('dp_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
/**
 * Content sections below the Intro, in their admin-configurable order
 * (admin > Dinner Party > Section Order, admins only - Hero and Intro
 * always stay fixed at the top).
 */
$dp_sections = [
    'dp_gallery' => function () use ($gallery_rows): void {
        if (!$gallery_rows || !section_visible('dp_gallery')) return;
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
    'dp_menu' => function () use ($menu_cats, $menu_items_by_cat): void {
        if (!section_visible('dp_menu')) return;
        ?>
<section class="menu-dark menu-light reveal">
  <div class="container">
    <h2 class="menu-dark-heading"><?= e(setting('dp_menu_heading', 'Discover Our Menus')) ?></h2>
    <?php if (setting('dp_menu_lede')): ?>
      <p class="menu-dark-lede"><?= enl(setting('dp_menu_lede')) ?></p>
    <?php endif; ?>

    <?php if ($menu_cats): ?>
      <div class="menu-dark-tabs">
        <?php $first = true; foreach ($menu_cats as $cat): ?>
          <button type="button" class="menu-dark-tab-btn<?= $first ? ' active' : '' ?>" data-dp-menu-tab="<?= (int) $cat['id'] ?>"><?= e($cat['title']) ?></button>
        <?php $first = false; endforeach; ?>
      </div>

      <div class="menu-dark-panel-wrap">
        <?php $first = true; foreach ($menu_cats as $cat): ?>
          <div class="menu-dark-panel<?= $first ? ' active' : '' ?>" data-dp-menu-panel="<?= (int) $cat['id'] ?>">
            <?php render_dp_menu_items($menu_items_by_cat[$cat['id']]); ?>
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
    'dp_guest' => function (): void {
        if (!section_visible('dp_guest')) return;
        ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('dp_guest_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('dp_guest_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('dp_guest_eyebrow', 'Guest Artists')) ?></p>
        <h2><?= e(setting('dp_guest_heading')) ?></h2>
        <p><?= enl(setting('dp_guest_p1')) ?></p>
        <p><?= enl(setting('dp_guest_p2')) ?></p>
        <?php if (setting('dp_guest_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('dp_guest_btn_url', '#'))) ?>"><?= e(setting('dp_guest_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php
    },
    'dp_guest_cards' => function () use ($guestCards): void {
        if (!$guestCards || !section_visible('dp_guest_cards')) return;
        ?>
<section class="section reveal">
  <div class="container">
    <div class="livelate-cards">
      <?php foreach ($guestCards as $card): ?>
        <div class="livelate-card">
          <div class="livelate-card-media">
            <?php if ($card['image']): ?>
              <img loading="lazy" decoding="async" src="<?= e(asset($card['image'])) ?>" alt="<?= e($card['title']) ?>">
            <?php else: ?>
              <div class="livelate-card-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M9 9l-2-1M17 10l-2 1M9 15l-2 1M17 14l-2-1"/></svg>
                <span>ARTIST PHOTO</span>
              </div>
            <?php endif; ?>
            <span class="livelate-card-tint"></span>
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
  </div>
</section>
<?php
    },
];
foreach (section_order('dinnerparty', array_keys($dp_sections)) as $dp_key) {
    $dp_sections[$dp_key]();
}
?>

<script>
(function () {
  var tabs = document.querySelectorAll('[data-dp-menu-tab]');
  var panels = document.querySelectorAll('[data-dp-menu-panel]');
  if (!tabs.length) return;
  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (b) { b.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('active'); });
      btn.classList.add('active');
      var panel = document.querySelector('[data-dp-menu-panel="' + btn.dataset.dpMenuTab + '"]');
      if (panel) panel.classList.add('active');
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
