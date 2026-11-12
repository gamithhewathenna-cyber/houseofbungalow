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
 * Renders a flat list of menu rows (no headings expected inside it),
 * using each row's type (set via admin > Restaurant > Menu > Type
 * column): a page title + subtitle, a plain line (set banquets), a
 * small footnote, or a normal name + price + description. 'heading'
 * rows are handled separately by render_rest_menu_items() below, which
 * turns them into sub-tabs instead of inline dividers.
 */
function render_rest_menu_item_list(array $items): void
{
    foreach ($items as $item) {
        $itemType = $item['link_url'];
        if ($itemType === 'title') {
            ?><h3 class="menu-page-title"><?= e($item['title']) ?></h3><?php
            if ($item['body']) { ?><p class="menu-page-subtitle"><?= e($item['body']) ?></p><?php }
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

/**
 * Splits one category's rows into a leading "preamble" (anything
 * before the first 'heading' row - e.g. a title/intro line) and a
 * list of groups, one per 'heading' row, each collecting the rows
 * that follow it up to the next 'heading'. Used to turn section
 * headings (admin > Restaurant > Menu > Type column) into sub-tabs.
 */
function rest_menu_group_by_heading(array $items): array
{
    $preamble = [];
    $groups = [];
    $current = -1;
    foreach ($items as $item) {
        if ($item['link_url'] === 'heading') {
            $groups[] = ['name' => $item['title'], 'items' => []];
            $current++;
        } elseif ($current === -1) {
            $preamble[] = $item;
        } else {
            $groups[$current]['items'][] = $item;
        }
    }
    return [$preamble, $groups];
}

/**
 * Renders one menu category: a leading preamble (if any), then, if
 * the category has section headings, a row of sub-tabs (one per
 * heading) each showing just that section's items - or, if it has no
 * headings at all, every item in one continuous list as before.
 */
function render_rest_menu_items(array $items): void
{
    if (!$items) {
        ?><p class="cup-dark-dummy">Menu details coming soon.</p><?php
        return;
    }
    [$preamble, $groups] = rest_menu_group_by_heading($items);
    render_rest_menu_item_list($preamble);

    if (!$groups) {
        return;
    }
    ?>
    <div class="menu-dark-subtabs">
      <?php foreach ($groups as $gi => $g): ?>
        <button type="button" class="menu-dark-subtab-btn<?= $gi === 0 ? ' active' : '' ?>"><?= e($g['name']) ?></button>
      <?php endforeach; ?>
    </div>
    <div class="menu-dark-subpanel-wrap">
      <?php foreach ($groups as $gi => $g): ?>
        <div class="menu-dark-subpanel<?= $gi === 0 ? ' active' : '' ?>">
          <?php render_rest_menu_item_list($g['items']); ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php
}

$gallery = blocks('rest_gallery');
$faqs    = blocks('rest_faq');
$guestCards = blocks('rest_guest_card');

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
  <a class="hero-book-btn" href="<?= e(url('restaurantreservations.php')) ?>">Book Now</a>
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

<?php
/**
 * Content sections below the Intro, in their admin-configurable order
 * (admin > Restaurant & Menus > Section Order, admins only - Hero and
 * Intro always stay fixed at the top).
 */
$rest_sections = [
    'rest_food' => function (): void {
        if (!section_visible('rest_food')) return;
        ?>
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
<?php
    },
    'rest_menu' => function () use ($menu_cats, $menu_items_by_cat): void {
        if (!section_visible('rest_menu')) return;
        ?>
<section class="menu-dark menu-light reveal">
  <div class="container">
    <h2 class="menu-dark-heading"><?= e(setting('rest_menu_heading', 'Discover Our Menus')) ?></h2>
    <?php if (setting('rest_menu_lede')): ?>
      <p class="menu-dark-lede"><?= enl(setting('rest_menu_lede')) ?></p>
    <?php endif; ?>

    <?php if ($menu_cats): ?>
      <div class="menu-dark-tabs">
        <?php $first = true; foreach ($menu_cats as $cat): ?>
          <button type="button" class="menu-dark-tab-btn<?= $first ? ' active' : '' ?>" data-rest-menu-tab="<?= (int) $cat['id'] ?>"><?= e($cat['title']) ?></button>
        <?php $first = false; endforeach; ?>
      </div>

      <div class="menu-dark-panel-wrap">
        <?php $first = true; foreach ($menu_cats as $cat): ?>
          <div class="menu-dark-panel<?= $first ? ' active' : '' ?>" data-rest-menu-panel="<?= (int) $cat['id'] ?>">
            <?php render_rest_menu_items($menu_items_by_cat[$cat['id']]); ?>
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
    'rest_room' => function (): void {
        if (!section_visible('rest_room')) return;
        ?>
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
        <?php if (setting('rest_room_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('rest_room_btn_url', '#'))) ?>"><?= e(setting('rest_room_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php
    },
    'rest_brunch' => function (): void {
        if (!section_visible('rest_brunch')) return;
        ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('rest_brunch_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('rest_brunch_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('rest_brunch_eyebrow', 'Saturday & Sunday · 11:30 AM - 3:00 PM')) ?></p>
        <h2><?= e(setting('rest_brunch_heading', 'Brunch With Nowhere Else To Be.')) ?></h2>
        <p><?= enl(setting('rest_brunch_p1')) ?></p>
        <p><?= enl(setting('rest_brunch_p2')) ?></p>
        <?php if (setting('rest_brunch_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('rest_brunch_btn_url', '#'))) ?>"><?= e(setting('rest_brunch_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php
    },
    'rest_fridaysat' => function (): void {
        if (!section_visible('rest_fridaysat')) return;
        ?>
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
<?php
    },
    'rest_happyhour' => function (): void {
        if (!section_visible('rest_happyhour')) return;
        ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
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
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('rest_happyhour_image', 'assets/img/restaurant.jpg'))) ?>" alt="<?= e(setting('rest_happyhour_heading')) ?>">
      </div>
    </div>
  </div>
</section>
<?php
    },
    'rest_chef' => function (): void {
        if (!section_visible('rest_chef')) return;
        ?>
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
<?php
    },
    'rest_livelate' => function (): void {
        if (!section_visible('rest_livelate')) return;
        ?>
<section class="split-section reveal">
  <div class="container">
    <div class="split-grid">
      <div class="split-media">
        <img loading="lazy" decoding="async" src="<?= e(asset(setting('rest_livelate_image', 'assets/img/below.jpg'))) ?>" alt="<?= e(setting('rest_livelate_heading')) ?>">
      </div>
      <div class="split-copy">
        <p class="eyebrow"><?= e(setting('rest_livelate_eyebrow', 'Live & Late')) ?></p>
        <h2><?= e(setting('rest_livelate_heading', 'The Night Doesn\'t Stop At Dinner.')) ?></h2>
        <p><?= enl(setting('rest_livelate_p1')) ?></p>
        <p><?= enl(setting('rest_livelate_p2')) ?></p>
        <?php if (setting('rest_livelate_btn_label')): ?>
          <div class="btn-wrap">
            <a class="link-underline" href="<?= e(url(setting('rest_livelate_btn_url', '#'))) ?>"><?= e(setting('rest_livelate_btn_label')) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php
    },
    'rest_guest_cards' => function () use ($guestCards): void {
        if (!$guestCards || !section_visible('rest_guest_cards')) return;
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
    'rest_hours' => function (): void {
        if (!section_visible('rest_hours')) return;
        ?>
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
<?php
    },
    'rest_gallery' => function () use ($gallery, $gallery_rows): void {
        if (!$gallery || !section_visible('rest_gallery')) return;
        ?>
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
<?php
    },
    'rest_faq' => function () use ($faqs): void {
        if (!$faqs || !section_visible('rest_faq')) return;
        ?>
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
<?php
    },
];
foreach (section_order('restaurant', array_keys($rest_sections)) as $rest_key) {
    $rest_sections[$rest_key]();
}
?>

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
  var tabs = document.querySelectorAll('.menu-dark-tab-btn');
  var panels = document.querySelectorAll('.menu-dark-panel');
  if (!tabs.length) return;
  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (b) { b.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('active'); });
      btn.classList.add('active');
      var panel = document.querySelector('.menu-dark-panel[data-rest-menu-panel="' + btn.dataset.restMenuTab + '"]');
      if (panel) panel.classList.add('active');
    });
  });
})();
</script>

<script>
(function () {
  // Sub-tabs within a category (one per section heading, e.g. SNACKS /
  // ENTRÉES / MAINS) - each row's buttons map 1:1 by position to the
  // panels in the sub-panel-wrap right after it, so this works for any
  // number of categories/sub-tabs without needing matching IDs.
  document.querySelectorAll('.menu-dark-subtabs').forEach(function (row) {
    var buttons = Array.prototype.slice.call(row.querySelectorAll('.menu-dark-subtab-btn'));
    var panelWrap = row.nextElementSibling;
    if (!panelWrap) return;
    var panels = Array.prototype.slice.call(panelWrap.querySelectorAll('.menu-dark-subpanel'));
    buttons.forEach(function (btn, i) {
      btn.addEventListener('click', function () {
        buttons.forEach(function (b) { b.classList.remove('active'); });
        panels.forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        if (panels[i]) panels[i].classList.add('active');
      });
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
