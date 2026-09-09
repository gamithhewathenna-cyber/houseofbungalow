<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'herovideo' => ['below_hero_video'],
    'intro'     => ['below_heading', 'below_subheading', 'below_lede', 'below_intro_p1', 'below_intro_p2', 'below_intro_btn1_label', 'below_intro_btn1_url', 'below_intro_btn2_label', 'below_intro_btn2_url'],
    'cocktails' => ['below_cocktails_heading', 'below_cocktails_p1', 'below_cocktails_p2', 'below_cocktails_p3', 'below_cocktails_hours', 'below_cocktails_image1', 'below_cocktails_image2', 'below_cocktails_image3'],
    'music'     => ['below_music_heading', 'below_music_lede', 'below_music_btn1_label', 'below_music_btn1_url', 'below_music_btn2_label', 'below_music_btn2_url'],
    'lineup'    => ['below_lineup_image', 'below_lineup_heading', 'below_lineup_p1'],
    'guest'     => ['below_guest_eyebrow', 'below_guest_heading', 'below_guest_p1', 'below_guest_p2', 'below_guest_btn_label', 'below_guest_btn_url', 'below_guest_image'],
    'vip'       => ['below_vip_eyebrow', 'below_vip_heading', 'below_vip_p1', 'below_vip_p2', 'below_vip_btn_label', 'below_vip_btn_url', 'below_vip_image'],
    'hours'     => ['below_hours_heading', 'below_hours_days', 'below_hours_cocktail_label', 'below_hours_cocktail', 'below_hours_dj_label', 'below_hours_dj', 'below_hours_note'],
];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            // ---- Generic settings groups ----
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
                header('Location: below.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            // ---- Nights (Music section) ----
            if ($action === 'save_nights') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id = (int) $id;
                    $q = db()->prepare('UPDATE blocks SET title=?, body=?, sort=? WHERE id=? AND block_type="below_night"');
                    $q->execute([trim($title), trim($_POST['desc'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0), $id]);
                }
                header('Location: below.php?tab=music&saved=1'); exit;
            }
            if ($action === 'add_night') {
                $q = db()->prepare('INSERT INTO blocks (block_type,title,body,sort,active) VALUES ("below_night",?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New Night'), trim($_POST['new_desc'] ?? ''), (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: below.php?tab=music&saved=1'); exit;
            }
            if ($action === 'delete_night') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="below_night"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: below.php?tab=music&saved=1'); exit;
            }

            // ---- DJs / Line-up cards ----
            if ($action === 'save_djs') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    $fields = ['title=?', 'subtitle=?', 'body=?', 'link_url=?', 'sort=?'];
                    $params = [trim($title), trim($_POST['subtitle'][$id] ?? ''), trim($_POST['desc'][$id] ?? ''), trim($_POST['link'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0)];
                    if ($img !== null) {
                        $fields[] = 'image=?';
                        $params[] = $img;
                    }
                    $params[] = $id;
                    $q = db()->prepare('UPDATE blocks SET ' . implode(',', $fields) . ' WHERE id=? AND block_type="below_dj"');
                    $q->execute($params);
                }
                header('Location: below.php?tab=lineup&saved=1'); exit;
            }
            if ($action === 'add_dj') {
                $img = handle_upload('new_file');
                $q = db()->prepare('INSERT INTO blocks (block_type,title,subtitle,body,link_url,image,sort,active) VALUES ("below_dj",?,?,?,?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New DJ'), trim($_POST['new_subtitle'] ?? ''), trim($_POST['new_desc'] ?? ''), trim($_POST['new_link'] ?? '#'), $img ?? '', (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: below.php?tab=lineup&saved=1'); exit;
            }
            if ($action === 'delete_dj') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="below_dj"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: below.php?tab=lineup&saved=1'); exit;
            }

            // ---- FAQs ----
            if ($action === 'save_faqs') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id = (int) $id;
                    $q = db()->prepare('UPDATE blocks SET title=?, body=?, sort=? WHERE id=? AND block_type="below_faq"');
                    $q->execute([trim($title), trim($_POST['desc'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0), $id]);
                }
                header('Location: below.php?tab=faqs&saved=1'); exit;
            }
            if ($action === 'add_faq') {
                $q = db()->prepare('INSERT INTO blocks (block_type,title,body,sort,active) VALUES ("below_faq",?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New Question'), trim($_POST['new_desc'] ?? ''), (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: below.php?tab=faqs&saved=1'); exit;
            }
            if ($action === 'delete_faq') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="below_faq"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: below.php?tab=faqs&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

// ---- Load data ----
$allRows = db()->query("SELECT * FROM settings WHERE section='below'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}
$nights = db()->query("SELECT * FROM blocks WHERE block_type='below_night' ORDER BY sort ASC, id ASC")->fetchAll();
$djs    = db()->query("SELECT * FROM blocks WHERE block_type='below_dj' ORDER BY sort ASC, id ASC")->fetchAll();
$faqs   = db()->query("SELECT * FROM blocks WHERE block_type='below_faq' ORDER BY sort ASC, id ASC")->fetchAll();

$tabs = [
    'herovideo' => 'Hero Video',
    'intro'     => 'Intro',
    'cocktails' => 'From Cocktails To Late Night',
    'music'     => 'Music',
    'lineup'    => "This Week's Line-Up",
    'guest'     => 'Guest Artists',
    'vip'       => 'VIP Tables',
    'hours'     => 'Below Hours',
    'faqs'      => 'FAQs',
];
$activeTab = $_GET['tab'] ?? 'herovideo';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'herovideo';
}

function render_below_field(array $f): void
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
$page_title = 'Below';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<div class="tab-nav">
  <?php foreach ($tabs as $key => $label): ?>
    <button type="button" class="tab-btn<?= $activeTab === $key ? ' active' : '' ?>" data-tab="<?= e($key) ?>"><?= e($label) ?></button>
  <?php endforeach; ?>
</div>

<?php foreach ($fieldGroups as $group => $keys): if ($group === 'music') continue; ?>
  <div class="tab-panel settings-group<?= $activeTab === $group ? ' active' : '' ?>" data-tab="<?= e($group) ?>">
    <h3><?= e($tabs[$group]) ?></h3>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="group" value="<?= e($group) ?>">
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_below_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>

    <?php if ($group === 'lineup'): ?>
      <h3 style="margin-top:30px;">DJ / Line-up Cards</h3>
      <p class="group-help">Shown as cards over the line-up background image.</p>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="save_djs">
        <table>
          <tr><th style="width:90px;">Image</th><th>Name</th><th style="width:110px;">Day</th><th style="width:140px;">Time</th><th>Link</th><th style="width:60px;">Order</th><th style="width:160px;">Replace image</th><th style="width:80px;"></th></tr>
          <?php foreach ($djs as $dj): ?>
            <tr>
              <td><?php if ($dj['image']): ?><img src="<?= e(asset($dj['image'])) ?>" alt=""><?php endif; ?></td>
              <td><input type="text" name="title[<?= $dj['id'] ?>]" value="<?= e($dj['title']) ?>"></td>
              <td><input type="text" name="subtitle[<?= $dj['id'] ?>]" value="<?= e($dj['subtitle']) ?>"></td>
              <td><input type="text" name="desc[<?= $dj['id'] ?>]" value="<?= e($dj['body']) ?>"></td>
              <td><input type="text" name="link[<?= $dj['id'] ?>]" value="<?= e($dj['link_url']) ?>"></td>
              <td><input type="text" name="sort[<?= $dj['id'] ?>]" value="<?= (int)$dj['sort'] ?>" style="width:56px;"></td>
              <td><input type="file" name="file_<?= $dj['id'] ?>" accept="image/*"></td>
              <td><button form="deldj<?= $dj['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this card?')">Delete</button></td>
            </tr>
          <?php endforeach; ?>
        </table>
        <div class="form-actions"><button class="btn">Save Changes</button></div>
      </form>
      <?php foreach ($djs as $dj): ?>
        <form id="deldj<?= $dj['id'] ?>" method="post" class="inline-form">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_dj"><input type="hidden" name="id" value="<?= $dj['id'] ?>">
        </form>
      <?php endforeach; ?>
      <h3 style="margin-top:24px;font-size:14px;">Add a Card</h3>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="add_dj">
        <div class="row-actions">
          <input type="file" name="new_file" accept="image/*">
          <input type="text" name="new_title" placeholder="Name" style="max-width:160px;">
          <input type="text" name="new_subtitle" placeholder="Day" style="width:90px;">
          <input type="text" name="new_desc" placeholder="Time" style="width:120px;">
          <input type="text" name="new_link" placeholder="Link URL" style="max-width:140px;">
          <input type="text" name="new_sort" placeholder="Order" value="99" style="width:60px;">
          <button class="btn btn-sm">Add</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<!-- Music tab: settings + nights CRUD -->
<div class="tab-panel settings-group<?= $activeTab === 'music' ? ' active' : '' ?>" data-tab="music">
  <h3>Music</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_section">
    <input type="hidden" name="group" value="music">
    <?php foreach ($fieldGroups['music'] as $key): if (!isset($bySkey[$key])) continue; render_below_field($bySkey[$key]); endforeach; ?>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>

  <h3 style="margin-top:30px;">Nights</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_nights">
    <table>
      <tr><th>Night / Genre</th><th>Description</th><th style="width:70px;">Order</th><th style="width:80px;"></th></tr>
      <?php foreach ($nights as $n): ?>
        <tr>
          <td><input type="text" name="title[<?= $n['id'] ?>]" value="<?= e($n['title']) ?>"></td>
          <td><input type="text" name="desc[<?= $n['id'] ?>]" value="<?= e($n['body']) ?>"></td>
          <td><input type="text" name="sort[<?= $n['id'] ?>]" value="<?= (int)$n['sort'] ?>" style="width:56px;"></td>
          <td><button form="delnight<?= $n['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this night?')">Delete</button></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($nights as $n): ?>
    <form id="delnight<?= $n['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_night"><input type="hidden" name="id" value="<?= $n['id'] ?>">
    </form>
  <?php endforeach; ?>
  <h3 style="margin-top:24px;font-size:14px;">Add a Night</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_night">
    <div class="row-actions">
      <input type="text" name="new_title" placeholder="e.g. Monday - Jazz" style="max-width:220px;">
      <input type="text" name="new_desc" placeholder="Description" style="max-width:280px;">
      <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
      <button class="btn btn-sm">Add</button>
    </div>
  </form>
</div>

<!-- FAQs tab -->
<div class="tab-panel settings-group<?= $activeTab === 'faqs' ? ' active' : '' ?>" data-tab="faqs">
  <h3>FAQs</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_faqs">
    <table>
      <tr><th>Question</th><th>Answer</th><th style="width:70px;">Order</th><th style="width:80px;"></th></tr>
      <?php foreach ($faqs as $f): ?>
        <tr>
          <td><input type="text" name="title[<?= $f['id'] ?>]" value="<?= e($f['title']) ?>"></td>
          <td><input type="text" name="desc[<?= $f['id'] ?>]" value="<?= e($f['body']) ?>"></td>
          <td><input type="text" name="sort[<?= $f['id'] ?>]" value="<?= (int)$f['sort'] ?>" style="width:56px;"></td>
          <td><button form="delfaq<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this question?')">Delete</button></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($faqs as $f): ?>
    <form id="delfaq<?= $f['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_faq"><input type="hidden" name="id" value="<?= $f['id'] ?>">
    </form>
  <?php endforeach; ?>
  <h3 style="margin-top:24px;font-size:14px;">Add a Question</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_faq">
    <div class="row-actions">
      <input type="text" name="new_title" placeholder="Question" style="max-width:260px;">
      <input type="text" name="new_desc" placeholder="Answer" style="max-width:320px;">
      <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
      <button class="btn btn-sm">Add</button>
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
