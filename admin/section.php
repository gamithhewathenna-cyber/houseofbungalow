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

  <?php if ($s === 'website'):
      $website_help = [
          'logo_white'       => 'Shown in the header, over the hero image.',
          'logo_colour'      => 'Shown in the footer, near the bottom of the page.',
          'seo_visible'      => "When off, a noindex tag is added so Google and other search engines won't list this site.",
          'maintenance_mode' => "When on, visitors see a “down for maintenance” page. You can still browse the live site while logged in here.",
      ];
      $website_groups = [
          'Logo Settings'           => ['logo_white', 'logo_colour'],
          'Colour Theme'            => ['theme_cream', 'theme_ink', 'theme_ink_soft', 'theme_ink_mute', 'theme_maroon', 'theme_dark'],
          'Search Engine Visibility'=> ['seo_visible'],
          'Maintenance Mode'        => ['maintenance_mode'],
      ];
      $byKey = [];
      foreach ($fields as $f) { $byKey[$f['skey']] = $f; }
  ?>
    <?php foreach ($website_groups as $groupTitle => $keys): ?>
      <div class="settings-group">
        <h3><?= e($groupTitle) ?></h3>

        <?php if ($groupTitle === 'Logo Settings'): ?>
          <div class="settings-grid">
            <?php foreach ($keys as $key): if (!isset($byKey[$key])) continue; $f = $byKey[$key]; $val = $f['svalue']; ?>
              <div class="logo-field">
                <div class="thumb-preview<?= $val ? '' : ' empty' ?>">
                  <?php if ($val): ?><img src="<?= e(asset($val)) ?>" alt=""><?php else: ?>No logo yet<?php endif; ?>
                </div>
                <label class="logo-field-label"><?= e($f['label']) ?></label>
                <span class="help"><?= e($website_help[$key] ?? '') ?></span>
                <?php if ($val): ?><input type="hidden" name="keep_<?= e($key) ?>" value="1"><?php endif; ?>
                <input type="file" name="file_<?= e($key) ?>" accept="image/*">
              </div>
            <?php endforeach; ?>
          </div>

        <?php elseif ($groupTitle === 'Colour Theme'): ?>
          <p class="group-help">These colours control the site's background, text and accent tones everywhere.</p>
          <div class="settings-grid">
            <?php foreach ($keys as $key): if (!isset($byKey[$key])) continue; $f = $byKey[$key]; $val = $f['svalue'] ?: '#000000'; ?>
              <label class="color-field">
                <input type="color" name="<?= e($key) ?>" value="<?= e($val) ?>" oninput="this.parentElement.querySelector('.color-hex').textContent=this.value.toUpperCase()">
                <span>
                  <span class="color-name"><?= e($f['label']) ?></span><br>
                  <span class="color-hex"><?= e(strtoupper($val)) ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>

        <?php else: ?>
          <?php foreach ($keys as $key): if (!isset($byKey[$key])) continue; $f = $byKey[$key]; $checked = $f['svalue'] === '1'; ?>
            <div class="toggle-row">
              <div class="toggle-copy">
                <strong><?= e($f['label']) ?></strong>
                <span><?= e($website_help[$key] ?? '') ?></span>
              </div>
              <label class="toggle">
                <input type="checkbox" name="<?= e($key) ?>" value="1" <?= $checked ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

  <?php else: ?>
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
  <?php endif; ?>

  <div class="form-actions">
    <button type="submit" class="btn">Save Changes</button>
  </div>
</form>
<?php include __DIR__ . '/layout_end.php'; ?>
