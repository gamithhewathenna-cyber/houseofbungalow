<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'herovideo' => ['pe_hero_video'],
    'intro'     => ['pe_heading', 'pe_subheading', 'pe_lede', 'pe_p1', 'pe_p2', 'pe_p3', 'pe_btn_label', 'pe_btn_url'],
    'final'     => ['pe_final_heading', 'pe_final_p1', 'pe_final_btn_label', 'pe_final_btn_url'],
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
                header('Location: privateevents.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            // ---- Event cards ----
            if ($action === 'save_cards') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    $fields = ['title=?', 'subtitle=?', 'body=?', 'link_url=?', 'sort=?'];
                    $params = [trim($title), trim($_POST['subtitle'][$id] ?? ''), trim($_POST['desc'][$id] ?? ''), trim($_POST['link'][$id] ?? '#'), (int) ($_POST['sort'][$id] ?? 0)];
                    if ($img !== null) {
                        $fields[] = 'image=?';
                        $params[] = $img;
                    }
                    $params[] = $id;
                    $q = db()->prepare('UPDATE blocks SET ' . implode(',', $fields) . ' WHERE id=? AND block_type="pe_event_card"');
                    $q->execute($params);
                }
                header('Location: privateevents.php?tab=cards&saved=1'); exit;
            }
            if ($action === 'add_card') {
                $img = handle_upload('new_file');
                $q = db()->prepare('INSERT INTO blocks (block_type,title,subtitle,body,link_url,image,sort,active) VALUES ("pe_event_card",?,?,?,?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New Event Type'), trim($_POST['new_subtitle'] ?? ''), trim($_POST['new_desc'] ?? ''), trim($_POST['new_link'] ?? '#'), $img ?? '', (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: privateevents.php?tab=cards&saved=1'); exit;
            }
            if ($action === 'delete_card') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="pe_event_card"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: privateevents.php?tab=cards&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='privateevents_page'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}
$cards = db()->query("SELECT * FROM blocks WHERE block_type='pe_event_card' ORDER BY sort ASC, id ASC")->fetchAll();

$tabs = [
    'herovideo' => 'Hero Video',
    'intro'     => 'Intro',
    'cards'     => 'Event Cards',
    'final'     => 'Make The House Yours',
];
$activeTab = $_GET['tab'] ?? 'herovideo';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'herovideo';
}

function render_pe_field(array $f): void
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
          <span class="help">MP4 or WEBM, ideally <strong>1280×720 (720p)</strong>, short and compressed (max 60&nbsp;MB) so it loads quickly. Leave empty to keep the current video.</span>
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
        <?php endif; ?>
      </label>
    </div>
    <?php
}

$csrf = csrf_token();
$page_title = 'Private Events';
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
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="group" value="<?= e($group) ?>">
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_pe_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<!-- Event Cards tab -->
<div class="tab-panel settings-group<?= $activeTab === 'cards' ? ' active' : '' ?>" data-tab="cards">
  <h3>Event Cards</h3>
  <p class="group-help">The two cards below the intro (e.g. "Restaurant Events" / "Below Events"). Add more if the House offers another kind of private event space.</p>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_cards">
    <div class="dj-admin-list">
      <?php foreach ($cards as $c): ?>
        <div class="dj-admin-card">
          <div class="dj-admin-media">
            <div class="thumb-preview<?= $c['image'] ? '' : ' empty' ?>">
              <?php if ($c['image']): ?><img src="<?= e(asset($c['image'])) ?>" alt=""><?php else: ?>No photo yet<?php endif; ?>
            </div>
            <input type="file" name="file_<?= $c['id'] ?>" accept="image/*">
            <span class="help">Replace photo</span>
          </div>
          <div class="dj-admin-fields">
            <div class="field-row">
              <label>Heading
                <input type="text" name="title[<?= $c['id'] ?>]" value="<?= e($c['title']) ?>">
              </label>
            </div>
            <div class="field-row">
              <label>Tagline (short line)
                <input type="text" name="subtitle[<?= $c['id'] ?>]" value="<?= e($c['subtitle']) ?>">
              </label>
            </div>
            <div class="field-row">
              <label>Description
                <textarea name="desc[<?= $c['id'] ?>]"><?= e($c['body']) ?></textarea>
              </label>
            </div>
            <div class="field-grid">
              <label>ENQUIRE link URL
                <input type="text" name="link[<?= $c['id'] ?>]" value="<?= e($c['link_url']) ?>">
              </label>
              <label>Order
                <input type="text" name="sort[<?= $c['id'] ?>]" value="<?= (int)$c['sort'] ?>">
              </label>
            </div>
          </div>
          <div class="dj-admin-actions">
            <button form="delcard<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this card?')">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($cards as $c): ?>
    <form id="delcard<?= $c['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_card"><input type="hidden" name="id" value="<?= $c['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h3 style="margin-top:30px;">Add a Card</h3>
  <form method="post" enctype="multipart/form-data" class="dj-admin-card dj-admin-add">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_card">
    <div class="dj-admin-media">
      <div class="thumb-preview empty">No photo yet</div>
      <input type="file" name="new_file" accept="image/*">
      <span class="help">Upload photo</span>
    </div>
    <div class="dj-admin-fields">
      <div class="field-row">
        <label>Heading
          <input type="text" name="new_title" placeholder="e.g. Cafe Events">
        </label>
      </div>
      <div class="field-row">
        <label>Tagline (short line)
          <input type="text" name="new_subtitle" placeholder="e.g. Daytime gatherings, done differently.">
        </label>
      </div>
      <div class="field-row">
        <label>Description
          <textarea name="new_desc" placeholder="Longer description of this event space..."></textarea>
        </label>
      </div>
      <div class="field-grid">
        <label>ENQUIRE link URL
          <input type="text" name="new_link" placeholder="#">
        </label>
        <label>Order
          <input type="text" name="new_sort" value="99">
        </label>
      </div>
    </div>
    <div class="dj-admin-actions">
      <button class="btn btn-sm">Add Card</button>
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
