<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('cafe_heading', 'Café');
$page_meta_description = setting('cafe_lede');

include __DIR__ . '/includes/header.php';

$cafe_hero_image = asset(setting('cafe_hero_image', 'assets/img/cafe.jpg'));
$cafe_hero_video = setting('cafe_hero_video');
$cafe_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($cafe_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$gallery = blocks('cafe_gallery');
$gallery_rows = [];
$i = 0; $take3 = true;
while ($i < count($gallery)) {
    $take = $take3 ? 3 : 2;
    $gallery_rows[] = array_slice($gallery, $i, $take);
    $i += $take;
    $take3 = !$take3;
}

$menu_cats = blocks('cafe_menu_cat');
$menu_items_by_cat = [];
foreach ($menu_cats as $cat) {
    $menu_items_by_cat[$cat['id']] = blocks('cafe_menu_item_' . $cat['id']);
}
?>

<!-- Hero ------------------------------------------------------------- -->
<?php if (section_visible('cafe_hero')): ?>
<section class="hero"<?php if (!$cafe_hero_video): ?> style="background-image:url('<?= e($cafe_hero_image) ?>');"<?php endif; ?>>
  <?php if ($cafe_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($cafe_hero_video)) ?>" type="<?= e($cafe_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Intro -------------------------------------------------------------- -->
<?php if (section_visible('cafe_intro')): ?>
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('cafe_heading', 'Café')) ?></h1>
    <p class="subheading"><?= e(setting('cafe_subheading')) ?></p>
    <p class="lede"><?= e(setting('cafe_lede')) ?></p>
    <p class="cafe-intro-p"><?= enl(setting('cafe_p1')) ?></p>
    <p class="cafe-intro-p"><?= enl(setting('cafe_p2')) ?></p>
  </div>
</section>
<?php endif; ?>

<!-- Menu --------------------------------------------------------------- -->
<?php if (section_visible('cafe_menu')): ?>
<section class="menu-section reveal">
  <div class="container">
    <h2><?= e(setting('cafe_menu_heading', 'Café Menu')) ?></h2>
    <?php if (setting('cafe_menu_lede')): ?>
      <p class="menu-lede"><?= enl(setting('cafe_menu_lede')) ?></p>
    <?php endif; ?>

    <p class="menu-active-label" id="menuActiveLabel"><?= e($menu_cats ? $menu_cats[0]['title'] : '') ?></p>

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
                <?php if ($item['subtitle']): ?><span class="price"><?= e($item['subtitle']) ?></span><?php endif; ?>
              </div>
              <?php if ($item['body']): ?><div class="menu-item-desc"><?= enl($item['body']) ?></div><?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="menu-empty">More to come - this menu is being finalised.</p>
        <?php endif; ?>
      </div>
    <?php $first = false; endforeach; ?>

    <?php if (setting('cafe_menu_footnote')): ?>
      <p class="menu-footnote"><?= e(setting('cafe_menu_footnote')) ?></p>
    <?php endif; ?>
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
      if (activeLabel) activeLabel.textContent = btn.textContent.trim();
    });
  });
})();
</script>
<?php endif; ?>

<!-- Gallery ------------------------------------------------------------------ -->
<?php if ($gallery && section_visible('cafe_gallery')): ?>
<section class="rest-gallery reveal">
  <div class="container">
    <div class="gallery-grid">
      <?php foreach ($gallery_rows as $row): ?>
        <div class="gallery-row gallery-row-<?= count($row) ?>">
          <?php foreach ($row as $g): ?>
            <div class="gallery-item">
              <img loading="lazy" decoding="async" src="<?= e(asset($g['image'])) ?>" alt="<?= e($g['title'] ?: SITE_NAME) ?>">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<hr class="section-divider">
<?php endif; ?>

<!-- Hours ---------------------------------------------------------------- -->
<?php if (section_visible('cafe_hours')): ?>
<section class="hours-block reveal">
  <div class="container">
    <h2><?= e(setting('cafe_hours_heading', 'Coffee. Food. Foveaux Street.')) ?></h2>
    <?php if (setting('cafe_hours_p1')): ?><p class="hours-note"><?= enl(setting('cafe_hours_p1')) ?></p><?php endif; ?>
    <?php if (setting('cafe_hours_p2')): ?><p class="hours-note"><?= enl(setting('cafe_hours_p2')) ?></p><?php endif; ?>
    <?php if (setting('cafe_hours')): ?><p class="hours-line" style="margin-top:16px;"><?= eb(setting('cafe_hours')) ?></p><?php endif; ?>
    <?php if (setting('cafe_address')): ?><p class="hours-address"><?= e(setting('cafe_address')) ?></p><?php endif; ?>
    <div class="btn-wrap btn-wrap-row">
      <?php if (setting('cafe_btn1_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('cafe_btn1_url', '#'))) ?>"><?= e(setting('cafe_btn1_label')) ?></a>
      <?php endif; ?>
      <?php if (setting('cafe_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('cafe_btn2_url', '#'))) ?>"><?= e(setting('cafe_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- More Of The House ----------------------------------------------------- -->
<?php if (section_visible('cafe_final')): ?>
<section class="textblock reveal">
  <div class="container">
    <h2><?= e(setting('cafe_final_heading', 'More Of The House')) ?></h2>
    <p><?= enl(setting('cafe_final_p1')) ?></p>
    <p><?= enl(setting('cafe_final_p2')) ?></p>
    <div class="btn-wrap btn-wrap-row">
      <?php if (setting('cafe_final_btn1_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('cafe_final_btn1_url', '#'))) ?>"><?= e(setting('cafe_final_btn1_label')) ?></a>
      <?php endif; ?>
      <?php if (setting('cafe_final_btn2_label')): ?>
        <a class="link-underline" href="<?= e(url(setting('cafe_final_btn2_url', '#'))) ?>"><?= e(setting('cafe_final_btn2_label')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
