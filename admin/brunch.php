<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'herovideo' => ['brunch_hero_video'],
    'intro'     => ['brunch_heading', 'brunch_subheading', 'brunch_lede1', 'brunch_lede2', 'brunch_p1', 'brunch_hours', 'brunch_btn1_label', 'brunch_btn1_url', 'brunch_btn2_label', 'brunch_btn2_url'],
    'menu'      => ['brunch_menu_heading', 'brunch_menu_lede'],
    'guest'     => ['brunch_guest_eyebrow', 'brunch_guest_heading', 'brunch_guest_p1', 'brunch_guest_p2', 'brunch_guest_btn_label', 'brunch_guest_btn_url', 'brunch_guest_image'],
];
$catBlockType = 'brunch_menu_cat';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
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
                header('Location: brunch.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            // ---- Menu categories ----
            if ($action === 'save_categories') {
                foreach (($_POST['cat_title'] ?? []) as $id => $title) {
                    $id = (int) $id;
                    $q = db()->prepare('UPDATE blocks SET title=?, sort=? WHERE id=? AND block_type=?');
                    $q->execute([trim($title), (int) ($_POST['cat_sort'][$id] ?? 0), $id, $catBlockType]);
                }
                header('Location: brunch.php?tab=menu&saved=1'); exit;
            }
            if ($action === 'add_category') {
                $label = trim($_POST['new_cat_title'] ?? '');
                if ($label === '') {
                    $error = 'Enter a category name.';
                } else {
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,sort,active) VALUES (?,?,?,1)');
                    $q->execute([$catBlockType, $label, (int) ($_POST['new_cat_sort'] ?? 99)]);
                    $newId = (int) db()->lastInsertId();
                    header('Location: brunch.php?tab=menu&cat=' . $newId . '&saved=1'); exit;
                }
            }
            if ($action === 'delete_category') {
                $catId = (int) ($_POST['cat_id'] ?? 0);
                db()->prepare('DELETE FROM blocks WHERE id=? AND block_type=?')->execute([$catId, $catBlockType]);
                db()->prepare('DELETE FROM blocks WHERE block_type=?')->execute(['brunch_menu_item_' . $catId]);
                header('Location: brunch.php?tab=menu&saved=1'); exit;
            }

            // ---- Menu items (per category) ----
            if ($action === 'save_menu_items') {
                $catId = (int) ($_POST['cat'] ?? 0);
                $blockType = 'brunch_menu_item_' . $catId;
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id = (int) $id;
                    $q = db()->prepare('UPDATE blocks SET title=?, subtitle=?, body=?, sort=? WHERE id=? AND block_type=?');
                    $q->execute([trim($title), trim($_POST['price'][$id] ?? ''), trim($_POST['desc'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0), $id, $blockType]);
                }
                header('Location: brunch.php?tab=menu&cat=' . $catId . '&saved=1'); exit;
            }
            if ($action === 'add_menu_item') {
                $catId = (int) ($_POST['cat'] ?? 0);
                $q = db()->prepare('INSERT INTO blocks (block_type,title,subtitle,body,sort,active) VALUES (?,?,?,?,?,1)');
                $q->execute(['brunch_menu_item_' . $catId, trim($_POST['new_title'] ?? 'New Item'), trim($_POST['new_price'] ?? ''), trim($_POST['new_desc'] ?? ''), (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: brunch.php?tab=menu&cat=' . $catId . '&saved=1'); exit;
            }
            if ($action === 'delete_menu_item') {
                $catId = (int) ($_POST['cat'] ?? 0);
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type=?');
                $q->execute([(int) ($_POST['id'] ?? 0), 'brunch_menu_item_' . $catId]);
                header('Location: brunch.php?tab=menu&cat=' . $catId . '&saved=1'); exit;
            }

            // ---- Gallery ----
            if ($action === 'save_gallery') {
                foreach (($_POST['g_sort'] ?? []) as $id => $sort) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    if ($img !== null) {
                        $q = db()->prepare('UPDATE blocks SET sort=?, image=? WHERE id=? AND block_type="brunch_gallery"');
                        $q->execute([(int) $sort, $img, $id]);
                    } else {
                        $q = db()->prepare('UPDATE blocks SET sort=? WHERE id=? AND block_type="brunch_gallery"');
                        $q->execute([(int) $sort, $id]);
                    }
                }
                header('Location: brunch.php?tab=gallery&saved=1'); exit;
            }
            if ($action === 'add_gallery') {
                $img = handle_upload('new_file');
                if ($img === null) {
                    $error = 'Choose an image to add to the gallery.';
                } else {
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,image,sort,active) VALUES ("brunch_gallery","",?,?,1)');
                    $q->execute([$img, (int) ($_POST['new_sort'] ?? 99)]);
                    header('Location: brunch.php?tab=gallery&saved=1'); exit;
                }
            }
            if ($action === 'delete_gallery') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="brunch_gallery"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: brunch.php?tab=gallery&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='brunch_page'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}
$menuCats = db()->query("SELECT * FROM blocks WHERE block_type='brunch_menu_cat' ORDER BY sort ASC, id ASC")->fetchAll();
$menuItemsByCat = [];
foreach ($menuCats as $cat) {
    $catId = (int) $cat['id'];
    $menuItemsByCat[$catId] = db()->query("SELECT * FROM blocks WHERE block_type='brunch_menu_item_$catId' ORDER BY sort ASC, id ASC")->fetchAll();
}
$gallery = db()->query("SELECT * FROM blocks WHERE block_type='brunch_gallery' ORDER BY sort ASC, id ASC")->fetchAll();

$tabs = [
    'herovideo' => 'Hero Video',
    'intro'     => 'Intro',
    'gallery'   => 'Gallery',
    'menu'      => 'Menu',
    'guest'     => 'Guest Artists',
];
$activeTab = $_GET['tab'] ?? 'herovideo';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'herovideo';
}
$activeCat = isset($_GET['cat']) ? (int) $_GET['cat'] : ($menuCats[0]['id'] ?? 0);

function render_brunch_field(array $f): void
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
          <input type="file" name="file_<?= e($key) ?>" accept="image/*">
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
          <span class="help">MP4 or WEBM, ideally <strong>1280×720 (720p)</strong>, short and compressed (max 60&nbsp;MB) so it loads quickly. Leave empty to keep the current video.</span>
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
        <?php endif; ?>
      </label>
    </div>
    <?php
}

$csrf = csrf_token();
$page_title = 'Brunch';
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
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="group" value="<?= e($group) ?>">
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_brunch_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<!-- Gallery tab -->
<div class="tab-panel settings-group<?= $activeTab === 'gallery' ? ' active' : '' ?>" data-tab="gallery">
  <h3>Gallery</h3>
  <p class="group-help">The photo grid near the top of the Brunch page.</p>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_gallery">
    <table>
      <tr><th style="width:110px;">Image</th><th style="width:70px;">Order</th><th style="width:220px;">Replace image</th><th style="width:80px;"></th></tr>
      <?php foreach ($gallery as $g): ?>
        <tr>
          <td><?php if ($g['image']): ?><img src="<?= e(asset($g['image'])) ?>" alt=""><?php endif; ?></td>
          <td><input type="text" name="g_sort[<?= $g['id'] ?>]" value="<?= (int)$g['sort'] ?>" style="width:56px;"></td>
          <td><input type="file" name="file_<?= $g['id'] ?>" accept="image/*"></td>
          <td><button form="delgal<?= $g['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this photo?')">Delete</button></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($gallery as $g): ?>
    <form id="delgal<?= $g['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_gallery"><input type="hidden" name="id" value="<?= $g['id'] ?>">
    </form>
  <?php endforeach; ?>
  <h3 style="margin-top:30px;">Add a Photo</h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_gallery">
    <div class="row-actions">
      <input type="file" name="new_file" accept="image/*">
      <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
      <button class="btn btn-sm">Add</button>
    </div>
  </form>
</div>

<!-- Menu tab: settings + per-category item CRUD -->
<div class="tab-panel settings-group<?= $activeTab === 'menu' ? ' active' : '' ?>" data-tab="menu">
  <h3>Menu</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_section">
    <input type="hidden" name="group" value="menu">
    <?php foreach ($fieldGroups['menu'] as $key): if (!isset($bySkey[$key])) continue; render_brunch_field($bySkey[$key]); endforeach; ?>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>

  <h3 style="margin-top:30px;">Menu Categories</h3>
  <p class="group-help">These become the tabs shown in the "Discover Our Menus" section. This is a separate menu from the Restaurant page's menu — editing one does not affect the other. Deleting a category also deletes its menu items.</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_categories">
    <div class="admin-card-count"><?= count($menuCats) ?> categor<?= count($menuCats) === 1 ? 'y' : 'ies' ?></div>
    <div class="admin-card-list">
      <?php foreach ($menuCats as $cat): ?>
        <div class="admin-card">
          <div class="admin-card-fields">
            <div class="field-grid field-grid-cat">
              <label>Category Name
                <input type="text" name="cat_title[<?= $cat['id'] ?>]" value="<?= e($cat['title']) ?>">
              </label>
              <label>Order
                <input type="text" name="cat_sort[<?= $cat['id'] ?>]" value="<?= (int)$cat['sort'] ?>">
              </label>
            </div>
          </div>
          <div class="admin-card-actions">
            <button form="delcat<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category and ALL its menu items? This cannot be undone.')">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
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
  <form method="post" class="admin-card admin-card-add">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_category">
    <div class="admin-card-fields">
      <div class="field-grid field-grid-cat">
        <label>Category Name
          <input type="text" name="new_cat_title" placeholder="e.g. Kids Menu">
        </label>
        <label>Order
          <input type="text" name="new_cat_sort" value="99">
        </label>
      </div>
    </div>
    <div class="admin-card-actions">
      <button class="btn btn-sm">Add Category</button>
    </div>
  </form>

  <h3 style="margin-top:30px;">Menu Items</h3>
  <?php if (!$menuCats): ?>
    <p class="group-help">Add a category above first — its items will appear here once it exists.</p>
  <?php else: ?>
    <div class="tab-nav" style="margin-bottom:18px;">
      <?php foreach ($menuCats as $cat): ?>
        <button type="button" class="tab-btn menu-cat-btn<?= $activeCat === (int)$cat['id'] ? ' active' : '' ?>" data-menu-cat="<?= $cat['id'] ?>"><?= e($cat['title']) ?></button>
      <?php endforeach; ?>
    </div>

    <?php foreach ($menuCats as $cat): $catId = (int) $cat['id']; ?>
      <div class="menu-cat-panel<?= $activeCat === $catId ? ' active' : '' ?>" data-menu-cat-panel="<?= $catId ?>" style="<?= $activeCat === $catId ? '' : 'display:none;' ?>">
        <form method="post">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="save_menu_items">
          <input type="hidden" name="cat" value="<?= $catId ?>">
          <div class="admin-card-count"><?= count($menuItemsByCat[$catId]) ?> item<?= count($menuItemsByCat[$catId]) === 1 ? '' : 's' ?></div>
          <div class="admin-card-list">
            <?php foreach ($menuItemsByCat[$catId] as $item): ?>
              <div class="admin-card">
                <div class="admin-card-fields">
                  <div class="field-row">
                    <label>Item Name
                      <input type="text" name="title[<?= $item['id'] ?>]" value="<?= e($item['title']) ?>">
                    </label>
                  </div>
                  <div class="field-grid">
                    <label>Price
                      <input type="text" name="price[<?= $item['id'] ?>]" value="<?= e($item['subtitle']) ?>">
                    </label>
                    <label>Order
                      <input type="text" name="sort[<?= $item['id'] ?>]" value="<?= (int)$item['sort'] ?>">
                    </label>
                  </div>
                  <div class="field-row">
                    <label>Description
                      <textarea name="desc[<?= $item['id'] ?>]"><?= e($item['body']) ?></textarea>
                    </label>
                  </div>
                </div>
                <div class="admin-card-actions">
                  <button form="delmenu<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">Delete</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
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

        <h3 style="margin-top:24px;font-size:14px;">Add an Item to <?= e($cat['title']) ?></h3>
        <form method="post" class="admin-card admin-card-add">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="add_menu_item">
          <input type="hidden" name="cat" value="<?= $catId ?>">
          <div class="admin-card-fields">
            <div class="field-row">
              <label>Item Name
                <input type="text" name="new_title" placeholder="Item name">
              </label>
            </div>
            <div class="field-grid">
              <label>Price
                <input type="text" name="new_price" placeholder="Price">
              </label>
              <label>Order
                <input type="text" name="new_sort" value="99">
              </label>
            </div>
            <div class="field-row">
              <label>Description
                <textarea name="new_desc" placeholder="Description"></textarea>
              </label>
            </div>
          </div>
          <div class="admin-card-actions">
            <button class="btn btn-sm">Add Item</button>
          </div>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
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
