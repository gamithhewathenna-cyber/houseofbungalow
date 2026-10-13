<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'herovideo' => ['cafe_hero_video'],
    'intro'     => ['cafe_heading', 'cafe_subheading', 'cafe_lede', 'cafe_p1', 'cafe_p2'],
    'hours'     => ['cafe_hours_heading', 'cafe_hours_p1', 'cafe_hours_p2', 'cafe_hours', 'cafe_address', 'cafe_btn1_label', 'cafe_btn1_url', 'cafe_btn2_label', 'cafe_btn2_url'],
    'final'     => ['cafe_final_heading', 'cafe_final_p1', 'cafe_final_p2', 'cafe_final_btn1_label', 'cafe_final_btn1_url', 'cafe_final_btn2_label', 'cafe_final_btn2_url'],
];
$visibilitySections = [
    'cafe_hero'   => 'Hero',
    'cafe_intro'  => 'Intro',
    'cafe_gallery' => 'Gallery',
    'cafe_hours'  => 'Hours & Location',
    'cafe_final'  => 'More Of The House',
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
                header('Location: cafe.php?tab=visibility&saved=1'); exit;
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
                header('Location: cafe.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            // ---- Gallery ----
            if ($action === 'save_gallery') {
                foreach (($_POST['g_sort'] ?? []) as $id => $sort) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    if ($img !== null) {
                        $q = db()->prepare('UPDATE blocks SET sort=?, image=? WHERE id=? AND block_type="cafe_gallery"');
                        $q->execute([(int) $sort, $img, $id]);
                    } else {
                        $q = db()->prepare('UPDATE blocks SET sort=? WHERE id=? AND block_type="cafe_gallery"');
                        $q->execute([(int) $sort, $id]);
                    }
                }
                header('Location: cafe.php?tab=gallery&saved=1'); exit;
            }
            if ($action === 'add_gallery') {
                $img = handle_upload('new_file');
                if ($img === null) {
                    $error = 'Choose an image to add to the gallery.';
                } else {
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,image,sort,active) VALUES ("cafe_gallery","",?,?,1)');
                    $q->execute([$img, (int) ($_POST['new_sort'] ?? 99)]);
                    header('Location: cafe.php?tab=gallery&saved=1'); exit;
                }
            }
            if ($action === 'delete_gallery') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="cafe_gallery"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: cafe.php?tab=gallery&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='cafe_page'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}
$gallery = db()->query("SELECT * FROM blocks WHERE block_type='cafe_gallery' ORDER BY sort ASC, id ASC")->fetchAll();

$tabs = [
    'herovideo' => 'Hero Video',
    'intro'     => 'Intro',
    'gallery'   => 'Gallery',
    'hours'     => 'Hours & Location',
    'final'     => 'More Of The House',
    'visibility' => 'Section Visibility',
];
$activeTab = $_GET['tab'] ?? 'herovideo';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'herovideo';
}

function render_cafe_field(array $f): void
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
$page_title = 'Café';
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
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="group" value="<?= e($group) ?>">
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_cafe_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<!-- Gallery tab -->
<div class="tab-panel settings-group<?= $activeTab === 'gallery' ? ' active' : '' ?>" data-tab="gallery">
  <h3>Gallery</h3>
  <p class="group-help">The photo grid on the Café page. Add, reorder or remove as many photos as you like.</p>
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

<div class="tab-panel settings-group<?= $activeTab === 'visibility' ? ' active' : '' ?>" data-tab="visibility">
  <h3>Section Visibility</h3>
  <p class="group-help">Untick a section to hide it from the live Café page without deleting its content.</p>
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
