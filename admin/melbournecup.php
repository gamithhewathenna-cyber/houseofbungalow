<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'herovideo' => ['mcup_hero_image', 'mcup_hero_video'],
    'intro'     => ['mcup_heading', 'mcup_subheading', 'mcup_lede', 'mcup_p1', 'mcup_p2', 'mcup_p3', 'mcup_date', 'mcup_btn2_label', 'mcup_btn2_url'],
    'restaurant' => ['mcup_rest_image', 'mcup_rest_eyebrow', 'mcup_rest_heading', 'mcup_rest_price_eyebrow', 'mcup_rest_price', 'mcup_rest_price_note', 'mcup_rest_expander1_label', 'mcup_rest_expander2_label', 'mcup_rest_btn_label', 'mcup_rest_btn_url'],
    'restbev'   => ['mcup_rest_bev_intro', 'mcup_rest_bev_standard_heading', 'mcup_rest_bev_standard_price', 'mcup_rest_bev_standard_items', 'mcup_rest_bev_premium_heading', 'mcup_rest_bev_premium_price', 'mcup_rest_bev_premium_items', 'mcup_rest_bev_note1', 'mcup_rest_bev_note2'],
    'below'     => ['mcup_below_image', 'mcup_below_eyebrow', 'mcup_below_heading', 'mcup_below_price_eyebrow', 'mcup_below_price', 'mcup_below_expander1_label', 'mcup_below_expander2_label', 'mcup_below_btn_label', 'mcup_below_btn_url'],
    'belowdrinks' => ['mcup_below_drinks_lede', 'mcup_below_drinks_champagne_items', 'mcup_below_drinks_white_items', 'mcup_below_drinks_rose_items', 'mcup_below_drinks_red_items', 'mcup_below_drinks_beer_items', 'mcup_below_drinks_cocktails_items'],
];
$menuFamilies = [
    'restmenu'  => ['label' => 'Three-Course Menu', 'catType' => 'mcup_rest_menu_cat',  'itemPrefix' => 'mcup_rest_menu_item_'],
    'belowmenu' => ['label' => 'Canapé Menu',       'catType' => 'mcup_below_menu_cat', 'itemPrefix' => 'mcup_below_menu_item_'],
];
$visibilitySections = [
    'mcup_hero'       => 'Hero',
    'mcup_intro'      => 'Top Intro',
    'mcup_restaurant' => 'At The Restaurant (heading, pricing & its two expanders)',
    'mcup_below'      => 'At Below (heading, pricing & its two expanders)',
    'mcup_menus'      => 'The whole dark menu section (both of the above)',
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
                header('Location: melbournecup.php?tab=visibility&saved=1'); exit;
            }

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
                header('Location: melbournecup.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            // ---- Menu categories (shared by both menu families) ----
            if ($action === 'save_categories') {
                $fam = $_POST['fam'] ?? '';
                if (isset($menuFamilies[$fam])) {
                    $catType = $menuFamilies[$fam]['catType'];
                    foreach (($_POST['cat_title'] ?? []) as $id => $title) {
                        $id = (int) $id;
                        $q = db()->prepare('UPDATE blocks SET title=?, subtitle=?, sort=? WHERE id=? AND block_type=?');
                        $q->execute([trim($title), trim($_POST['cat_note'][$id] ?? ''), (int) ($_POST['cat_sort'][$id] ?? 0), $id, $catType]);
                    }
                }
                header('Location: melbournecup.php?tab=' . urlencode($fam) . '&saved=1'); exit;
            }
            if ($action === 'add_category') {
                $fam = $_POST['fam'] ?? '';
                $label = trim($_POST['new_cat_title'] ?? '');
                if ($label === '' || !isset($menuFamilies[$fam])) {
                    $error = 'Enter a category name.';
                } else {
                    $catType = $menuFamilies[$fam]['catType'];
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,subtitle,sort,active) VALUES (?,?,?,?,1)');
                    $q->execute([$catType, $label, trim($_POST['new_cat_note'] ?? ''), (int) ($_POST['new_cat_sort'] ?? 99)]);
                    $newId = (int) db()->lastInsertId();
                    header('Location: melbournecup.php?tab=' . urlencode($fam) . '&cat=' . $newId . '&saved=1'); exit;
                }
            }
            if ($action === 'delete_category') {
                $fam = $_POST['fam'] ?? '';
                if (isset($menuFamilies[$fam])) {
                    $catType = $menuFamilies[$fam]['catType'];
                    $itemPrefix = $menuFamilies[$fam]['itemPrefix'];
                    $catId = (int) ($_POST['cat_id'] ?? 0);
                    db()->prepare('DELETE FROM blocks WHERE id=? AND block_type=?')->execute([$catId, $catType]);
                    db()->prepare('DELETE FROM blocks WHERE block_type=?')->execute([$itemPrefix . $catId]);
                }
                header('Location: melbournecup.php?tab=' . urlencode($fam) . '&saved=1'); exit;
            }

            // ---- Menu items (per category) ----
            if ($action === 'save_menu_items') {
                $fam = $_POST['fam'] ?? '';
                $catId = (int) ($_POST['cat'] ?? 0);
                if (isset($menuFamilies[$fam])) {
                    $blockType = $menuFamilies[$fam]['itemPrefix'] . $catId;
                    foreach (($_POST['title'] ?? []) as $id => $title) {
                        $id = (int) $id;
                        $q = db()->prepare('UPDATE blocks SET title=?, body=?, sort=? WHERE id=? AND block_type=?');
                        $q->execute([trim($title), trim($_POST['desc'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0), $id, $blockType]);
                    }
                }
                header('Location: melbournecup.php?tab=' . urlencode($fam) . '&cat=' . $catId . '&saved=1'); exit;
            }
            if ($action === 'add_menu_item') {
                $fam = $_POST['fam'] ?? '';
                $catId = (int) ($_POST['cat'] ?? 0);
                if (isset($menuFamilies[$fam])) {
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,body,sort,active) VALUES (?,?,?,?,1)');
                    $q->execute([$menuFamilies[$fam]['itemPrefix'] . $catId, trim($_POST['new_title'] ?? 'New Dish'), trim($_POST['new_desc'] ?? ''), (int) ($_POST['new_sort'] ?? 99)]);
                }
                header('Location: melbournecup.php?tab=' . urlencode($fam) . '&cat=' . $catId . '&saved=1'); exit;
            }
            if ($action === 'delete_menu_item') {
                $fam = $_POST['fam'] ?? '';
                $catId = (int) ($_POST['cat'] ?? 0);
                if (isset($menuFamilies[$fam])) {
                    $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type=?');
                    $q->execute([(int) ($_POST['id'] ?? 0), $menuFamilies[$fam]['itemPrefix'] . $catId]);
                }
                header('Location: melbournecup.php?tab=' . urlencode($fam) . '&cat=' . $catId . '&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='melbournecup_page'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}

$menuData = [];
foreach ($menuFamilies as $famKey => $fam) {
    $cats = db()->query("SELECT * FROM blocks WHERE block_type='" . $fam['catType'] . "' ORDER BY sort ASC, id ASC")->fetchAll();
    $itemsByCat = [];
    foreach ($cats as $cat) {
        $catId = (int) $cat['id'];
        $itemsByCat[$catId] = db()->query("SELECT * FROM blocks WHERE block_type='" . $fam['itemPrefix'] . $catId . "' ORDER BY sort ASC, id ASC")->fetchAll();
    }
    $menuData[$famKey] = ['cats' => $cats, 'itemsByCat' => $itemsByCat];
}

$tabs = [
    'herovideo'   => 'Hero Video',
    'intro'       => 'Top Intro',
    'restaurant'  => 'At The Restaurant',
    'restmenu'    => 'Three-Course Menu',
    'restbev'     => 'Beverage Packages',
    'below'       => 'At Below',
    'belowmenu'   => 'Canapé Menu',
    'belowdrinks' => 'The Drinks',
    'visibility'  => 'Section Visibility',
];
$activeTab = $_GET['tab'] ?? 'herovideo';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'herovideo';
}
$activeCat = isset($_GET['cat']) ? (int) $_GET['cat'] : null;

function render_mcup_field(array $f): void
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

/**
 * Renders a menu-category CRUD block (categories table + add form + per-category
 * item CRUD) for one of the two independent menu families (Restaurant / Below).
 */
function render_menu_family(string $famKey, array $fam, array $data, ?int $activeCat, string $csrf): void
{
    $cats = $data['cats'];
    $itemsByCat = $data['itemsByCat'];
    $activeCat = $activeCat ?? ($cats[0]['id'] ?? 0);
    ?>
    <h3><?= e($fam['label']) ?> - Categories</h3>
    <p class="group-help">These are the course headings shown on the live page (e.g. "For The Table", "Entrée"). The optional note shows as a small italic line under the heading (e.g. "Served as an alternate drop."). Deleting a category also deletes its dishes.</p>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_categories">
      <input type="hidden" name="fam" value="<?= e($famKey) ?>">
      <table>
        <tr><th>Category Name</th><th>Note (optional)</th><th style="width:100px;">Order</th><th style="width:90px;"></th></tr>
        <?php foreach ($cats as $cat): ?>
          <tr>
            <td><input type="text" name="cat_title[<?= $cat['id'] ?>]" value="<?= e($cat['title']) ?>"></td>
            <td><input type="text" name="cat_note[<?= $cat['id'] ?>]" value="<?= e($cat['subtitle']) ?>"></td>
            <td><input type="text" name="cat_sort[<?= $cat['id'] ?>]" value="<?= (int)$cat['sort'] ?>"></td>
            <td><button form="delcat<?= e($famKey) ?><?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category and ALL its dishes? This cannot be undone.')">Delete</button></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <div class="form-actions"><button class="btn">Save Categories</button></div>
    </form>
    <?php foreach ($cats as $cat): ?>
      <form id="delcat<?= e($famKey) ?><?= $cat['id'] ?>" method="post" class="inline-form">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="delete_category">
        <input type="hidden" name="fam" value="<?= e($famKey) ?>">
        <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
      </form>
    <?php endforeach; ?>

    <h4 style="margin-top:20px;font-size:14px;">Add a Category</h4>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="add_category">
      <input type="hidden" name="fam" value="<?= e($famKey) ?>">
      <div class="row-actions">
        <input type="text" name="new_cat_title" placeholder="e.g. Dessert" style="max-width:200px;">
        <input type="text" name="new_cat_note" placeholder="Note (optional)" style="max-width:220px;">
        <input type="text" name="new_cat_sort" placeholder="Order" value="99" style="width:70px;">
        <button class="btn btn-sm">+ Add Category</button>
      </div>
    </form>

    <h3 style="margin-top:30px;"><?= e($fam['label']) ?> - Dishes</h3>
    <?php if (!$cats): ?>
      <p class="group-help">Add a category above first - its dishes will appear here once it exists.</p>
    <?php else: ?>
      <div class="tab-nav" style="margin-bottom:18px;">
        <?php foreach ($cats as $cat): ?>
          <button type="button" class="tab-btn menu-cat-btn menu-cat-btn-<?= e($famKey) ?><?= $activeCat === (int)$cat['id'] ? ' active' : '' ?>" data-fam="<?= e($famKey) ?>" data-menu-cat="<?= $cat['id'] ?>"><?= e($cat['title']) ?></button>
        <?php endforeach; ?>
      </div>

      <?php foreach ($cats as $cat): $catId = (int) $cat['id']; ?>
        <div class="menu-cat-panel menu-cat-panel-<?= e($famKey) ?>" data-fam="<?= e($famKey) ?>" data-menu-cat-panel="<?= $catId ?>" style="<?= $activeCat === $catId ? '' : 'display:none;' ?>">
          <form method="post">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save_menu_items">
            <input type="hidden" name="fam" value="<?= e($famKey) ?>">
            <input type="hidden" name="cat" value="<?= $catId ?>">
            <table>
              <tr><th>Dish</th><th>Description</th><th style="width:90px;">Order</th><th style="width:90px;"></th></tr>
              <?php foreach ($itemsByCat[$catId] as $item): ?>
                <tr>
                  <td><input type="text" name="title[<?= $item['id'] ?>]" value="<?= e($item['title']) ?>"></td>
                  <td><textarea name="desc[<?= $item['id'] ?>]" rows="2" style="min-height:0;"><?= e($item['body']) ?></textarea></td>
                  <td><input type="text" name="sort[<?= $item['id'] ?>]" value="<?= (int)$item['sort'] ?>"></td>
                  <td><button form="delmenu<?= e($famKey) ?><?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this dish?')">Delete</button></td>
                </tr>
              <?php endforeach; ?>
            </table>
            <div class="form-actions"><button class="btn">Save <?= e($cat['title']) ?> Dishes</button></div>
          </form>
          <?php foreach ($itemsByCat[$catId] as $item): ?>
            <form id="delmenu<?= e($famKey) ?><?= $item['id'] ?>" method="post" class="inline-form">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="action" value="delete_menu_item">
              <input type="hidden" name="fam" value="<?= e($famKey) ?>">
              <input type="hidden" name="cat" value="<?= $catId ?>">
              <input type="hidden" name="id" value="<?= $item['id'] ?>">
            </form>
          <?php endforeach; ?>

          <h3 style="margin-top:24px;font-size:14px;">Add a Dish to <?= e($cat['title']) ?></h3>
          <form method="post">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="add_menu_item">
            <input type="hidden" name="fam" value="<?= e($famKey) ?>">
            <input type="hidden" name="cat" value="<?= $catId ?>">
            <div class="row-actions">
              <input type="text" name="new_title" placeholder="Dish name" style="max-width:220px;">
              <input type="text" name="new_desc" placeholder="Description" style="max-width:300px;">
              <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
              <button class="btn btn-sm">+ Add Dish</button>
            </div>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    <?php
}

$csrf = csrf_token();
$page_title = 'Melbourne Cup';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<div class="tab-nav">
  <?php foreach ($tabs as $key => $label): ?>
    <button type="button" class="tab-btn<?= $activeTab === $key ? ' active' : '' ?>" data-tab="<?= e($key) ?>"><?= e($label) ?></button>
  <?php endforeach; ?>
</div>

<?php foreach ($fieldGroups as $group => $keys): ?>
  <div class="tab-panel settings-group<?= $activeTab === $group ? ' active' : '' ?>" data-tab="<?= e($group) ?>">
    <h3><?= e($tabs[$group]) ?></h3>
    <?php if ($group === 'restaurant'): ?><p class="group-help">The heading block, the two expander button labels, and the "Book The Restaurant" button shown after the menu section (label + link). Expander content itself is edited on the "Three-Course Menu" and "Beverage Packages" tabs.</p><?php endif; ?>
    <?php if ($group === 'below'): ?><p class="group-help">The heading block, the two expander button labels, and the "Book Below" button shown after the menu section (label + link). Expander content itself is edited on the "Canapé Menu" and "The Drinks" tabs.</p><?php endif; ?>
    <?php if ($group === 'restbev'): ?><p class="group-help">Shown live on the page inside the "View Beverage Packages" expander. Standard / Premium items: one drink per line. The dietary note (bottom field) appears in "View Three-Course Menu" instead, not here.</p><?php endif; ?>
    <?php if ($group === 'belowdrinks'): ?><p class="group-help">Shown live on the page inside the "View Drinks Package" expander. Each field is the item list for its drink type (e.g. the White Wine field lists the white wines) - one item per line.</p><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="group" value="<?= e($group) ?>">
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_mcup_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<!-- Three-Course Menu - menu items tab -->
<div class="tab-panel settings-group<?= $activeTab === 'restmenu' ? ' active' : '' ?>" data-tab="restmenu">
  <p class="group-help">Shown live on the page inside the "View Three-Course Menu" expander. A category's "Note" field (e.g. "Alternate Drop" on Main) is appended to its heading after an em dash. The final item is followed by the dietary note from the Beverage Packages tab.</p>
  <?php render_menu_family('restmenu', $menuFamilies['restmenu'], $menuData['restmenu'], $activeCat, $csrf); ?>
</div>

<!-- Canapé Menu - menu items tab -->
<div class="tab-panel settings-group<?= $activeTab === 'belowmenu' ? ' active' : '' ?>" data-tab="belowmenu">
  <p class="group-help">Shown live on the page inside the "View Canapé Menu" expander.</p>
  <?php render_menu_family('belowmenu', $menuFamilies['belowmenu'], $menuData['belowmenu'], $activeCat, $csrf); ?>
</div>

<!-- Section Visibility tab -->
<div class="tab-panel settings-group<?= $activeTab === 'visibility' ? ' active' : '' ?>" data-tab="visibility">
  <h3>Section Visibility</h3>
  <p class="group-help">Untick a section to hide it from the live Melbourne Cup page without deleting its content - tick it again any time to bring it back.</p>
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
    var fam = btn.dataset.fam;
    document.querySelectorAll('.menu-cat-btn[data-fam="' + fam + '"]').forEach(function (b) { b.classList.remove('active'); });
    document.querySelectorAll('.menu-cat-panel[data-fam="' + fam + '"]').forEach(function (p) { p.style.display = 'none'; });
    btn.classList.add('active');
    var panel = document.querySelector('.menu-cat-panel[data-fam="' + fam + '"][data-menu-cat-panel="' + btn.dataset.menuCat + '"]');
    if (panel) panel.style.display = 'block';
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, '', '?tab=' + fam + '&cat=' + btn.dataset.menuCat);
    }
  });
});
</script>

<?php include __DIR__ . '/layout_end.php'; ?>
