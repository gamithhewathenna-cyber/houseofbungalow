<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'herovideo' => ['faq_hero_video'],
    'intro'     => ['faq_hero_image', 'faq_heading', 'faq_subheading'],
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
                header('Location: faq.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            // ---- FAQ items ----
            if ($action === 'save_faqs') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id = (int) $id;
                    $enabled = isset($_POST['link_enabled'][$id]) ? '1' : '';
                    $q = db()->prepare('UPDATE blocks SET title=?, body=?, subtitle=?, link_url=?, link_url2=?, sort=? WHERE id=? AND block_type="faq_page_item"');
                    $q->execute([
                        trim($title),
                        trim($_POST['body'][$id] ?? ''),
                        $enabled,
                        trim($_POST['link_url'][$id] ?? ''),
                        trim($_POST['link_label'][$id] ?? ''),
                        (int) ($_POST['sort'][$id] ?? 0),
                        $id,
                    ]);
                }
                header('Location: faq.php?tab=faqs&saved=1'); exit;
            }
            if ($action === 'add_faq') {
                $enabled = !empty($_POST['new_link_enabled']) ? '1' : '';
                $q = db()->prepare('INSERT INTO blocks (block_type,title,body,subtitle,link_url,link_url2,sort,active) VALUES ("faq_page_item",?,?,?,?,?,?,1)');
                $q->execute([
                    trim($_POST['new_title'] ?? 'New Question?'),
                    trim($_POST['new_body'] ?? ''),
                    $enabled,
                    trim($_POST['new_link_url'] ?? ''),
                    trim($_POST['new_link_label'] ?? ''),
                    (int) ($_POST['new_sort'] ?? 99),
                ]);
                header('Location: faq.php?tab=faqs&saved=1'); exit;
            }
            if ($action === 'delete_faq') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="faq_page_item"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: faq.php?tab=faqs&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='faq_page'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}
$faqs = db()->query("SELECT * FROM blocks WHERE block_type='faq_page_item' ORDER BY sort ASC, id ASC")->fetchAll();

$tabs = [
    'herovideo' => 'Hero Video',
    'intro'     => 'Heading',
    'faqs'      => 'Questions & Answers',
];
$activeTab = $_GET['tab'] ?? 'herovideo';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'herovideo';
}

function render_faq_field(array $f): void
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
$page_title = 'FAQ';
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
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_faq_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<!-- Questions & Answers tab -->
<div class="tab-panel settings-group<?= $activeTab === 'faqs' ? ' active' : '' ?>" data-tab="faqs">
  <h3>Questions &amp; Answers</h3>
  <p class="group-help">Shown as an accordion on the FAQ page — click a question on the live site to expand its answer. Tick "Show a button" on any question to add a link-styled button at the end of its answer (e.g. linking to the Restaurant or Below page).</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_faqs">
    <div class="faq-admin-list">
      <?php foreach ($faqs as $faq): ?>
        <div class="faq-admin-card">
          <button type="button" class="faq-admin-toggle">
            <span class="faq-admin-toggle-title"><?= e($faq['title'] ?: 'Untitled question') ?></span>
            <span class="chevron">▾</span>
          </button>
          <div class="faq-admin-body">
            <div class="dj-admin-fields">
              <div class="field-row">
                <label>Question
                  <input type="text" name="title[<?= $faq['id'] ?>]" value="<?= e($faq['title']) ?>">
                </label>
              </div>
              <div class="field-row">
                <label>Answer
                  <textarea name="body[<?= $faq['id'] ?>]"><?= e($faq['body']) ?></textarea>
                </label>
              </div>
              <label class="checkbox-row">
                <input type="checkbox" name="link_enabled[<?= $faq['id'] ?>]" value="1"<?= $faq['subtitle'] === '1' ? ' checked' : '' ?>>
                Show a button at the end of this answer
              </label>
              <div class="field-grid">
                <label>Button label
                  <input type="text" name="link_label[<?= $faq['id'] ?>]" value="<?= e($faq['link_url2']) ?>" placeholder="e.g. VIEW MENU">
                </label>
                <label>Button URL
                  <input type="text" name="link_url[<?= $faq['id'] ?>]" value="<?= e($faq['link_url']) ?>" placeholder="e.g. restaurant.php">
                </label>
              </div>
              <div class="field-row">
                <label>Order
                  <input type="text" name="sort[<?= $faq['id'] ?>]" value="<?= (int)$faq['sort'] ?>" style="max-width:100px;">
                </label>
              </div>
            </div>
            <div class="dj-admin-actions">
              <button form="delfaq<?= $faq['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this question?')">Delete</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($faqs as $faq): ?>
    <form id="delfaq<?= $faq['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="delete_faq">
      <input type="hidden" name="id" value="<?= $faq['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h4 style="margin-top:20px;font-size:14px;">Add a Question</h4>
  <form method="post" class="dj-admin-card no-media dj-admin-add">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_faq">
    <div class="dj-admin-fields">
      <div class="field-row">
        <label>Question
          <input type="text" name="new_title" placeholder="Question">
        </label>
      </div>
      <div class="field-row">
        <label>Answer
          <textarea name="new_body" placeholder="Answer"></textarea>
        </label>
      </div>
      <label class="checkbox-row">
        <input type="checkbox" name="new_link_enabled" value="1">
        Show a button at the end of this answer
      </label>
      <div class="field-grid">
        <label>Button label
          <input type="text" name="new_link_label" placeholder="e.g. VIEW MENU">
        </label>
        <label>Button URL
          <input type="text" name="new_link_url" placeholder="e.g. restaurant.php">
        </label>
      </div>
      <div class="field-row">
        <label>Order
          <input type="text" name="new_sort" value="99" style="max-width:100px;">
        </label>
      </div>
    </div>
    <div class="dj-admin-actions">
      <button class="btn btn-sm">Add Question</button>
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
document.querySelectorAll('.faq-admin-toggle').forEach(function (btn) {
  btn.addEventListener('click', function () {
    btn.closest('.faq-admin-card').classList.toggle('open');
  });
});
</script>

<?php include __DIR__ . '/layout_end.php'; ?>
