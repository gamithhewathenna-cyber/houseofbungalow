<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
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

            // ---- Hero slides (image + its own content) ----
            if ($action === 'save_slides') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    $fields = ['title=?', 'subtitle=?', 'body=?', 'link_url=?', 'link_url2=?', 'sort=?'];
                    $params = [
                        trim($title),
                        trim($_POST['subtitle'][$id] ?? ''),
                        trim($_POST['body'][$id] ?? ''),
                        trim($_POST['link'][$id] ?? '#'),
                        trim($_POST['link_label'][$id] ?? ''),
                        (int) ($_POST['sort'][$id] ?? 0),
                    ];
                    if ($img !== null) {
                        $fields[] = 'image=?';
                        $params[] = $img;
                    }
                    $params[] = $id;
                    $q = db()->prepare('UPDATE blocks SET ' . implode(',', $fields) . ' WHERE id=? AND block_type="reserve_hero_slide"');
                    $q->execute($params);
                }
                header('Location: reserve.php?tab=slides&saved=1'); exit;
            }
            if ($action === 'add_slide') {
                $img = handle_upload('new_file');
                if ($img === null) {
                    $error = 'Choose an image to add to the slider.';
                } else {
                    $q = db()->prepare('INSERT INTO blocks (block_type,title,subtitle,body,link_url,link_url2,image,sort,active) VALUES ("reserve_hero_slide",?,?,?,?,?,?,?,1)');
                    $q->execute([
                        trim($_POST['new_title'] ?? 'New Slide'),
                        trim($_POST['new_subtitle'] ?? ''),
                        trim($_POST['new_body'] ?? ''),
                        trim($_POST['new_link'] ?? '#'),
                        trim($_POST['new_link_label'] ?? ''),
                        $img,
                        (int) ($_POST['new_sort'] ?? 99),
                    ]);
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
    'heading'   => 'Reservations Heading',
    'fridaysat' => 'Friday & Saturday',
    'happyhour' => 'Happy Hour',
    'vip'       => 'VIP Tables',
];
$activeTab = $_GET['tab'] ?? 'slides';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'slides';
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

<!-- Hero Slides tab (image + its own heading/text/button) -->
<div class="tab-panel settings-group<?= $activeTab === 'slides' ? ' active' : '' ?>" data-tab="slides">
  <h3>Hero Slides</h3>
  <p class="group-help">Each slide is a photo with its own heading, subheading, paragraph(s) and button - the text card changes together with the photo as visitors click the arrows. Leave the button label empty to hide the button on that slide. For the paragraph, put each sentence on its own line to create separate paragraphs.</p>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_slides">
    <div class="dj-admin-list">
      <?php foreach ($slides as $s): ?>
        <div class="dj-admin-card">
          <div class="dj-admin-media">
            <div class="thumb-preview<?= $s['image'] ? '' : ' empty' ?>">
              <?php if ($s['image']): ?><img src="<?= e(asset($s['image'])) ?>" alt=""><?php else: ?>No photo yet<?php endif; ?>
            </div>
            <input type="file" name="file_<?= $s['id'] ?>" accept="image/*">
            <span class="help">Replace photo</span>
          </div>
          <div class="dj-admin-fields">
            <div class="field-row">
              <label>Heading
                <input type="text" name="title[<?= $s['id'] ?>]" value="<?= e($s['title']) ?>">
              </label>
            </div>
            <div class="field-row">
              <label>Subheading
                <input type="text" name="subtitle[<?= $s['id'] ?>]" value="<?= e($s['subtitle']) ?>">
              </label>
            </div>
            <div class="field-row">
              <label>Paragraph(s)
                <textarea name="body[<?= $s['id'] ?>]"><?= e($s['body']) ?></textarea>
              </label>
            </div>
            <div class="field-grid">
              <label>Button label
                <input type="text" name="link_label[<?= $s['id'] ?>]" value="<?= e($s['link_url2']) ?>" placeholder="e.g. VIEW HAPPY HOUR">
              </label>
              <label>Button URL
                <input type="text" name="link[<?= $s['id'] ?>]" value="<?= e($s['link_url']) ?>" placeholder="#happyhour">
              </label>
            </div>
            <div class="field-grid">
              <label>Order
                <input type="text" name="sort[<?= $s['id'] ?>]" value="<?= (int)$s['sort'] ?>">
              </label>
            </div>
          </div>
          <div class="dj-admin-actions">
            <button form="delslide<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this slide?')">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
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
  <form method="post" enctype="multipart/form-data" class="dj-admin-card dj-admin-add">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_slide">
    <div class="dj-admin-media">
      <div class="thumb-preview empty">No photo yet</div>
      <input type="file" name="new_file" accept="image/*">
      <span class="help">Upload photo</span>
    </div>
    <div class="dj-admin-fields">
      <div class="field-row">
        <label>Heading
          <input type="text" name="new_title" placeholder="e.g. Reserve Your Night At The House.">
        </label>
      </div>
      <div class="field-row">
        <label>Subheading
          <input type="text" name="new_subtitle" placeholder="e.g. Restaurant. Happy Hour. Below.">
        </label>
      </div>
      <div class="field-row">
        <label>Paragraph(s)
          <textarea name="new_body" placeholder="One sentence per line..."></textarea>
        </label>
      </div>
      <div class="field-grid">
        <label>Button label
          <input type="text" name="new_link_label" placeholder="e.g. VIEW HAPPY HOUR">
        </label>
        <label>Button URL
          <input type="text" name="new_link" placeholder="#happyhour">
        </label>
      </div>
      <div class="field-grid">
        <label>Order
          <input type="text" name="new_sort" value="99">
        </label>
      </div>
    </div>
    <div class="dj-admin-actions">
      <button class="btn btn-sm">Add Slide</button>
    </div>
  </form>
</div>

<?php foreach ($fieldGroups as $group => $keys): ?>
  <div class="tab-panel settings-group<?= $activeTab === $group ? ' active' : '' ?>" data-tab="<?= e($group) ?>">
    <h3><?= e($tabs[$group]) ?></h3>
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
