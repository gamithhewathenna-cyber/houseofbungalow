<?php
require_once __DIR__ . '/includes/functions.php';

$page_meta_title       = setting('rest_heading', 'Restaurant');
$page_meta_description = setting('rest_lede');

include __DIR__ . '/includes/header.php';

$rest_hero_image = asset(setting('rest_hero_image', 'assets/img/restaurant.jpg'));
$rest_hero_video = setting('rest_hero_video');
$rest_hero_video_mime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime'][strtolower(pathinfo($rest_hero_video, PATHINFO_EXTENSION))] ?? 'video/mp4';

$menu_cats = blocks('rest_menu_cat');
$menu_items_by_cat = [];
foreach ($menu_cats as $cat) {
    $menu_items_by_cat[$cat['id']] = blocks('rest_menu_item_' . $cat['id']);
}

$gallery = blocks('rest_gallery');

// Group photos into alternating rows of 3 (wide middle) then 2 (equal), repeating.
$gallery_rows = [];
$i = 0; $take3 = true;
while ($i < count($gallery)) {
    $take = $take3 ? 3 : 2;
    $gallery_rows[] = array_slice($gallery, $i, $take);
    $i += $take;
    $take3 = !$take3;
}
?>

<!-- Hero ------------------------------------------------------------- -->
<section class="hero"<?php if (!$rest_hero_video): ?> style="background-image:url('<?= e($rest_hero_image) ?>');"<?php endif; ?>>
  <?php if ($rest_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($rest_hero_video)) ?>" type="<?= e($rest_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
</section>

<!-- Intro ------------------------------------------------------------- -->
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('rest_heading', 'Restaurant')) ?></h1>
    <p class="subheading"><?= e(setting('rest_subheading')) ?></p>
    <p class="lede"><?= e(setting('rest_lede')) ?></p>
    <p><?= e(setting('rest_intro_p1')) ?></p>
    <p><?= e(setting('rest_intro_p2')) ?></p>
    <?php if (setting('rest_intro_btn_label')): ?>
      <div class="btn-wrap">
        <a class="link-underline" href="<?= e(url(setting('rest_intro_btn_url', '#'))) ?>"><?= e(setting('rest_intro_btn_label')) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- The Food ----------------------------------------------------------- -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('rest_food_eyebrow', 'The Food')) ?></p>
        <h2><?= e(setting('rest_food_heading')) ?></h2>
        <p><?= e(setting('rest_food_p1')) ?></p>
        <p><?= e(setting('rest_food_p2')) ?></p>
      </div>
      <div class="split-media">
        <img src="<?= e(asset(setting('rest_food_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_food_heading')) ?>">
      </div>
    </div>
  </div>
</section>

<!-- Menu --------------------------------------------------------------- -->
<section class="menu-section reveal">
  <div class="container">
    <h2><?= e(setting('rest_menu_heading', 'Discover Our Menus')) ?></h2>
    <?php if (setting('rest_menu_lede')): ?>
      <p class="menu-lede"><?= e(setting('rest_menu_lede')) ?></p>
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

<!-- The Room ------------------------------------------------------------ -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img src="<?= e(asset(setting('rest_room_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_room_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('rest_room_eyebrow', 'The Room')) ?></p>
        <h2><?= e(setting('rest_room_heading')) ?></h2>
        <p><?= e(setting('rest_room_p1')) ?></p>
        <p><?= e(setting('rest_room_p2')) ?></p>
      </div>
    </div>
  </div>
</section>

<!-- Friday & Saturday ----------------------------------------------------- -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('rest_fridaysat_eyebrow', 'Friday & Saturday')) ?></p>
        <h2><?= e(setting('rest_fridaysat_heading')) ?></h2>
        <p><?= e(setting('rest_fridaysat_p1')) ?></p>
        <p><?= e(setting('rest_fridaysat_p2')) ?></p>
        <?php if (setting('rest_fridaysat_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('rest_fridaysat_btn_url', '#'))) ?>"><?= e(setting('rest_fridaysat_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img src="<?= e(asset(setting('rest_fridaysat_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_fridaysat_heading')) ?>">
      </div>
    </div>
  </div>
</section>

<!-- Happy Hour -------------------------------------------------------------- -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img src="<?= e(asset(setting('rest_happyhour_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_happyhour_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('rest_happyhour_eyebrow', 'Happy Hour')) ?></p>
        <h2><?= e(setting('rest_happyhour_heading')) ?></h2>
        <p><?= e(setting('rest_happyhour_p1')) ?></p>
        <?php if (setting('rest_happyhour_hours1')): ?><p class="hours-line"><?= e(setting('rest_happyhour_hours1')) ?></p><?php endif; ?>
        <?php if (setting('rest_happyhour_hours2')): ?><p class="hours-line"><?= e(setting('rest_happyhour_hours2')) ?></p><?php endif; ?>
        <?php if (setting('rest_happyhour_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('rest_happyhour_btn_url', '#'))) ?>"><?= e(setting('rest_happyhour_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- The Chef ------------------------------------------------------------------ -->
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy chef-copy">
        <p class="eyebrow"><?= e(setting('rest_chef_eyebrow', 'The Chef')) ?></p>
        <h2 class="chef-name"><?= e(setting('rest_chef_name')) ?></h2>
        <p><?= e(setting('rest_chef_p1')) ?></p>
        <p><?= e(setting('rest_chef_p2')) ?></p>
        <p><?= e(setting('rest_chef_p3')) ?></p>
      </div>
      <div class="split-media">
        <img src="<?= e(asset(setting('rest_chef_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_chef_name')) ?>">
      </div>
    </div>
  </div>
</section>

<!-- Restaurant Hours ------------------------------------------------------------ -->
<section class="hours-block reveal">
  <div class="container">
    <h2><?= e(setting('rest_hours_heading', 'Restaurant Hours')) ?></h2>
    <?php if (setting('rest_hours_lunch')): ?><p class="hours-line"><strong><?= e(setting('rest_hours_lunch_label', 'Lunch')) ?></strong> <?= e(setting('rest_hours_lunch')) ?></p><?php endif; ?>
    <?php if (setting('rest_hours_dinner')): ?><p class="hours-line"><strong><?= e(setting('rest_hours_dinner_label', 'Dinner')) ?></strong> <?= e(setting('rest_hours_dinner')) ?></p><?php endif; ?>
    <?php if (setting('rest_hours_fridaysat') || setting('rest_hours_note')): ?>
      <hr class="hours-divider">
    <?php endif; ?>
    <?php if (setting('rest_hours_fridaysat')): ?><p class="hours-line"><strong><?= e(setting('rest_hours_fridaysat_label', 'Friday & Saturday')) ?></strong> <?= e(setting('rest_hours_fridaysat')) ?></p><?php endif; ?>
    <?php if (setting('rest_hours_note')): ?><p class="hours-note"><?= e(setting('rest_hours_note')) ?></p><?php endif; ?>
    <?php if (setting('rest_hours_btn_label')): ?>
      <div class="btn-wrap">
        <a class="link-underline" href="<?= e(url(setting('rest_hours_btn_url', '#'))) ?>"><?= e(setting('rest_hours_btn_label')) ?></a>
      </div>
    <?php endif; ?>
    <?php if (setting('rest_hours_footnote')): ?><p class="hours-footnote"><?= e(setting('rest_hours_footnote')) ?></p><?php endif; ?>
  </div>
</section>

<!-- Gallery ------------------------------------------------------------------ -->
<?php if ($gallery): ?>
<section class="rest-gallery reveal">
  <div class="container">
    <div class="gallery-grid">
      <?php foreach ($gallery_rows as $row): ?>
        <div class="gallery-row gallery-row-<?= count($row) ?>">
          <?php foreach ($row as $g): ?>
            <div class="gallery-item">
              <img src="<?= e(asset($g['image'])) ?>" alt="<?= e($g['title'] ?: SITE_NAME) ?>">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

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
