<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$sections = [
    'header'  => 'Header & Nav',
    'hero'    => 'Hero',
    'intro'   => 'Intro',
    'door'    => 'The Door',
    'mood'    => 'Every Mood',
    'whatson' => "What's On",
    'events'  => 'Private Events',
    'website' => 'Website Settings',
];

$s = $_GET['s'] ?? '';
if (!isset($sections[$s])) {
    http_response_code(404);
    exit('Unknown section.');
}

// Load the fields for this section, in order.
$stmt = db()->prepare('SELECT * FROM settings WHERE section = ? ORDER BY sort ASC, id ASC');
$stmt->execute([$s]);
$fields = $stmt->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        try {
            foreach ($fields as $f) {
                $key  = $f['skey'];
                $type = $f['field_type'];
                if ($type === 'image') {
                    $uploaded = handle_upload('file_' . $key);
                    if ($uploaded !== null) {
                        save_setting($key, $uploaded);
                    } elseif (isset($_POST['keep_' . $key])) {
                        // keep existing – no change
                    }
                } elseif ($type === 'checkbox') {
                    save_setting($key, isset($_POST[$key]) ? '1' : '0');
                } else {
                    if (array_key_exists($key, $_POST)) {
                        save_setting($key, $_POST[$key]);
                    }
                }
            }
            header('Location: section.php?s=' . urlencode($s) . '&saved=1');
            exit;
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$page_title = $sections[$s];
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <?php foreach ($fields as $f):
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
        <?php elseif ($type === 'color'): ?>
          <input type="color" name="<?= e($key) ?>" value="<?= e($val ?: '#000000') ?>" style="width:70px;height:38px;padding:2px;">
        <?php elseif ($type === 'checkbox'): ?>
          <input type="checkbox" name="<?= e($key) ?>" value="1" <?= $val === '1' ? 'checked' : '' ?>>
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
        <?php endif; ?>
      </label>
    </div>
  <?php endforeach; ?>
  <div class="form-actions">
    <button type="submit" class="btn">Save Changes</button>
  </div>
</form>
<?php include __DIR__ . '/layout_end.php'; ?>
