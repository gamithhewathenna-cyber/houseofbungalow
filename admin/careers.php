<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'intro'  => ['career_heading', 'career_lede', 'career_p1', 'career_p2', 'career_p3'],
    'form'   => ['career_form_eyebrow', 'career_form_intro', 'career_success_message'],
    'emails' => ['career_to_email', 'career_cc_email'],
];
$visibilitySections = [
    'career_intro' => 'Intro',
    'career_form'  => 'Application Form',
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
                header('Location: careers.php?tab=visibility&saved=1'); exit;
            }

            if ($action === 'save_section') {
                $group = $_POST['group'] ?? '';
                if (isset($fieldGroups[$group])) {
                    $keys = $fieldGroups[$group];
                    $placeholders = implode(',', array_fill(0, count($keys), '?'));
                    $stmt = db()->prepare("SELECT * FROM settings WHERE skey IN ($placeholders)");
                    $stmt->execute($keys);
                    foreach ($stmt->fetchAll() as $f) {
                        $key = $f['skey'];
                        if (array_key_exists($key, $_POST)) {
                            save_setting($key, $_POST[$key]);
                        }
                    }
                }
                header('Location: careers.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='career_page'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}

$tabs = [
    'intro'      => 'Intro',
    'form'       => 'Application Form',
    'emails'     => 'Application Emails',
    'visibility' => 'Section Visibility',
];
$activeTab = $_GET['tab'] ?? 'intro';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'intro';
}

function render_career_field(array $f): void
{
    $key = $f['skey']; $val = $f['svalue']; $type = $f['field_type'];
    ?>
    <div class="field-row">
      <label>
        <?= e($f['label'] ?: $key) ?>
        <?php if ($type === 'textarea'): ?>
          <textarea name="<?= e($key) ?>"><?= e($val) ?></textarea>
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
        <?php endif; ?>
      </label>
    </div>
    <?php
}

$csrf = csrf_token();
$page_title = 'Join Our Team';
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
    <?php if ($group === 'form'): ?>
      <p class="group-help">The application fields themselves (Full Name, Email, Area of Interest, CV upload, etc.) are fixed and not editable here - only this intro text and the thank-you message shown after someone applies.</p>
    <?php endif; ?>
    <?php if ($group === 'emails'): ?>
      <p class="group-help">Controls what happens when someone submits the Join Our Team application form. No email server setup needed - this sends straight from the website, with a link to download the applicant's CV. Leave "Send applications to" empty to only save applications here in the admin (under "Applications") without emailing anyone.</p>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="group" value="<?= e($group) ?>">
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_career_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<div class="tab-panel settings-group<?= $activeTab === 'visibility' ? ' active' : '' ?>" data-tab="visibility">
  <h3>Section Visibility</h3>
  <p class="group-help">Untick a section to hide it from the live Join Our Team page without deleting its content.</p>
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
