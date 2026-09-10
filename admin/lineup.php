<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$keys = ['below_lineup_image', 'below_lineup_heading', 'below_lineup_p1'];

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
                header('Location: lineup.php?saved=1'); exit;
            }

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
                header('Location: lineup.php?saved=1'); exit;
            }
            if ($action === 'add_dj') {
                $img = handle_upload('new_file');
                $q = db()->prepare('INSERT INTO blocks (block_type,title,subtitle,body,link_url,image,sort,active) VALUES ("below_dj",?,?,?,?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New DJ'), trim($_POST['new_subtitle'] ?? ''), trim($_POST['new_desc'] ?? ''), trim($_POST['new_link'] ?? '#'), $img ?? '', (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: lineup.php?saved=1'); exit;
            }
            if ($action === 'delete_dj') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="below_dj"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: lineup.php?saved=1'); exit;
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
$djs = db()->query("SELECT * FROM blocks WHERE block_type='below_dj' ORDER BY sort ASC, id ASC")->fetchAll();

function render_lineup_field(array $f): void
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
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
        <?php endif; ?>
      </label>
    </div>
    <?php
}

$csrf = csrf_token();
$page_title = "Week's Line-Up";
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<div class="settings-group">
  <h3>See This Week's Line-Up</h3>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_section">
    <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_lineup_field($bySkey[$key]); endforeach; ?>
    <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
  </form>
</div>

<div class="settings-group">
  <h3>DJ / Line-up Cards</h3>
  <p class="group-help">Shown as cards over the line-up background image on the Below page.</p>
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
</div>

<?php include __DIR__ . '/layout_end.php'; ?>
