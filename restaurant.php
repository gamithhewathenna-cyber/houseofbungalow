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

/**
 * Renders one menu category's rows inside its "View ..." expander, using
 * each row's type (set via admin > Restaurant > Menu > Type column):
 * a page title + subtitle, an uppercase section heading, a plain line
 * (set banquets), a small footnote, or a normal name + price + description.
 */
function render_rest_menu_items(array $items): void
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
            ?><p class="menu-line"><?= e($item['body']) ?></p><?php
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

/**
 * One full-width bordered expander button (same style as the Melbourne Cup
 * page's "View ... Menu" buttons) - click toggles the panel underneath it.
 */
function render_rest_dark_expander(string $label, callable $renderContent): void
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

$gallery = blocks('rest_gallery');
$faqs    = blocks('rest_faq');

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
<?php if (section_visible('rest_hero')): ?>
<section class="hero"<?php if (!$rest_hero_video): ?> style="background-image:url('<?= e($rest_hero_image) ?>');"<?php endif; ?>>
  <?php if ($rest_hero_video): ?>
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= e(asset($rest_hero_video)) ?>" type="<?= e($rest_hero_video_mime) ?>">
    </video>
  <?php endif; ?>
  <?php if (setting('rest_opentable_embed')): ?>
    <div class="opentable-overlay"><?= setting('rest_opentable_embed') ?></div>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- Intro ------------------------------------------------------------- -->
<?php if (section_visible('rest_intro')): ?>
<section class="page-hero-intro reveal">
  <div class="container">
    <h1><?= e(setting('rest_heading', 'Restaurant')) ?></h1>
    <p class="subheading"><?= e(setting('rest_subheading')) ?></p>
    <p class="lede"><?= enl(setting('rest_lede')) ?></p>
    <p><?= enl(setting('rest_intro_p1')) ?></p>
    <p><?= enl(setting('rest_intro_p2')) ?></p>
    <?php if (setting('rest_intro_btn_label')): ?>
      <div class="btn-wrap">
        <a class="link-underline" href="<?= e(url(setting('rest_intro_btn_url', '#'))) ?>"><?= e(setting('rest_intro_btn_label')) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- The Food ----------------------------------------------------------- -->
<?php if (section_visible('rest_food')): ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('rest_food_eyebrow', 'The Food')) ?></p>
        <h2><?= e(setting('rest_food_heading')) ?></h2>
        <p><?= enl(setting('rest_food_p1')) ?></p>
        <p><?= enl(setting('rest_food_p2')) ?></p>
      </div>
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('rest_food_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_food_heading')) ?>">
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Menu --------------------------------------------------------------- -->
<?php if (section_visible('rest_menu')): ?>
<section class="cup-dark-menu reveal">
  <div class="container">
    <h2 style="text-align:center;font-family:var(--serif);font-weight:300;font-size:40px;color:var(--ink);margin:0 0 20px;"><?= e(setting('rest_menu_heading', 'Discover Our Menus')) ?></h2>
    <?php if (setting('rest_menu_lede')): ?>
      <p class="menu-lede" style="text-align:center;font-family:var(--serif);font-style:italic;font-weight:200;font-size:18px;line-height:1.5;color:var(--ink-soft);max-width:1000px;margin:0 auto 40px;"><?= enl(setting('rest_menu_lede')) ?></p>
    <?php endif; ?>

    <div class="cup-dark-grid rest-menu-grid">
      <?php foreach ($menu_cats as $cat): ?>
        <div class="cup-dark-offer">
          <?php if ($cat['image']): ?>
            <div class="cup-dark-offer-media"><img loading="lazy" decoding="async" src="<?= e(asset($cat['image'])) ?>" alt="<?= e($cat['title']) ?>"></div>
          <?php endif; ?>
          <?php if ($cat['subtitle']): ?><p class="cup-dark-eyebrow"><?= e($cat['subtitle']) ?></p><?php endif; ?>
          <h3 class="cup-dark-heading"><?= e($cat['title']) ?></h3>
          <?php if ($cat['body']): ?><p class="cup-dark-subtext"><?= e($cat['body']) ?></p><?php endif; ?>

          <?php render_rest_dark_expander($cat['link_url2'] ?: 'View ' . $cat['title'], function () use ($menu_items_by_cat, $cat) {
              render_rest_menu_items($menu_items_by_cat[$cat['id']]);
          }); ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- The Room ------------------------------------------------------------ -->
<?php if (section_visible('rest_room')): ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('rest_room_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_room_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('rest_room_eyebrow', 'The Room')) ?></p>
        <h2><?= e(setting('rest_room_heading')) ?></h2>
        <p><?= enl(setting('rest_room_p1')) ?></p>
        <p><?= enl(setting('rest_room_p2')) ?></p>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Friday & Saturday ----------------------------------------------------- -->
<?php if (section_visible('rest_fridaysat')): ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('rest_fridaysat_eyebrow', 'Friday & Saturday')) ?></p>
        <h2><?= e(setting('rest_fridaysat_heading')) ?></h2>
        <p><?= enl(setting('rest_fridaysat_p1')) ?></p>
        <p><?= enl(setting('rest_fridaysat_p2')) ?></p>
        <?php if (setting('rest_fridaysat_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('rest_fridaysat_btn_url', '#'))) ?>"><?= e(setting('rest_fridaysat_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('rest_fridaysat_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_fridaysat_heading')) ?>">
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Happy Hour -------------------------------------------------------------- -->
<?php if (section_visible('rest_happyhour')): ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('rest_happyhour_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_happyhour_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('rest_happyhour_eyebrow', 'Happy Hour')) ?></p>
        <h2><?= e(setting('rest_happyhour_heading')) ?></h2>
        <p><?= enl(setting('rest_happyhour_p1')) ?></p>
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
<?php endif; ?>

<!-- The Chef ------------------------------------------------------------------ -->
<?php if (section_visible('rest_chef')): ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-copy chef-copy">
        <p class="eyebrow"><?= e(setting('rest_chef_eyebrow', 'The Chef')) ?></p>
        <h2 class="chef-name"><?= e(setting('rest_chef_name')) ?></h2>
        <p><?= enl(setting('rest_chef_p1')) ?></p>
        <p><?= enl(setting('rest_chef_p2')) ?></p>
        <p><?= enl(setting('rest_chef_p3')) ?></p>
      </div>
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('rest_chef_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_chef_name')) ?>">
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Restaurant Hours ------------------------------------------------------------ -->
<?php if (section_visible('rest_hours')): ?>
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
<?php endif; ?>

<!-- Gallery ------------------------------------------------------------------ -->
<?php if ($gallery && section_visible('rest_gallery')): ?>
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
<?php endif; ?>

<!-- FAQs ------------------------------------------------------------------------ -->
<?php if ($faqs && section_visible('rest_faq')): ?>
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
          <div class="faq-answer"><p><?= e($faq['body']) ?></p></div>
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
  document.querySelectorAll('.cup-dark-expander-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.closest('.cup-dark-expander').classList.toggle('open');
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
