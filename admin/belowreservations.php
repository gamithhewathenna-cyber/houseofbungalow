<?php
require_once __DIR__ . '/auth.php';
require_role('content');
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'hero'    => ['br_hero_image', 'br_heading', 'br_subheading', 'br_body'],
    'widget'  => ['below_opentable_embed'],
    'content' => ['br_section_heading', 'br_cross_heading', 'br_cross_btn_label', 'br_cross_btn_url'],
];
$visibilitySections = [
    'br_hero'   => 'Hero',
    'br_widget' => 'Reservations Widget',
    'br_cross'  => 'Looking for Restaurant Reservations? Section',
];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'save_visibility') {
                foreach ($visibilitySections as $vkey => $vlabel) {
                    save_setting($vkey . '_visible', isset($_POST['vis_' . $vkey]) ? '1' : '0');
                }
                header('Location: belowreservations.php?tab=visibility&saved=1'); exit;
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
                        } else {
                            if (array_key_exists($key, $_POST)) {
                                save_setting($key, $_POST[$key]);
                            }
                        }
                    }
                }
                header('Location: belowreservations.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='belowreservations_page' OR skey='below_opentable_embed'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}

$tabs = [
    'hero'       => 'Hero',
    'widget'     => 'Reservation Widget',
    'content'    => 'Content',
    'visibility' => 'Section Visibility',
];
$activeTab = $_GET['tab'] ?? 'hero';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'hero';
}

function render_br_field(array $f): void
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
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
        <?php endif; ?>
      </label>
    </div>
    <?php
}

$csrf = csrf_token();
$page_title = 'Below Reservations';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<p class="muted" style="margin-bottom:18px;">This page is not linked anywhere on the live site's menu - it's only reachable by its direct URL, for sharing separately (e.g. in an email, QR code or social link). The Reservation Widget tab below shares the same embed code as the Below page, so changing it in either place updates both.</p>

<div class="tab-nav">
  <?php foreach ($tabs as $key => $label): ?>
    <button type="button" class="tab-btn<?= $activeTab === $key ? ' active' : '' ?>" data-tab="<?= e($key) ?>"><?= e($label) ?></button>
  <?php endforeach; ?>
</div>

<div class="tab-panel settings-group<?= $activeTab === 'hero' ? ' active' : '' ?>" data-tab="hero">
  <h3>Hero</h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_section">
    <input type="hidden" name="group" value="hero">
    <?php foreach ($fieldGroups['hero'] as $key): if (!isset($bySkey[$key])) continue; render_br_field($bySkey[$key]); endforeach; ?>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>
</div>

<div class="tab-panel settings-group<?= $activeTab === 'widget' ? ' active' : '' ?>" data-tab="widget">
  <h3>Reservation Widget</h3>
  <p class="group-help">This is the same OpenTable embed code used on the Below page (admin &gt; Below &gt; Hero Video tab) - changing it here also updates it there, since they share the same widget.</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_section">
    <input type="hidden" name="group" value="widget">
    <?php foreach ($fieldGroups['widget'] as $key): if (!isset($bySkey[$key])) continue; render_br_field($bySkey[$key]); endforeach; ?>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>
</div>

<div class="tab-panel settings-group<?= $activeTab === 'content' ? ' active' : '' ?>" data-tab="content">
  <h3>Content</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_section">
    <input type="hidden" name="group" value="content">
    <?php foreach ($fieldGroups['content'] as $key): if (!isset($bySkey[$key])) continue; render_br_field($bySkey[$key]); endforeach; ?>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>
</div>

<div class="tab-panel settings-group<?= $activeTab === 'visibility' ? ' active' : '' ?>" data-tab="visibility">
  <h3>Section Visibility</h3>
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
