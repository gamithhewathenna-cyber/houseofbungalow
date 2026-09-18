<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$keys = ['popup_heading', 'popup_subheading', 'popup_btn1_label', 'popup_btn1_url', 'popup_btn2_label', 'popup_btn2_url'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        foreach ($keys as $key) {
            if (array_key_exists($key, $_POST)) {
                save_setting($key, $_POST[$key]);
            }
        }
        header('Location: popup.php?saved=1'); exit;
    }
}

$stmt = db()->prepare('SELECT * FROM settings WHERE skey IN (' . implode(',', array_fill(0, count($keys), '?')) . ')');
$stmt->execute($keys);
$bySkey = [];
foreach ($stmt->fetchAll() as $r) {
    $bySkey[$r['skey']] = $r;
}

function render_popup_field(array $f): void
{
    $key = $f['skey']; $val = $f['svalue'];
    ?>
    <div class="field-row">
      <label>
        <?= e($f['label'] ?: $key) ?>
        <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
      </label>
    </div>
    <?php
}

$csrf = csrf_token();
$page_title = 'Popup Booking';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<div class="tab-panel settings-group active" data-tab="content">
  <h3>Popup Booking</h3>
  <p class="group-help">This full-screen popup only opens from the "BOOK A TABLE" button on the Home Page. Every "BOOK A TABLE" button on the other pages (Restaurant, Below, What's On, Dinner Party) is a normal link that goes straight to its own page's configured URL, edited from that page's own admin tab.</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_popup_field($bySkey[$key]); endforeach; ?>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>
</div>

<?php include __DIR__ . '/layout_end.php'; ?>
