<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'herovideo' => ['dp_hero_video'],
    'intro'     => ['dp_heading', 'dp_subheading', 'dp_lede1', 'dp_lede2', 'dp_p1', 'dp_hours', 'dp_btn1_label', 'dp_btn1_url', 'dp_btn2_label', 'dp_btn2_url'],
    'menu'      => ['dp_menu_heading', 'dp_menu_lede'],
    'guest'     => ['dp_guest_eyebrow', 'dp_guest_heading', 'dp_guest_p1', 'dp_guest_p2', 'dp_guest_btn_label', 'dp_guest_btn_url', 'dp_guest_image'],
];
$menuCats = [
    'dinner'    => 'Dinner',
    'drinks'    => 'Drink Menu',
    'dessert'   => 'Desert',
    'cocktails' => 'Cocktails',
    'wine'      => 'Wine',
];

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
                header('Location: dinnerparty.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            // ---- Menu items (per category) ----
            if ($action === 'save_menu_items') {
                $cat = $_POST['cat'] ?? '';
                if (isset($menuCats[$cat])) {
                    $blockType = 'dp_menu_' . $cat;
                    foreach (($_POST['title'] ?? []) as $id => $title) {
                        $id = (int) $id;
                        $q = db()->prepare('UPDATE blocks SET title=?, subtitle=?, body=?, sort=? WHERE id=? AND block_type=?');
                        $q->execute([trim($title), trim($_POST['price'][$id] ?? ''), trim($_POST['desc'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0), $id, $blockType]);
                    }
                }
                header('Location: dinnerparty.php?tab=menu&cat=' . urlencode($cat) . '&saved=1'); exit;
            }
            if ($action === 'add_menu_item') {
                $cat = $_POST['cat'] ?? '';
                if (isset($menuCats[$cat])) {
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,subtitle,body,sort,active) VALUES (?,?,?,?,?,1)');
                    $q->execute(['dp_menu_' . $cat, trim($_POST['new_title'] ?? 'New Item'), trim($_POST['new_price'] ?? ''), trim($_POST['new_desc'] ?? ''), (int) ($_POST['new_sort'] ?? 99)]);
                }
                header('Location: dinnerparty.php?tab=menu&cat=' . urlencode($cat) . '&saved=1'); exit;
            }
            if ($action === 'delete_menu_item') {
                $cat = $_POST['cat'] ?? '';
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type=?');
                $q->execute([(int) ($_POST['id'] ?? 0), 'dp_menu_' . $cat]);
                header('Location: dinnerparty.php?tab=menu&cat=' . urlencode($cat) . '&saved=1'); exit;
            }

            // ---- Gallery ----
            if ($action === 'save_gallery') {
                foreach (($_POST['g_sort'] ?? []) as $id => $sort) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    if ($img !== null) {
                        $q = db()->prepare('UPDATE blocks SET sort=?, image=? WHERE id=? AND block_type="dp_gallery"');
                        $q->execute([(int) $sort, $img, $id]);
                    } else {
                        $q = db()->prepare('UPDATE blocks SET sort=? WHERE id=? AND block_type="dp_gallery"');
                        $q->execute([(int) $sort, $id]);
                    }
                }
                header('Location: dinnerparty.php?tab=gallery&saved=1'); exit;
            }
            if ($action === 'add_gallery') {
                $img = handle_upload('new_file');
                if ($img === null) {
                    $error = 'Choose an image to add to the gallery.';
                } else {
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,image,sort,active) VALUES ("dp_gallery","",?,?,1)');
                    $q->execute([$img, (int) ($_POST['new_sort'] ?? 99)]);
                    header('Location: dinnerparty.php?tab=gallery&saved=1'); exit;
                }
            }
            if ($action === 'delete_gallery') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="dp_gallery"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: dinnerparty.php?tab=gallery&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='dinnerparty_page'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}
$menuItemsByCat = [];
foreach ($menuCats as $cat => $label) {
    $menuItemsByCat[$cat] = db()->query("SELECT * FROM blocks WHERE block_type='dp_menu_$cat' ORDER BY sort ASC, id ASC")->fetchAll();
}
$gallery = db()->query("SELECT * FROM blocks WHERE block_type='dp_gallery' ORDER BY sort ASC, id ASC")->fetchAll();

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
$activeCat = $_GET['cat'] ?? 'dinner';
if (!isset($menuCats[$activeCat])) {
    $activeCat = 'dinner';
}

function render_dp_field(array $f): void
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
$page_title = 'Dinner Party';
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
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_dp_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<!-- Gallery tab -->
<div class="tab-panel settings-group<?= $activeTab === 'gallery' ? ' active' : '' ?>" data-tab="gallery">
  <h3>Gallery</h3>
  <p class="group-help">The photo grid near the top of the Dinner Party page.</p>
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
    <?php foreach ($fieldGroups['menu'] as $key): if (!isset($bySkey[$key])) continue; render_dp_field($bySkey[$key]); endforeach; ?>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>

  <h3 style="margin-top:30px;">Menu Items</h3>
  <p class="group-help">This is a separate menu from the Restaurant and Brunch pages' menus — editing one does not affect the others.</p>
  <div class="tab-nav" style="margin-bottom:18px;">
    <?php foreach ($menuCats as $cat => $label): ?>
      <button type="button" class="tab-btn menu-cat-btn<?= $activeCat === $cat ? ' active' : '' ?>" data-menu-cat="<?= e($cat) ?>"><?= e($label) ?></button>
    <?php endforeach; ?>
  </div>

  <?php foreach ($menuCats as $cat => $label): ?>
    <div class="menu-cat-panel<?= $activeCat === $cat ? ' active' : '' ?>" data-menu-cat-panel="<?= e($cat) ?>" style="<?= $activeCat === $cat ? '' : 'display:none;' ?>">
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="save_menu_items">
        <input type="hidden" name="cat" value="<?= e($cat) ?>">
        <table>
          <tr><th>Item</th><th style="width:100px;">Price</th><th>Description</th><th style="width:70px;">Order</th><th style="width:80px;"></th></tr>
          <?php foreach ($menuItemsByCat[$cat] as $item): ?>
            <tr>
              <td><input type="text" name="title[<?= $item['id'] ?>]" value="<?= e($item['title']) ?>"></td>
              <td><input type="text" name="price[<?= $item['id'] ?>]" value="<?= e($item['subtitle']) ?>"></td>
              <td><input type="text" name="desc[<?= $item['id'] ?>]" value="<?= e($item['body']) ?>"></td>
              <td><input type="text" name="sort[<?= $item['id'] ?>]" value="<?= (int)$item['sort'] ?>" style="width:56px;"></td>
              <td><button form="delmenu<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">Delete</button></td>
            </tr>
          <?php endforeach; ?>
        </table>
        <div class="form-actions"><button class="btn">Save <?= e($label) ?> Items</button></div>
      </form>
      <?php foreach ($menuItemsByCat[$cat] as $item): ?>
        <form id="delmenu<?= $item['id'] ?>" method="post" class="inline-form">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="action" value="delete_menu_item">
          <input type="hidden" name="cat" value="<?= e($cat) ?>">
          <input type="hidden" name="id" value="<?= $item['id'] ?>">
        </form>
      <?php endforeach; ?>

      <h3 style="margin-top:24px;font-size:14px;">Add an Item to <?= e($label) ?></h3>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="add_menu_item">
        <input type="hidden" name="cat" value="<?= e($cat) ?>">
        <div class="row-actions">
          <input type="text" name="new_title" placeholder="Item name" style="max-width:200px;">
          <input type="text" name="new_price" placeholder="Price" style="width:80px;">
          <input type="text" name="new_desc" placeholder="Description" style="max-width:260px;">
          <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
          <button class="btn btn-sm">Add</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
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
