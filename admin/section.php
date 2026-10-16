<?php
require_once __DIR__ . '/auth.php';
require_role('admin');
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../includes/mailer.php';

$sections = [
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
$ok    = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } elseif (($_POST['action'] ?? 'save') === 'test_smtp') {
        $testTo = trim(setting('smtp_username'));
        if ($testTo === '') {
            $error = 'Enter and save a Workspace email address below first.';
        } else {
            $sent = app_mail(
                $testTo,
                'Test email from ' . SITE_NAME,
                "This is a test email sent from your website's admin panel (Website Settings > Email) to confirm your email settings are working.\n\nIf you received this, everything is set up correctly.",
                ['Content-Type: text/plain; charset=UTF-8']
            );
            if ($sent) {
                $ok = 'Test email sent to ' . $testTo . ' - check that inbox (and its spam folder).';
            } else {
                $error = 'Could not send a test email. Double-check the host, port, username and password below, then save and try again.';
            }
        }
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

$website_help = [
    'logo_white'       => 'Shown in the header, over the hero image.',
    'logo_colour'      => 'Shown in the footer, near the bottom of the page.',
    'seo_visible'      => "When off, a noindex tag is added so Google and other search engines won't list this site.",
    'maintenance_mode' => "When on, visitors see a “down for maintenance” page. You can still browse the live site while logged in here.",
    'smtp_enabled'     => 'When off, the site sends email using the web server directly (less reliable - often lands in spam).',
];
$website_groups = [
    'Logo Settings'            => ['logo_white', 'logo_colour'],
    'Colour Theme'             => ['theme_cream', 'theme_ink', 'theme_ink_soft', 'theme_ink_mute', 'theme_maroon', 'theme_dark'],
    'Search Engine Visibility' => ['seo_visible'],
    'Email (SMTP)'             => ['smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password', 'smtp_from_name', 'smtp_from_email'],
    'Maintenance Mode'         => ['maintenance_mode'],
];
$byKey = [];
foreach ($fields as $f) {
    $byKey[$f['skey']] = $f;
}
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<?php if ($ok): ?><div class="alert ok"><?= e($ok) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="save">

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
              <input type="file" name="file_<?= e($key) ?>" accept="image/*" data-crop-ratio="<?= e(crop_ratio_for_key($key)) ?>">
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

      <?php elseif ($groupTitle === 'Email (SMTP)'): ?>
        <p class="group-help">Send site emails (enquiries, applications, thank-you replies) through a real mailbox - e.g. a Google Workspace address - instead of the web server's own mail, for much better deliverability. See the field help below for where to find these details in Google Workspace.</p>
        <?php $smtpEnabledField = $byKey['smtp_enabled'] ?? null; ?>
        <?php if ($smtpEnabledField): ?>
          <div class="toggle-row">
            <div class="toggle-copy">
              <strong><?= e($smtpEnabledField['label']) ?></strong>
              <span><?= e($website_help['smtp_enabled'] ?? '') ?></span>
            </div>
            <label class="toggle">
              <input type="checkbox" name="smtp_enabled" value="1" <?= $smtpEnabledField['svalue'] === '1' ? 'checked' : '' ?>>
              <span class="toggle-slider"></span>
            </label>
          </div>
        <?php endif; ?>
        <div class="field-grid" style="margin-top:14px;">
          <?php foreach (['smtp_host' => 'smtp.gmail.com', 'smtp_username' => 'you@yourdomain.com'] as $key => $ph): if (!isset($byKey[$key])) continue; $f = $byKey[$key]; ?>
            <label><?= e($f['label']) ?>
              <input type="text" name="<?= e($key) ?>" value="<?= e($f['svalue']) ?>" placeholder="<?= e($ph) ?>">
            </label>
          <?php endforeach; ?>
        </div>
        <div class="field-grid">
          <?php if (isset($byKey['smtp_port'])): ?>
            <label><?= e($byKey['smtp_port']['label']) ?>
              <input type="text" name="smtp_port" value="<?= e($byKey['smtp_port']['svalue']) ?>" placeholder="587" style="max-width:120px;">
            </label>
          <?php endif; ?>
          <?php if (isset($byKey['smtp_encryption'])): ?>
            <label><?= e($byKey['smtp_encryption']['label']) ?>
              <select name="smtp_encryption">
                <option value="tls" <?= $byKey['smtp_encryption']['svalue'] === 'tls' ? 'selected' : '' ?>>STARTTLS (port 587 - typical for Google Workspace)</option>
                <option value="ssl" <?= $byKey['smtp_encryption']['svalue'] === 'ssl' ? 'selected' : '' ?>>SSL (port 465)</option>
              </select>
            </label>
          <?php endif; ?>
        </div>
        <?php if (isset($byKey['smtp_password'])): ?>
          <div class="field-row">
            <label><?= e($byKey['smtp_password']['label']) ?> <span class="help">(a Google <strong>App Password</strong> - not your normal login password; create one at myaccount.google.com &gt; Security &gt; 2-Step Verification &gt; App passwords)</span>
              <input type="password" name="smtp_password" value="<?= e($byKey['smtp_password']['svalue']) ?>" placeholder="<?= $byKey['smtp_password']['svalue'] ? 'Leave as-is to keep the saved password' : '' ?>">
            </label>
          </div>
        <?php endif; ?>
        <div class="field-grid">
          <?php foreach (['smtp_from_name' => SITE_NAME, 'smtp_from_email' => 'Same as the Workspace email above'] as $key => $ph): if (!isset($byKey[$key])) continue; $f = $byKey[$key]; ?>
            <label><?= e($f['label']) ?>
              <input type="text" name="<?= e($key) ?>" value="<?= e($f['svalue']) ?>" placeholder="<?= e($ph) ?>">
            </label>
          <?php endforeach; ?>
        </div>
        <p class="help" style="margin-top:6px;">Save your changes below first, then use this to confirm they work - it sends a test email to the Workspace email address above.</p>
        <button type="submit" form="testSmtpForm" class="btn btn-sm btn-ghost">Send Test Email</button>

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

  <div class="form-actions">
    <button type="submit" class="btn">Save Changes</button>
  </div>
</form>

<form id="testSmtpForm" method="post" class="inline-form">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="test_smtp">
</form>
<?php include __DIR__ . '/layout_end.php'; ?>
