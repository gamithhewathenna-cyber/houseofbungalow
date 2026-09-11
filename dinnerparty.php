<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('dp_heading', 'Dinner Party At The House');
$page_meta_description = setting('dp_p1');

include __DIR__ . '/includes/header.php';

$dp_hero_image = asset(setting('dp_hero_image', 'assets/img/below.jpg'));
$dp_hero_video = setting('dp_hero_video');
$dp_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($dp_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$gallery = blocks('dp_gallery');
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
?>

<!-- Hero ------------------------------------------------------------- -->
<section class="hero"<?php if (!$dp_hero_video): ?> style="background-image:url('<?= e($dp_hero_image) ?>');"<?php endif; ?>>
  <?php if ($dp_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($dp_hero_video)) ?>" type="<?= e($dp_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>

<!-- Intro -------------------------------------------------------------- -->
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('dp_heading', 'Dinner Party At The House')) ?></h1>
    <p class="subheading"><?= e(setting('dp_subheading')) ?></p>
    <p class="lede"><?= e(setting('dp_lede1')) ?></p>
    <p class="lede"><?= e(setting('dp_lede2')) ?></p>
    <p><?= e(setting('dp_p1')) ?></p>
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

<!-- Gallery ------------------------------------------------------------------ -->
<?php if ($gallery_rows): ?>
<section class="rest-gallery reveal">
  <div class="container">
    <div class="gallery-grid">
      <?php foreach ($gallery_rows as $row): ?>
        <div class="gallery-row gallery-row-<?= count($row) ?>">
          <?php foreach ($row as $g): ?>
            <div class="gallery-item"><img src="<?= e(asset($g['image'])) ?>" alt="<?= e($g['title'] ?: SITE_NAME) ?>"></div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<hr class="section-divider">

<!-- Menu (own dataset — not shared with the Restaurant or Brunch pages) --- -->
<section class="menu-section reveal">
  <div class="container">
    <h2><?= e(setting('dp_menu_heading', 'Discover Our Menus')) ?></h2>
    <?php if (setting('dp_menu_lede')): ?>
      <p class="menu-lede"><?= e(setting('dp_menu_lede')) ?></p>
    <?php endif; ?>

    <p class="menu-active-label" id="menuActiveLabel"><?= e($menu_cats ? $menu_cats[0]['title'] : '') ?> Specials</p>

    <div class="menu-tabs">
      <?php $first = true; foreach ($menu_cats as $cat): ?>
        <button type="button" class="menu-tab-btn<?= $first ? ' active' : '' ?>" data-menu-tab="<?= (int) $cat['id'] ?>"><?= e($cat['title']) ?></button>
      <?php $first = false; endforeach; ?>
    </div>

    <?php $first = true; foreach ($menu_cats as $cat): ?>
      <div class="menu-panel<?= $first ? ' active' : '' ?>" data-menu-panel="<?= (int) $cat['id'] ?>">
        <?php if ($menu_items_by_cat[$cat['id']]): ?>
          <?php foreach ($menu_items_by_cat[$cat['id']] as $item): ?>
            <div class="menu-item">
              <div class="menu-item-title">
                <span><?= e($item['title']) ?></span>
                <?php if ($item['subtitle']): ?><span class="price">$<?= e($item['subtitle']) ?></span><?php endif; ?>
              </div>
              <?php if ($item['body']): ?><div class="menu-item-desc"><?= e($item['body']) ?></div><?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="menu-empty">More to come — this menu is being finalised.</p>
        <?php endif; ?>
      </div>
    <?php $first = false; endforeach; ?>
  </div>
</section>

<!-- Guest Artists -------------------------------------------------------- -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img src="<?= e(asset(setting('dp_guest_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('dp_guest_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('dp_guest_eyebrow', 'Guest Artists')) ?></p>
        <h2><?= e(setting('dp_guest_heading')) ?></h2>
        <p><?= e(setting('dp_guest_p1')) ?></p>
        <p><?= e(setting('dp_guest_p2')) ?></p>
        <?php if (setting('dp_guest_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('dp_guest_btn_url', '#'))) ?>"><?= e(setting('dp_guest_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script>
(function () {
  var tabs = document.querySelectorAll('.menu-tab-btn');
  var panels = document.querySelectorAll('.menu-panel');
  var activeLabel = document.getElementById('menuActiveLabel');
  if (!tabs.length) return;
  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (b) { b.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('active'); });
      btn.classList.add('active');
      var panel = document.querySelector('.menu-panel[data-menu-panel="' + btn.dataset.menuTab + '"]');
      if (panel) panel.classList.add('active');
      if (activeLabel) activeLabel.textContent = btn.textContent.trim() + ' Specials';
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
