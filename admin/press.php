<?php
require_once __DIR__ . '/auth.php';
require_role('content');
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'herovideo' => ['press_hero_image', 'press_hero_video'],
    'intro'     => ['press_heading', 'press_subheading', 'press_lede', 'press_p1', 'press_p2', 'press_p3', 'press_btn_label'],
    'popup'     => ['press_form_eyebrow', 'press_form_intro', 'press_success_message'],
];
$visibilitySections = [
    'press_hero'     => 'Hero',
    'press_intro'    => 'Intro',
    'press_features' => 'Featured In',
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
                header('Location: press.php?tab=visibility&saved=1'); exit;
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
                header('Location: press.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            // ---- Featured In grid ----
            if ($action === 'save_features') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    $fields = ['title=?', 'link_url=?', 'sort=?'];
                    $params = [trim($title), trim($_POST['link'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0)];
                    if ($img !== null) {
                        $fields[] = 'image=?';
                        $params[] = $img;
                    }
                    $params[] = $id;
                    $q = db()->prepare('UPDATE blocks SET ' . implode(',', $fields) . ' WHERE id=? AND block_type="press_feature"');
                    $q->execute($params);
                }
                header('Location: press.php?tab=features&saved=1'); exit;
            }
            if ($action === 'add_feature') {
                $img = handle_upload('new_file');
                $q = db()->prepare('INSERT INTO blocks (block_type,title,link_url,image,sort,active) VALUES ("press_feature",?,?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New Feature'), trim($_POST['new_link'] ?? '#'), $img ?? '', (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: press.php?tab=features&saved=1'); exit;
            }
            if ($action === 'delete_feature') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="press_feature"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: press.php?tab=features&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='press_page'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}
$pressFeatures = db()->query("SELECT * FROM blocks WHERE block_type='press_feature' ORDER BY sort ASC, id ASC")->fetchAll();

$tabs = [
    'herovideo'  => 'Hero Image/Video',
    'intro'      => 'Intro',
    'features'   => 'Featured In',
    'popup'      => 'Media Enquiry Popup',
    'visibility' => 'Section Visibility',
];
$activeTab = $_GET['tab'] ?? 'herovideo';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'herovideo';
}

function render_press_field(array $f): void
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
$page_title = 'Press & Media';
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
    <?php if ($group === 'herovideo'): ?>
      <p class="group-help">Shown full-bleed at the very top of the page, no text over it (just like Restaurant, Café, Below, etc). Upload a video to use that instead of the static image - it'll autoplay muted on loop.</p>
    <?php endif; ?>
    <?php if ($group === 'intro'): ?>
      <p class="group-help">Shown just below the hero image. The button opens the Media Enquiry popup form - leave its label empty to hide the button.</p>
    <?php endif; ?>
    <?php if ($group === 'popup'): ?>
      <p class="group-help">The popup's form fields themselves (Full Name, Publication/Organisation, Email, Phone, Nature of Enquiry, Your Enquiry) are fixed and not editable here. Where enquiries get emailed to is set under Press Enquiries in the sidebar.</p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="group" value="<?= e($group) ?>">
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_press_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<!-- Featured In tab -->
<div class="tab-panel settings-group<?= $activeTab === 'features' ? ' active' : '' ?>" data-tab="features">
  <h3>Featured In</h3>
  <p class="group-help">Shown as a grid after the intro section - 4 across, wrapping to a new row after every 4. Each image and title link out to the article, opening in a new tab.</p>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_features">
    <div class="dj-admin-list">
      <?php foreach ($pressFeatures as $pf): ?>
        <div class="dj-admin-card">
          <div class="dj-admin-media">
            <div class="thumb-preview<?= $pf['image'] ? '' : ' empty' ?>">
              <?php if ($pf['image']): ?><img src="<?= e(asset($pf['image'])) ?>" alt=""><?php else: ?>No photo yet<?php endif; ?>
            </div>
            <input type="file" name="file_<?= $pf['id'] ?>" accept="image/*" data-crop-ratio="1:1">
            <span class="help">Replace photo</span>
          </div>
          <div class="dj-admin-fields">
            <div class="field-row">
              <label>Title
                <input type="text" name="title[<?= $pf['id'] ?>]" value="<?= e($pf['title']) ?>">
              </label>
            </div>
            <div class="field-grid">
              <label>Link URL <span class="help">(opens in a new tab)</span>
                <input type="text" name="link[<?= $pf['id'] ?>]" value="<?= e($pf['link_url']) ?>" placeholder="https://...">
              </label>
              <label>Order
                <input type="text" name="sort[<?= $pf['id'] ?>]" value="<?= (int)$pf['sort'] ?>" style="max-width:100px;">
              </label>
            </div>
          </div>
          <div class="dj-admin-actions">
            <button form="delfeature<?= $pf['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this feature?')">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($pressFeatures as $pf): ?>
    <form id="delfeature<?= $pf['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_feature"><input type="hidden" name="id" value="<?= $pf['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h3 style="margin-top:30px;">Add a Feature</h3>
  <form method="post" enctype="multipart/form-data" class="dj-admin-card dj-admin-add">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_feature">
    <div class="dj-admin-media">
      <div class="thumb-preview empty">No photo yet</div>
      <input type="file" name="new_file" accept="image/*" data-crop-ratio="1:1">
      <span class="help">Upload photo</span>
    </div>
    <div class="dj-admin-fields">
      <div class="field-row">
        <label>Title
          <input type="text" name="new_title" placeholder="e.g. The Leftovers Miami">
        </label>
      </div>
      <div class="field-grid">
        <label>Link URL <span class="help">(opens in a new tab)</span>
          <input type="text" name="new_link" placeholder="https://...">
        </label>
        <label>Order
          <input type="text" name="new_sort" value="99" style="max-width:100px;">
        </label>
      </div>
    </div>
    <div class="dj-admin-actions">
      <button class="btn btn-sm">Add Feature</button>
    </div>
  </form>
</div>

<div class="tab-panel settings-group<?= $activeTab === 'visibility' ? ' active' : '' ?>" data-tab="visibility">
  <h3>Section Visibility</h3>
  <p class="group-help">Untick a section to hide it from the live Press &amp; Media page without deleting its content.</p>
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
