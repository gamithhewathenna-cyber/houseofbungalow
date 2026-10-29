<?php
require_once __DIR__ . '/auth.php';
require_role('content');
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'herovideo' => ['rest_hero_video', 'rest_opentable_embed'],
    'hero'      => ['rest_heading', 'rest_subheading', 'rest_lede', 'rest_intro_p1', 'rest_intro_p2', 'rest_intro_btn_label', 'rest_intro_btn_url'],
    'food'      => ['rest_food_eyebrow', 'rest_food_heading', 'rest_food_p1', 'rest_food_p2', 'rest_food_image'],
    'menu'      => ['rest_menu_heading', 'rest_menu_lede'],
    'room'      => ['rest_room_eyebrow', 'rest_room_heading', 'rest_room_p1', 'rest_room_p2', 'rest_room_image'],
    'fridaysat' => ['rest_fridaysat_eyebrow', 'rest_fridaysat_heading', 'rest_fridaysat_p1', 'rest_fridaysat_p2', 'rest_fridaysat_btn_label', 'rest_fridaysat_btn_url', 'rest_fridaysat_image'],
    'happyhour' => ['rest_happyhour_eyebrow', 'rest_happyhour_heading', 'rest_happyhour_p1', 'rest_happyhour_hours1', 'rest_happyhour_hours2', 'rest_happyhour_btn_label', 'rest_happyhour_btn_url', 'rest_happyhour_image'],
    'chef'      => ['rest_chef_eyebrow', 'rest_chef_name', 'rest_chef_p1', 'rest_chef_p2', 'rest_chef_p3', 'rest_chef_image'],
    'hours'     => ['rest_hours_heading', 'rest_hours_lunch_label', 'rest_hours_lunch', 'rest_hours_dinner_label', 'rest_hours_dinner', 'rest_hours_fridaysat_label', 'rest_hours_fridaysat', 'rest_hours_note', 'rest_hours_btn_label', 'rest_hours_btn_url', 'rest_hours_footnote'],
];
$catBlockType = 'rest_menu_cat';
$visibilitySections = [
    'rest_hero'      => 'Hero',
    'rest_intro'     => 'Hero & Intro Text',
    'rest_food'      => 'The Food',
    'rest_menu'      => 'Menu',
    'rest_room'      => 'The Room',
    'rest_fridaysat' => 'Friday & Saturday',
    'rest_happyhour' => 'Happy Hour',
    'rest_chef'      => 'The Chef',
    'rest_hours'     => 'Restaurant Hours',
    'rest_gallery'   => 'Gallery',
    'rest_faq'       => 'FAQs',
];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            // ---- Section visibility ----
            if ($action === 'save_visibility') {
                foreach ($visibilitySections as $vkey => $vlabel) {
                    save_setting($vkey . '_visible', isset($_POST['vis_' . $vkey]) ? '1' : '0');
                }
                header('Location: restaurant.php?tab=visibility&saved=1'); exit;
            }

            // ---- Generic settings groups ----
            if ($action === 'save_section') {
                $group = $_POST['group'] ?? '';
                if (isset($fieldGroups[$group])) {
                    $keys = $fieldGroups[$group];
                    $placeholders = implode(',', array_fill(0, count($keys), '?'));
                    $stmt = db()->prepare("SELECT * FROM settings WHERE skey IN ($placeholders)");
                    $stmt->execute($keys);
                    foreach ($stmt->fetchAll() as $f) {
                        $key = $f['skey']; $type = $f['field_type'];
                        if ($type === 'image') {
                            $uploaded = handle_upload('file_' . $key);
                            if ($uploaded !== null) {
                                save_setting($key, $uploaded);
                            }
                        } elseif ($type === 'video') {
                            $uploaded = handle_video_upload('file_' . $key);
                            if ($uploaded !== null) {
                                save_setting($key, $uploaded);
                            } elseif (isset($_POST['remove_' . $key])) {
                                save_setting($key, '');
                            }
                        } else {
                            if (array_key_exists($key, $_POST)) {
                                save_setting($key, $_POST[$key]);
                            }
                        }
                    }
                }
                header('Location: restaurant.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            // ---- Menu categories ----
            if ($action === 'save_categories') {
                foreach (($_POST['cat_title'] ?? []) as $id => $title) {
                    $id = (int) $id;
                    $catImg = handle_upload('cat_file_' . $id);
                    if ($catImg !== null) {
                        db()->prepare('UPDATE blocks SET image=? WHERE id=? AND block_type=?')->execute([$catImg, $id, $catBlockType]);
                    }
                    $catPdf = handle_document_upload('cat_pdf_' . $id);
                    if ($catPdf !== null) {
                        db()->prepare('UPDATE blocks SET link_url=? WHERE id=? AND block_type=?')->execute([$catPdf, $id, $catBlockType]);
                    } elseif (isset($_POST['cat_pdf_remove'][$id])) {
                        db()->prepare('UPDATE blocks SET link_url=? WHERE id=? AND block_type=?')->execute(['', $id, $catBlockType]);
                    }
                    $q = db()->prepare('UPDATE blocks SET title=?, subtitle=?, body=?, sort=? WHERE id=? AND block_type=?');
                    $q->execute([
                        trim($title),
                        trim($_POST['cat_eyebrow'][$id] ?? ''),
                        trim($_POST['cat_subheading'][$id] ?? ''),
                        (int) ($_POST['cat_sort'][$id] ?? 0),
                        $id,
                        $catBlockType,
                    ]);
                }
                header('Location: restaurant.php?tab=menu&saved=1'); exit;
            }
            if ($action === 'add_category') {
                $label = trim($_POST['new_cat_title'] ?? '');
                if ($label === '') {
                    $error = 'Enter a category name.';
                } else {
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,sort,active) VALUES (?,?,?,1)');
                    $q->execute([$catBlockType, $label, (int) ($_POST['new_cat_sort'] ?? 99)]);
                    $newId = (int) db()->lastInsertId();
                    header('Location: restaurant.php?tab=menu&cat=' . $newId . '&saved=1'); exit;
                }
            }
            if ($action === 'delete_category') {
                $catId = (int) ($_POST['cat_id'] ?? 0);
                db()->prepare('DELETE FROM blocks WHERE id=? AND block_type=?')->execute([$catId, $catBlockType]);
                db()->prepare('DELETE FROM blocks WHERE block_type=?')->execute(['rest_menu_item_' . $catId]);
                header('Location: restaurant.php?tab=menu&saved=1'); exit;
            }

            // ---- Menu items (per category) ----
            if ($action === 'save_menu_items') {
                $catId = (int) ($_POST['cat'] ?? 0);
                $blockType = 'rest_menu_item_' . $catId;
                $allowedTypes = ['', 'title', 'heading', 'line', 'note'];
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id = (int) $id;
                    $type = $_POST['type'][$id] ?? '';
                    if (!in_array($type, $allowedTypes, true)) { $type = ''; }
                    $q = db()->prepare('UPDATE blocks SET title=?, subtitle=?, body=?, link_url=?, sort=? WHERE id=? AND block_type=?');
                    $q->execute([
                        trim($title),
                        trim($_POST['price'][$id] ?? ''),
                        trim($_POST['desc'][$id] ?? ''),
                        $type,
                        (int) ($_POST['sort'][$id] ?? 0),
                        $id,
                        $blockType,
                    ]);
                }
                header('Location: restaurant.php?tab=menu&cat=' . $catId . '&saved=1'); exit;
            }
            if ($action === 'add_menu_item') {
                $catId = (int) ($_POST['cat'] ?? 0);
                $allowedTypes = ['', 'title', 'heading', 'line', 'note'];
                $newType = $_POST['new_type'] ?? '';
                if (!in_array($newType, $allowedTypes, true)) { $newType = ''; }
                $q = db()->prepare('INSERT INTO blocks (block_type,title,subtitle,body,link_url,sort,active) VALUES (?,?,?,?,?,?,1)');
                $q->execute(['rest_menu_item_' . $catId, trim($_POST['new_title'] ?? 'New Item'), trim($_POST['new_price'] ?? ''), trim($_POST['new_desc'] ?? ''), $newType, (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: restaurant.php?tab=menu&cat=' . $catId . '&saved=1'); exit;
            }
            if ($action === 'delete_menu_item') {
                $catId = (int) ($_POST['cat'] ?? 0);
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type=?');
                $q->execute([(int) ($_POST['id'] ?? 0), 'rest_menu_item_' . $catId]);
                header('Location: restaurant.php?tab=menu&cat=' . $catId . '&saved=1'); exit;
            }

            // ---- Gallery ----
            if ($action === 'save_gallery') {
                foreach (($_POST['g_sort'] ?? []) as $id => $sort) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    if ($img !== null) {
                        $q = db()->prepare('UPDATE blocks SET sort=?, image=? WHERE id=? AND block_type="rest_gallery"');
                        $q->execute([(int) $sort, $img, $id]);
                    } else {
                        $q = db()->prepare('UPDATE blocks SET sort=? WHERE id=? AND block_type="rest_gallery"');
                        $q->execute([(int) $sort, $id]);
                    }
                }
                header('Location: restaurant.php?tab=gallery&saved=1'); exit;
            }
            if ($action === 'add_gallery') {
                $img = handle_upload('new_file');
                if ($img === null) {
                    $error = 'Choose an image to add to the gallery.';
                } else {
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,image,sort,active) VALUES ("rest_gallery","",?,?,1)');
                    $q->execute([$img, (int) ($_POST['new_sort'] ?? 99)]);
                    header('Location: restaurant.php?tab=gallery&saved=1'); exit;
                }
            }
            if ($action === 'delete_gallery') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="rest_gallery"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: restaurant.php?tab=gallery&saved=1'); exit;
            }

            // ---- FAQs ----
            if ($action === 'save_faqs') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id = (int) $id;
                    $q = db()->prepare('UPDATE blocks SET title=?, body=?, sort=? WHERE id=? AND block_type="rest_faq"');
                    $q->execute([trim($title), trim($_POST['desc'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0), $id]);
                }
                header('Location: restaurant.php?tab=faqs&saved=1'); exit;
            }
            if ($action === 'add_faq') {
                $q = db()->prepare('INSERT INTO blocks (block_type,title,body,sort,active) VALUES ("rest_faq",?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New Question'), trim($_POST['new_desc'] ?? ''), (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: restaurant.php?tab=faqs&saved=1'); exit;
            }
            if ($action === 'delete_faq') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="rest_faq"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: restaurant.php?tab=faqs&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

// ---- Load data ----
$allRestRows = db()->query("SELECT * FROM settings WHERE section='restaurant'")->fetchAll();
$bySkey = [];
foreach ($allRestRows as $r) {
    $bySkey[$r['skey']] = $r;
}
$menuCats = db()->query("SELECT * FROM blocks WHERE block_type='rest_menu_cat' ORDER BY sort ASC, id ASC")->fetchAll();
$menuItemsByCat = [];
foreach ($menuCats as $cat) {
    $catId = (int) $cat['id'];
    $menuItemsByCat[$catId] = db()->query("SELECT * FROM blocks WHERE block_type='rest_menu_item_$catId' ORDER BY sort ASC, id ASC")->fetchAll();
}
$gallery = db()->query("SELECT * FROM blocks WHERE block_type='rest_gallery' ORDER BY sort ASC, id ASC")->fetchAll();
$faqs    = db()->query("SELECT * FROM blocks WHERE block_type='rest_faq' ORDER BY sort ASC, id ASC")->fetchAll();

$tabs = [
    'herovideo' => 'Hero Video',
    'hero'      => 'Hero & Intro',
    'food'      => 'The Food',
    'menu'      => 'Menu',
    'room'      => 'The Room',
    'fridaysat' => 'Friday & Saturday',
    'happyhour' => 'Happy Hour',
    'chef'      => 'The Chef',
    'hours'     => 'Restaurant Hours',
    'gallery'   => 'Gallery',
    'faqs'      => 'FAQs',
    'visibility' => 'Section Visibility',
];
$activeTab = $_GET['tab'] ?? 'hero';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'hero';
}
$activeCat = isset($_GET['cat']) ? (int) $_GET['cat'] : ($menuCats[0]['id'] ?? 0);

function render_rest_field(array $f): void
{
    $key = $f['skey']; $val = $f['svalue']; $type = $f['field_type'];
    ?>
    <div class="field-row">
      <label>
        <?= e($f['label'] ?: $key) ?>
        <?php if ($type === 'textarea'): ?>
          <textarea name="<?= e($key) ?>"><?= e($val) ?></textarea>
        <?php elseif ($type === 'image'): ?>
          <?php if ($val): ?>
            <div class="thumb-preview"><img src="<?= e(asset($val)) ?>" alt=""></div>
          <?php endif; ?>
          <input type="file" name="file_<?= e($key) ?>" accept="image/*" data-crop-ratio="<?= e(crop_ratio_for_key($key)) ?>">
          <span class="help">Current: <code><?= e($val ?: 'none') ?></code>. Leave empty to keep it.</span>
        <?php elseif ($type === 'video'): ?>
          <?php if ($val): ?>
            <div class="thumb-preview">
              <video src="<?= e(asset($val)) ?>" style="max-height:160px;max-width:100%;" controls muted></video>
            </div>
            <span class="help" style="display:block;margin:6px 0;">
              <input type="checkbox" name="remove_<?= e($key) ?>" value="1"> Remove this video (revert to the static hero image)
            </span>
          <?php endif; ?>
          <input type="file" name="file_<?= e($key) ?>" accept="video/mp4,video/webm,video/quicktime">
          <span class="help"><strong>MP4 (H.264) recommended</strong> - WEBM will not play in Safari. Ideally 1280×720 (720p), short and compressed (max 60&nbsp;MB) so it loads quickly. Leave empty to keep the current video.</span>
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
        <?php endif; ?>
      </label>
    </div>
    <?php
}

$csrf = csrf_token();
$page_title = 'Restaurant & Menus';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<div class="tab-nav">
  <?php foreach ($tabs as $key => $label): ?>
    <button type="button" class="tab-btn<?= $activeTab === $key ? ' active' : '' ?>" data-tab="<?= e($key) ?>"><?= e($label) ?></button>
  <?php endforeach; ?>
</div>

<?php foreach ($fieldGroups as $group => $keys): if ($group === 'menu') continue; ?>
  <div class="tab-panel settings-group<?= $activeTab === $group ? ' active' : '' ?>" data-tab="<?= e($group) ?>">
    <h3><?= e($tabs[$group]) ?></h3>
    <?php if ($group === 'herovideo'): ?>
      <p class="group-help">The OpenTable widget shows above the hero video/image, at the very top of the page. Paste a full replacement embed code from OpenTable to change it, or clear the field to hide it entirely.</p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="group" value="<?= e($group) ?>">
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_rest_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<!-- Menu tab: settings + categories + per-category item CRUD -->
<div class="tab-panel settings-group<?= $activeTab === 'menu' ? ' active' : '' ?>" data-tab="menu">
  <h3>Menu</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_section">
    <input type="hidden" name="group" value="menu">
    <?php foreach ($fieldGroups['menu'] as $key): if (!isset($bySkey[$key])) continue; render_rest_field($bySkey[$key]); endforeach; ?>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>

  <h3 style="margin-top:30px;">Menu Categories</h3>
  <p class="group-help">These show as cards in the "Discover Our Menus" section (e.g. "À La Carte", "Beverage Menu"), each with a photo, an optional small eyebrow label, the heading, a one-line subheading, and a "View [Name]" button that expands to show its menu items. An optional PDF adds a separate "Download Menu" button under that expander. Add, rename, reorder or remove categories here - deleting one also deletes its menu items.</p>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_categories">
    <table>
      <tr><th style="width:90px;">Photo</th><th>Name (heading)</th><th>Eyebrow (optional)</th><th>Subheading</th><th style="width:150px;">Download Menu PDF</th><th style="width:70px;">Order</th><th style="width:90px;"></th></tr>
      <?php foreach ($menuCats as $cat): ?>
        <tr>
          <td>
            <?php if ($cat['image']): ?><img src="<?= e(asset($cat['image'])) ?>" alt="" style="width:70px;height:48px;object-fit:cover;border-radius:4px;display:block;margin-bottom:6px;"><?php endif; ?>
            <input type="file" name="cat_file_<?= $cat['id'] ?>" accept="image/*" data-crop-ratio="16:9" style="width:90px;">
          </td>
          <td><input type="text" name="cat_title[<?= $cat['id'] ?>]" value="<?= e($cat['title']) ?>"></td>
          <td><input type="text" name="cat_eyebrow[<?= $cat['id'] ?>]" value="<?= e($cat['subtitle']) ?>" placeholder="Small label above heading"></td>
          <td><input type="text" name="cat_subheading[<?= $cat['id'] ?>]" value="<?= e($cat['body']) ?>" placeholder="e.g. Designed to be shared"></td>
          <td>
            <?php if ($cat['link_url']): ?>
              <div style="margin-bottom:6px;"><a href="<?= e(asset($cat['link_url'])) ?>" target="_blank" rel="noopener">Current PDF</a></div>
              <label style="font-size:11px;display:block;margin-bottom:4px;"><input type="checkbox" name="cat_pdf_remove[<?= $cat['id'] ?>]" value="1"> Remove</label>
            <?php endif; ?>
            <input type="file" name="cat_pdf_<?= $cat['id'] ?>" accept="application/pdf" style="width:140px;">
          </td>
          <td><input type="text" name="cat_sort[<?= $cat['id'] ?>]" value="<?= (int)$cat['sort'] ?>"></td>
          <td><button form="delcat<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category and ALL its menu items? This cannot be undone.')">Delete</button></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button class="btn">Save Categories</button></div>
  </form>
  <?php foreach ($menuCats as $cat): ?>
    <form id="delcat<?= $cat['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="delete_category">
      <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h4 style="margin-top:20px;font-size:14px;">Add a Category</h4>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_category">
    <div class="row-actions">
      <input type="text" name="new_cat_title" placeholder="e.g. Kids Menu" style="max-width:220px;">
      <input type="text" name="new_cat_sort" placeholder="Order" value="99" style="width:70px;">
      <button class="btn btn-sm">+ Add Category</button>
    </div>
  </form>

  <hr style="margin:34px 0;border:0;border-top:1px solid #e0d3c4;">
  <h3>Menu Items</h3>
  <?php if (!$menuCats): ?>
    <p class="group-help">Add a category above first - its items will appear here once it exists.</p>
  <?php else: ?>
    <p class="group-help"><strong>This is where you edit the actual dishes, drinks and prices</strong> - click a category name below to open and edit everything inside it.</p>
    <div class="tab-nav" style="margin-bottom:18px;">
      <?php foreach ($menuCats as $cat): ?>
        <button type="button" class="tab-btn menu-cat-btn<?= $activeCat === (int)$cat['id'] ? ' active' : '' ?>" data-menu-cat="<?= $cat['id'] ?>"><?= e($cat['title']) ?></button>
      <?php endforeach; ?>
    </div>

    <?php
    $menuTypeLabels = [
        ''        => 'Menu item (name + price + description)',
        'title'   => 'Page title (big heading, e.g. "Cocktails") - description box becomes its subtitle',
        'heading' => 'Section heading (e.g. "SNACKS", "TO START")',
        'line'    => 'Plain line of text - put the text in the Description box',
        'note'    => 'Small footnote at the end - put the text in the Description box',
    ];
    ?>
    <p class="group-help">Most rows are a normal <strong>Menu item</strong> (name, price, description). To build a page like a set banquet or a multi-section drinks list, change a row's <strong>Type</strong>: use <strong>Page title</strong> for a big heading (put its subtitle in Description), <strong>Section heading</strong> for an uppercase label like "SNACKS" or "TO START", <strong>Plain line</strong> for a line with no price (put the text in Description), and <strong>Note</strong> for the small footnote at the end.</p>

    <?php foreach ($menuCats as $cat): $catId = (int) $cat['id']; ?>
      <div class="menu-cat-panel<?= $activeCat === $catId ? ' active' : '' ?>" data-menu-cat-panel="<?= $catId ?>" style="<?= $activeCat === $catId ? '' : 'display:none;' ?>">
        <form method="post">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="save_menu_items">
          <input type="hidden" name="cat" value="<?= $catId ?>">
          <table>
            <tr><th style="width:150px;">Type</th><th>Item / Heading</th><th style="width:100px;">Price</th><th>Description</th><th style="width:70px;">Order</th><th style="width:90px;"></th></tr>
            <?php foreach ($menuItemsByCat[$catId] as $item): ?>
              <tr>
                <td>
                  <select name="type[<?= $item['id'] ?>]">
                    <?php foreach ($menuTypeLabels as $tVal => $tLabel): ?>
                      <option value="<?= e($tVal) ?>"<?= $item['link_url'] === $tVal ? ' selected' : '' ?>><?= e($tLabel) ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td><input type="text" name="title[<?= $item['id'] ?>]" value="<?= e($item['title']) ?>"></td>
                <td><input type="text" name="price[<?= $item['id'] ?>]" value="<?= e($item['subtitle']) ?>"></td>
                <td><textarea name="desc[<?= $item['id'] ?>]" rows="2" style="min-height:0;"><?= e($item['body']) ?></textarea></td>
                <td><input type="text" name="sort[<?= $item['id'] ?>]" value="<?= (int)$item['sort'] ?>"></td>
                <td><button form="delmenu<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">Delete</button></td>
              </tr>
            <?php endforeach; ?>
          </table>
          <div class="form-actions"><button class="btn">Save <?= e($cat['title']) ?> Items</button></div>
        </form>
        <?php foreach ($menuItemsByCat[$catId] as $item): ?>
          <form id="delmenu<?= $item['id'] ?>" method="post" class="inline-form">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="delete_menu_item">
            <input type="hidden" name="cat" value="<?= $catId ?>">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
          </form>
        <?php endforeach; ?>

        <h3 style="margin-top:24px;font-size:14px;">Add a Row to <?= e($cat['title']) ?></h3>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="add_menu_item">
          <input type="hidden" name="cat" value="<?= $catId ?>">
          <div class="row-actions">
            <select name="new_type">
              <?php foreach ($menuTypeLabels as $tVal => $tLabel): ?>
                <option value="<?= e($tVal) ?>"><?= e($tLabel) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="text" name="new_title" placeholder="Item / heading name" style="max-width:200px;">
            <input type="text" name="new_price" placeholder="Price" style="width:80px;">
            <input type="text" name="new_desc" placeholder="Description" style="max-width:260px;">
            <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
            <button class="btn btn-sm">+ Add Row</button>
          </div>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Gallery tab -->
<div class="tab-panel settings-group<?= $activeTab === 'gallery' ? ' active' : '' ?>" data-tab="gallery">
  <h3>Gallery</h3>
  <p class="group-help">The photo grid near the bottom of the Restaurant page.</p>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_gallery">
    <table>
      <tr><th style="width:110px;">Image</th><th style="width:70px;">Order</th><th style="width:220px;">Replace image</th><th style="width:80px;"></th></tr>
      <?php foreach ($gallery as $g): ?>
        <tr>
          <td><?php if ($g['image']): ?><img src="<?= e(asset($g['image'])) ?>" alt=""><?php endif; ?></td>
          <td><input type="text" name="g_sort[<?= $g['id'] ?>]" value="<?= (int)$g['sort'] ?>" style="width:56px;"></td>
          <td><input type="file" name="file_<?= $g['id'] ?>" accept="image/*" data-crop-ratio="3:2"></td>
          <td><button form="delgal<?= $g['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this photo?')">Delete</button></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($gallery as $g): ?>
    <form id="delgal<?= $g['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="delete_gallery">
      <input type="hidden" name="id" value="<?= $g['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h3 style="margin-top:30px;">Add a Photo</h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_gallery">
    <div class="row-actions">
      <input type="file" name="new_file" accept="image/*" data-crop-ratio="3:2">
      <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
      <button class="btn btn-sm">Add</button>
    </div>
  </form>
</div>

<!-- FAQs tab -->
<div class="tab-panel settings-group<?= $activeTab === 'faqs' ? ' active' : '' ?>" data-tab="faqs">
  <h3>FAQs</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_faqs">
    <table>
      <tr><th>Question</th><th>Answer</th><th style="width:70px;">Order</th><th style="width:80px;"></th></tr>
      <?php foreach ($faqs as $f): ?>
        <tr>
          <td><input type="text" name="title[<?= $f['id'] ?>]" value="<?= e($f['title']) ?>"></td>
          <td><textarea name="desc[<?= $f['id'] ?>]" rows="2" style="min-height:0;"><?= e($f['body']) ?></textarea></td>
          <td><input type="text" name="sort[<?= $f['id'] ?>]" value="<?= (int)$f['sort'] ?>" style="width:56px;"></td>
          <td><button form="delfaq<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this question?')">Delete</button></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($faqs as $f): ?>
    <form id="delfaq<?= $f['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_faq"><input type="hidden" name="id" value="<?= $f['id'] ?>">
    </form>
  <?php endforeach; ?>
  <h3 style="margin-top:24px;font-size:14px;">Add a Question</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_faq">
    <div class="row-actions">
      <input type="text" name="new_title" placeholder="Question" style="max-width:260px;">
      <input type="text" name="new_desc" placeholder="Answer" style="max-width:320px;">
      <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
      <button class="btn btn-sm">Add</button>
    </div>
  </form>
</div>

<div class="tab-panel settings-group<?= $activeTab === 'visibility' ? ' active' : '' ?>" data-tab="visibility">
  <h3>Section Visibility</h3>
  <p class="group-help">Untick a section to hide it from the live Restaurant page without deleting its content.</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_visibility">
    <div class="visibility-list">
      <?php foreach ($visibilitySections as $vkey => $vlabel): ?>
        <label class="visibility-row">
          <input type="checkbox" name="vis_<?= e($vkey) ?>" value="1"<?= section_visible($vkey) ? ' checked' : '' ?>>
          <?= e($vlabel) ?>
        </label>
      <?php endforeach; ?>
    </div>
    <div class="form-actions"><button class="btn">Save Visibility</button></div>
  </form>
</div>

<script>
document.querySelectorAll('.tab-btn:not(.menu-cat-btn)').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.querySelectorAll('.tab-btn:not(.menu-cat-btn)').forEach(function (b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });
    btn.classList.add('active');
    var panel = document.querySelector('.tab-panel[data-tab="' + btn.dataset.tab + '"]');
    if (panel) panel.classList.add('active');
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, '', '?tab=' + btn.dataset.tab);
    }
  });
});
document.querySelectorAll('.menu-cat-btn').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.querySelectorAll('.menu-cat-btn').forEach(function (b) { b.classList.remove('active'); });
    document.querySelectorAll('.menu-cat-panel').forEach(function (p) { p.style.display = 'none'; });
    btn.classList.add('active');
    var panel = document.querySelector('.menu-cat-panel[data-menu-cat-panel="' + btn.dataset.menuCat + '"]');
    if (panel) panel.style.display = 'block';
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, '', '?tab=menu&cat=' + btn.dataset.menuCat);
    }
  });
});
</script>

<?php include __DIR__ . '/layout_end.php'; ?>
