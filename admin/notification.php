<?php
require_once __DIR__ . '/auth.php';
require_role('content');
require_once __DIR__ . '/helpers.php';

$keys = ['ticker_enabled', 'ticker_bg_color', 'ticker_text_color', 'ticker_speed'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'save_section') {
                $placeholders = implode(',', array_fill(0, count($keys), '?'));
                $stmt = db()->prepare("SELECT * FROM settings WHERE skey IN ($placeholders)");
                $stmt->execute($keys);
                foreach ($stmt->fetchAll() as $f) {
                    $key = $f['skey'];
                    if ($f['field_type'] === 'checkbox') {
                        save_setting($key, isset($_POST[$key]) ? '1' : '0');
                    } elseif (array_key_exists($key, $_POST)) {
                        save_setting($key, $_POST[$key]);
                    }
                }
                header('Location: notification.php?tab=settings&saved=1'); exit;
            }

            if ($action === 'save_messages') {
                foreach (($_POST['message'] ?? []) as $id => $message) {
                    $id = (int) $id;
                    $q = db()->prepare('UPDATE blocks SET title=?, link_url=?, sort=? WHERE id=? AND block_type="notification_ticker"');
                    $q->execute([trim($message), trim($_POST['link'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0), $id]);
                }
                header('Location: notification.php?tab=messages&saved=1'); exit;
            }
            if ($action === 'add_message') {
                $q = db()->prepare('INSERT INTO blocks (block_type,title,link_url,sort,active) VALUES ("notification_ticker",?,?,?,1)');
                $q->execute([trim($_POST['new_message'] ?? ''), trim($_POST['new_link'] ?? ''), (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: notification.php?tab=messages&saved=1'); exit;
            }
            if ($action === 'delete_message') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="notification_ticker"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: notification.php?tab=messages&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$stmt = db()->prepare('SELECT * FROM settings WHERE skey IN (' . implode(',', array_fill(0, count($keys), '?')) . ')');
$stmt->execute($keys);
$bySkey = [];
foreach ($stmt->fetchAll() as $r) {
    $bySkey[$r['skey']] = $r;
}
$messages = db()->query("SELECT * FROM blocks WHERE block_type='notification_ticker' ORDER BY sort ASC, id ASC")->fetchAll();

function render_ticker_field(array $f): void
{
    $key = $f['skey']; $val = $f['svalue']; $type = $f['field_type'];
    if ($type === 'checkbox') {
        ?>
        <div class="toggle-row">
          <div class="toggle-copy"><strong><?= e($f['label']) ?></strong></div>
          <label class="toggle">
            <input type="checkbox" name="<?= e($key) ?>" value="1" <?= $val === '1' ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
          </label>
        </div>
        <?php
        return;
    }
    if ($type === 'color') {
        $val = $val ?: '#000000';
        ?>
        <label class="color-field">
          <input type="color" name="<?= e($key) ?>" value="<?= e($val) ?>" oninput="this.parentElement.querySelector('.color-hex').textContent=this.value.toUpperCase()">
          <span>
            <span class="color-name"><?= e($f['label']) ?></span><br>
            <span class="color-hex"><?= e(strtoupper($val)) ?></span>
          </span>
        </label>
        <?php
        return;
    }
    ?>
    <div class="field-row">
      <label>
        <?= e($f['label'] ?: $key) ?>
        <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>" style="max-width:120px;">
      </label>
    </div>
    <?php
}

$tabs = [
    'settings' => 'Ticker Settings',
    'messages' => 'Messages',
];
$activeTab = $_GET['tab'] ?? 'settings';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'settings';
}

$csrf = csrf_token();
$page_title = 'Notification Ticker';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<div class="tab-nav">
  <?php foreach ($tabs as $key => $label): ?>
    <button type="button" class="tab-btn<?= $activeTab === $key ? ' active' : '' ?>" data-tab="<?= e($key) ?>"><?= e($label) ?></button>
  <?php endforeach; ?>
</div>

<div class="tab-panel settings-group<?= $activeTab === 'settings' ? ' active' : '' ?>" data-tab="settings">
  <h3>Ticker Settings</h3>
  <p class="group-help">A notification bar shown across the very top of every page, above the header. Turn it on or off, and set its colours here. Add or edit the actual messages under the "Messages" tab.</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_section">
    <?php if (isset($bySkey['ticker_enabled'])): render_ticker_field($bySkey['ticker_enabled']); endif; ?>
    <div class="settings-grid" style="margin-top:14px;">
      <?php foreach (['ticker_bg_color', 'ticker_text_color'] as $key): if (!isset($bySkey[$key])) continue; render_ticker_field($bySkey[$key]); endforeach; ?>
    </div>
    <?php if (isset($bySkey['ticker_speed'])): render_ticker_field($bySkey['ticker_speed']); ?>
      <p class="help">Only used when there's more than one message below - it rotates through them.</p>
    <?php endif; ?>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>
</div>

<div class="tab-panel settings-group<?= $activeTab === 'messages' ? ' active' : '' ?>" data-tab="messages">
  <h3>Messages</h3>
  <p class="group-help">Each message shown in the ticker. With more than one, they rotate automatically. Leave "Link URL" empty for plain text, or add a page (e.g. <code>dinnerparty.php</code>) or full web address to make the message clickable.</p>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_messages">
    <div class="dj-admin-list">
      <?php foreach ($messages as $m): ?>
        <div class="dj-admin-card no-media">
          <div class="dj-admin-fields">
            <div class="field-row">
              <label>Message
                <input type="text" name="message[<?= $m['id'] ?>]" value="<?= e($m['title']) ?>">
              </label>
            </div>
            <div class="field-grid">
              <label>Link URL (optional)
                <input type="text" name="link[<?= $m['id'] ?>]" value="<?= e($m['link_url']) ?>" placeholder="#">
              </label>
              <label>Order
                <input type="text" name="sort[<?= $m['id'] ?>]" value="<?= (int) $m['sort'] ?>" style="max-width:100px;">
              </label>
            </div>
          </div>
          <div class="dj-admin-actions">
            <button form="delmsg<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this message?')">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($messages as $m): ?>
    <form id="delmsg<?= $m['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_message"><input type="hidden" name="id" value="<?= $m['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h3 style="margin-top:30px;">Add a Message</h3>
  <form method="post" class="dj-admin-card dj-admin-add no-media">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_message">
    <div class="dj-admin-fields">
      <div class="field-row">
        <label>Message
          <input type="text" name="new_message" placeholder="e.g. Dinner, with a difference. Discover our signature House Dinner Parties, every Fri & Sat from 7.30pm">
        </label>
      </div>
      <div class="field-grid">
        <label>Link URL (optional)
          <input type="text" name="new_link" placeholder="#">
        </label>
        <label>Order
          <input type="text" name="new_sort" value="99" style="max-width:100px;">
        </label>
      </div>
    </div>
    <div class="dj-admin-actions">
      <button class="btn btn-sm">Add Message</button>
    </div>
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
