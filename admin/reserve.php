<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'hero'      => ['reserve_hero_heading', 'reserve_hero_subheading', 'reserve_hero_p1', 'reserve_hero_p2', 'reserve_hero_btn_label', 'reserve_hero_btn_url'],
    'heading'   => ['reserve_heading'],
    'fridaysat' => ['reserve_fridaysat_eyebrow', 'reserve_fridaysat_heading', 'reserve_fridaysat_p1', 'reserve_fridaysat_p2', 'reserve_fridaysat_btn_label', 'reserve_fridaysat_btn_url', 'reserve_fridaysat_image'],
    'happyhour' => ['reserve_happyhour_eyebrow', 'reserve_happyhour_heading', 'reserve_happyhour_p1', 'reserve_happyhour_hours1', 'reserve_happyhour_hours2', 'reserve_happyhour_btn_label', 'reserve_happyhour_btn_url', 'reserve_happyhour_image'],
    'vip'       => ['reserve_vip_eyebrow', 'reserve_vip_heading', 'reserve_vip_p1', 'reserve_vip_p2', 'reserve_vip_btn_label', 'reserve_vip_btn_url', 'reserve_vip_image'],
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
                header('Location: reserve.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            // ---- Hero slides ----
            if ($action === 'save_slides') {
                foreach (($_POST['s_sort'] ?? []) as $id => $sort) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    if ($img !== null) {
                        $q = db()->prepare('UPDATE blocks SET sort=?, image=? WHERE id=? AND block_type="reserve_hero_slide"');
                        $q->execute([(int) $sort, $img, $id]);
                    } else {
                        $q = db()->prepare('UPDATE blocks SET sort=? WHERE id=? AND block_type="reserve_hero_slide"');
                        $q->execute([(int) $sort, $id]);
                    }
                }
                header('Location: reserve.php?tab=slides&saved=1'); exit;
            }
            if ($action === 'add_slide') {
                $img = handle_upload('new_file');
                if ($img === null) {
                    $error = 'Choose an image to add to the slider.';
                } else {
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,image,sort,active) VALUES ("reserve_hero_slide","",?,?,1)');
                    $q->execute([$img, (int) ($_POST['new_sort'] ?? 99)]);
                    header('Location: reserve.php?tab=slides&saved=1'); exit;
                }
            }
            if ($action === 'delete_slide') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="reserve_hero_slide"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: reserve.php?tab=slides&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='reserve_page'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}
$slides = db()->query("SELECT * FROM blocks WHERE block_type='reserve_hero_slide' ORDER BY sort ASC, id ASC")->fetchAll();

$tabs = [
    'slides'    => 'Hero Slides',
    'hero'      => 'Hero Card',
    'heading'   => 'Reservations Heading',
    'fridaysat' => 'Friday & Saturday',
    'happyhour' => 'Happy Hour',
    'vip'       => 'VIP Tables',
];
$activeTab = $_GET['tab'] ?? 'hero';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'hero';
}

function render_reserve_field(array $f): void
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
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
        <?php endif; ?>
      </label>
    </div>
    <?php
}

$csrf = csrf_token();
$page_title = 'Reserve';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<div class="tab-nav">
  <?php foreach ($tabs as $key => $label): ?>
    <button type="button" class="tab-btn<?= $activeTab === $key ? ' active' : '' ?>" data-tab="<?= e($key) ?>"><?= e($label) ?></button>
  <?php endforeach; ?>
</div>

<!-- Hero Slides tab -->
<div class="tab-panel settings-group<?= $activeTab === 'slides' ? ' active' : '' ?>" data-tab="slides">
  <h3>Hero Slides</h3>
  <p class="group-help">Photos shown in the hero image slider at the top of the page, with the text card overlaid on top. Add as many as you like — visitors can click the arrows to move between them (the arrows are hidden automatically if there's only one photo).</p>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_slides">
    <table>
      <tr><th style="width:110px;">Image</th><th style="width:90px;">Order</th><th style="width:220px;">Replace image</th><th style="width:90px;"></th></tr>
      <?php foreach ($slides as $s): ?>
        <tr>
          <td><?php if ($s['image']): ?><img src="<?= e(asset($s['image'])) ?>" alt=""><?php endif; ?></td>
          <td><input type="text" name="s_sort[<?= $s['id'] ?>]" value="<?= (int)$s['sort'] ?>"></td>
          <td><input type="file" name="file_<?= $s['id'] ?>" accept="image/*"></td>
          <td><button form="delslide<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this slide?')">Delete</button></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($slides as $s): ?>
    <form id="delslide<?= $s['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="delete_slide">
      <input type="hidden" name="id" value="<?= $s['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h4 style="margin-top:20px;font-size:14px;">Add a Slide</h4>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_slide">
    <div class="row-actions">
      <input type="file" name="new_file" accept="image/*">
      <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
      <button class="btn btn-sm">+ Add Slide</button>
    </div>
  </form>
</div>

<?php foreach ($fieldGroups as $group => $keys): ?>
  <div class="tab-panel settings-group<?= $activeTab === $group ? ' active' : '' ?>" data-tab="<?= e($group) ?>">
    <h3><?= e($tabs[$group]) ?></h3>
    <?php if ($group === 'hero'): ?>
      <p class="group-help">The text card centered over the hero slider, including the "VIEW HAPPY HOUR" button, which by default scrolls to the Happy Hour section on this page (set its URL to <code>#happyhour</code>, or any other link).</p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="group" value="<?= e($group) ?>">
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_reserve_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

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
