<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$settingTabs = [
    'header'  => 'Header & Nav',
    'hero'    => 'Hero',
    'intro'   => 'Intro',
    'door'    => 'The Door',
    'mood'    => 'Every Mood',
    'whatson' => "What's On",
    'events'  => 'Private Events',
];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            // ---- Generic text/image settings tabs (header, hero, intro, door, mood, whatson, events) ----
            if ($action === 'save_section') {
                $sec = $_POST['section'] ?? '';
                if (isset($settingTabs[$sec])) {
                    $stmt = db()->prepare('SELECT * FROM settings WHERE section = ?');
                    $stmt->execute([$sec]);
                    foreach ($stmt->fetchAll() as $f) {
                        $key  = $f['skey'];
                        $type = $f['field_type'];
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
                header('Location: homepage.php?tab=' . urlencode($sec) . '&saved=1'); exit;
            }

            // ---- Three Spaces ----
            if ($action === 'save_spaces') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id   = (int) $id;
                    $link = $_POST['link_url'][$id] ?? '#';
                    $sort = (int) ($_POST['sort'][$id] ?? 0);
                    $img  = handle_upload('file_' . $id);
                    if ($img !== null) {
                        $q = db()->prepare('UPDATE blocks SET title=?, link_url=?, sort=?, image=? WHERE id=? AND block_type="space"');
                        $q->execute([$title, $link, $sort, $img, $id]);
                    } else {
                        $q = db()->prepare('UPDATE blocks SET title=?, link_url=?, sort=? WHERE id=? AND block_type="space"');
                        $q->execute([$title, $link, $sort, $id]);
                    }
                }
                header('Location: homepage.php?tab=spaces&saved=1'); exit;
            }

            // ---- Footer content ----
            if ($action === 'save_footer_settings') {
                foreach ($_POST['setting'] ?? [] as $k => $v) {
                    save_setting($k, $v);
                }
                header('Location: homepage.php?tab=footer&saved=1'); exit;
            }
            if ($action === 'save_footer_nav') {
                foreach (($_POST['nav_title'] ?? []) as $id => $title) {
                    $id = (int)$id;
                    $q = db()->prepare('UPDATE blocks SET title=?, link_url=?, sort=? WHERE id=? AND block_type="footer_nav"');
                    $q->execute([$title, $_POST['nav_url'][$id] ?? '#', (int)($_POST['nav_sort'][$id] ?? 0), $id]);
                }
                header('Location: homepage.php?tab=footer&saved=1'); exit;
            }
            if ($action === 'add_footer_nav') {
                $q = db()->prepare('INSERT INTO blocks (block_type,title,link_url,sort,active) VALUES ("footer_nav",?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New Link'), trim($_POST['new_url'] ?? '#'), (int)($_POST['new_sort'] ?? 99)]);
                header('Location: homepage.php?tab=footer&saved=1'); exit;
            }
            if ($action === 'delete_footer_nav') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="footer_nav"');
                $q->execute([(int)($_POST['id'] ?? 0)]);
                header('Location: homepage.php?tab=footer&saved=1'); exit;
            }

            // ---- Brand logos ----
            if ($action === 'save_brands') {
                foreach (($_POST['b_title'] ?? []) as $id => $title) {
                    $id  = (int)$id;
                    $img = handle_upload('file_' . $id);
                    if ($img !== null) {
                        $q = db()->prepare('UPDATE blocks SET title=?, sort=?, image=? WHERE id=? AND block_type="brand"');
                        $q->execute([$title, (int)($_POST['b_sort'][$id] ?? 0), $img, $id]);
                    } else {
                        $q = db()->prepare('UPDATE blocks SET title=?, sort=? WHERE id=? AND block_type="brand"');
                        $q->execute([$title, (int)($_POST['b_sort'][$id] ?? 0), $id]);
                    }
                }
                header('Location: homepage.php?tab=brands&saved=1'); exit;
            }
            if ($action === 'clear_brand_img') {
                $q = db()->prepare('UPDATE blocks SET image="" WHERE id=? AND block_type="brand"');
                $q->execute([(int)($_POST['id'] ?? 0)]);
                header('Location: homepage.php?tab=brands&saved=1'); exit;
            }
            if ($action === 'add_brand') {
                $q = db()->prepare('INSERT INTO blocks (block_type,title,image,sort,active) VALUES ("brand",?, "", ?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'Brand'), (int)($_POST['new_sort'] ?? 99)]);
                header('Location: homepage.php?tab=brands&saved=1'); exit;
            }
            if ($action === 'delete_brand') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="brand"');
                $q->execute([(int)($_POST['id'] ?? 0)]);
                header('Location: homepage.php?tab=brands&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

// ---- Load data for every tab up front ----
$allRows = db()->query(
    "SELECT * FROM settings WHERE section IN ('header','hero','intro','door','mood','whatson','events') ORDER BY sort ASC, id ASC"
)->fetchAll();
$fieldsBySection = [];
foreach ($allRows as $r) {
    $fieldsBySection[$r['section']][] = $r;
}
$footerFields = db()->query("SELECT * FROM settings WHERE section='footer' ORDER BY sort ASC, id ASC")->fetchAll();
$spaceCards   = db()->query("SELECT * FROM blocks WHERE block_type='space' ORDER BY sort ASC, id ASC")->fetchAll();
$footerNav    = db()->query("SELECT * FROM blocks WHERE block_type='footer_nav' ORDER BY sort ASC, id ASC")->fetchAll();
$brands       = db()->query("SELECT * FROM blocks WHERE block_type='brand' ORDER BY sort ASC, id ASC")->fetchAll();

$tabs = [
    'header'  => 'Header & Nav',
    'hero'    => 'Hero',
    'intro'   => 'Intro',
    'spaces'  => 'Three Spaces',
    'door'    => 'The Door',
    'mood'    => 'Every Mood',
    'whatson' => "What's On",
    'events'  => 'Private Events',
    'footer'  => 'Footer & Nav',
    'brands'  => 'Brand Logos',
];
$activeTab = $_GET['tab'] ?? 'header';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'header';
}

function render_setting_field(array $f): void
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
            <input type="hidden" name="keep_<?= e($key) ?>" value="1">
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
$page_title = 'Home Page';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<div class="tab-nav">
  <?php foreach ($tabs as $key => $label): ?>
    <button type="button" class="tab-btn<?= $activeTab === $key ? ' active' : '' ?>" data-tab="<?= e($key) ?>"><?= e($label) ?></button>
  <?php endforeach; ?>
</div>

<?php foreach ($settingTabs as $key => $label): ?>
  <div class="tab-panel settings-group<?= $activeTab === $key ? ' active' : '' ?>" data-tab="<?= e($key) ?>">
    <h3><?= e($label) ?></h3>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="section" value="<?= e($key) ?>">
      <?php foreach ($fieldsBySection[$key] ?? [] as $f): render_setting_field($f); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<div class="tab-panel settings-group<?= $activeTab === 'spaces' ? ' active' : '' ?>" data-tab="spaces">
  <h3>Three Spaces</h3>
  <p class="group-help">The three cards shown under “Three Spaces. One House.” Update the label, image, link and display order.</p>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_spaces">
    <table>
      <tr><th style="width:110px;">Image</th><th>Label</th><th>Link URL</th><th style="width:70px;">Order</th><th style="width:180px;">Replace image</th></tr>
      <?php foreach ($spaceCards as $c): ?>
        <tr>
          <td><?php if ($c['image']): ?><img src="<?= e(asset($c['image'])) ?>" alt=""><?php endif; ?></td>
          <td><input type="text" name="title[<?= $c['id'] ?>]" value="<?= e($c['title']) ?>"></td>
          <td><input type="text" name="link_url[<?= $c['id'] ?>]" value="<?= e($c['link_url']) ?>"></td>
          <td><input type="text" name="sort[<?= $c['id'] ?>]" value="<?= (int)$c['sort'] ?>" style="width:56px;"></td>
          <td><input type="file" name="file_<?= $c['id'] ?>" accept="image/*"></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>
</div>

<div class="tab-panel settings-group<?= $activeTab === 'footer' ? ' active' : '' ?>" data-tab="footer">
  <h3>Footer Content</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_footer_settings">
    <?php foreach ($footerFields as $f): ?>
      <div class="field-row">
        <label><?= e($f['label'] ?: $f['skey']) ?>
          <?php if ($f['field_type'] === 'textarea'): ?>
            <textarea name="setting[<?= e($f['skey']) ?>]"><?= e($f['svalue']) ?></textarea>
          <?php else: ?>
            <input type="text" name="setting[<?= e($f['skey']) ?>]" value="<?= e($f['svalue']) ?>">
          <?php endif; ?>
        </label>
      </div>
    <?php endforeach; ?>
    <div class="form-actions"><button class="btn">Save Footer Content</button></div>
  </form>

  <h3 style="margin-top:30px;">Footer Navigation Links</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_footer_nav">
    <table>
      <tr><th>Label</th><th>Link URL</th><th style="width:70px;">Order</th><th style="width:80px;"></th></tr>
      <?php foreach ($footerNav as $n): ?>
        <tr>
          <td><input type="text" name="nav_title[<?= $n['id'] ?>]" value="<?= e($n['title']) ?>"></td>
          <td><input type="text" name="nav_url[<?= $n['id'] ?>]" value="<?= e($n['link_url']) ?>"></td>
          <td><input type="text" name="nav_sort[<?= $n['id'] ?>]" value="<?= (int)$n['sort'] ?>" style="width:56px;"></td>
          <td><button form="delfnav<?= $n['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this link?')">Delete</button></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button class="btn">Save Links</button></div>
  </form>
  <?php foreach ($footerNav as $n): ?>
    <form id="delfnav<?= $n['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="delete_footer_nav">
      <input type="hidden" name="id" value="<?= $n['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h3 style="margin-top:30px;">Add a Link</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_footer_nav">
    <div class="row-actions">
      <input type="text" name="new_title" placeholder="Label" style="max-width:240px;">
      <input type="text" name="new_url" placeholder="# or https://…" style="max-width:240px;">
      <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
      <button class="btn btn-sm">Add</button>
    </div>
  </form>
</div>

<div class="tab-panel settings-group<?= $activeTab === 'brands' ? ' active' : '' ?>" data-tab="brands">
  <h3>Brand Logos</h3>
  <p class="group-help">The partner/brand marks in the footer. Upload a logo image, or leave it empty to show the name as text.</p>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_brands">
    <table>
      <tr><th style="width:130px;">Logo</th><th>Name</th><th style="width:70px;">Order</th><th style="width:190px;">Upload logo</th><th style="width:140px;"></th></tr>
      <?php foreach ($brands as $b): ?>
        <tr>
          <td><?php if ($b['image']): ?><img src="<?= e(asset($b['image'])) ?>" alt=""><?php else: ?><span class="muted">text</span><?php endif; ?></td>
          <td><input type="text" name="b_title[<?= $b['id'] ?>]" value="<?= e($b['title']) ?>"></td>
          <td><input type="text" name="b_sort[<?= $b['id'] ?>]" value="<?= (int)$b['sort'] ?>" style="width:56px;"></td>
          <td><input type="file" name="file_<?= $b['id'] ?>" accept="image/*"></td>
          <td class="row-actions">
            <?php if ($b['image']): ?><button form="clrb<?= $b['id'] ?>" class="btn btn-sm btn-ghost">Clear</button><?php endif; ?>
            <button form="delb<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this brand?')">Delete</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($brands as $b): ?>
    <form id="clrb<?= $b['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="clear_brand_img"><input type="hidden" name="id" value="<?= $b['id'] ?>">
    </form>
    <form id="delb<?= $b['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_brand"><input type="hidden" name="id" value="<?= $b['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h3 style="margin-top:30px;">Add a Brand</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_brand">
    <div class="row-actions">
      <input type="text" name="new_title" placeholder="Brand name" style="max-width:240px;">
      <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
      <button class="btn btn-sm">Add</button>
    </div>
  </form>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });
    btn.classList.add('active');
    var panel = document.querySelector('.tab-panel[data-tab="' + btn.dataset.tab + '"]');
    if (panel) panel.classList.add('active');
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, '', '?tab=' + btn.dataset.tab);
    }
  });
});
</script>

<?php include __DIR__ . '/layout_end.php'; ?>
