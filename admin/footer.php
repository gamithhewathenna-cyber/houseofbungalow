<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'save_settings') {
                foreach ($_POST['setting'] ?? [] as $k => $v) {
                    save_setting($k, $v);
                }
                header('Location: footer.php?saved=1'); exit;
            }
            if ($action === 'save_nav') {
                foreach (($_POST['nav_title'] ?? []) as $id => $title) {
                    $id = (int)$id;
                    $q = db()->prepare('UPDATE blocks SET title=?, link_url=?, sort=? WHERE id=? AND block_type="footer_nav"');
                    $q->execute([$title, $_POST['nav_url'][$id] ?? '#', (int)($_POST['nav_sort'][$id] ?? 0), $id]);
                }
                header('Location: footer.php?saved=1'); exit;
            }
            if ($action === 'add_nav') {
                $q = db()->prepare('INSERT INTO blocks (block_type,title,link_url,sort,active) VALUES ("footer_nav",?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New Link'), trim($_POST['new_url'] ?? '#'), (int)($_POST['new_sort'] ?? 99)]);
                header('Location: footer.php?saved=1'); exit;
            }
            if ($action === 'delete_nav') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="footer_nav"');
                $q->execute([(int)($_POST['id'] ?? 0)]);
                header('Location: footer.php?saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$stmt = db()->prepare('SELECT * FROM settings WHERE section="footer" ORDER BY sort ASC, id ASC');
$stmt->execute();
$fields = $stmt->fetchAll();
$nav = db()->query("SELECT * FROM blocks WHERE block_type='footer_nav' ORDER BY sort ASC, id ASC")->fetchAll();

$page_title = 'Footer & Nav';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<h2 style="font-size:15px;margin:0 0 12px;color:#5a544e;">Footer Content</h2>
<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="save_settings">
  <?php foreach ($fields as $f): ?>
    <div class="field-row">
      <label><?= e($f['label'] ?: $f['skey']) ?>
        <?php if ($f['field_type']==='textarea'): ?>
          <textarea name="setting[<?= e($f['skey']) ?>]"><?= e($f['svalue']) ?></textarea>
        <?php else: ?>
          <input type="text" name="setting[<?= e($f['skey']) ?>]" value="<?= e($f['svalue']) ?>">
        <?php endif; ?>
      </label>
    </div>
  <?php endforeach; ?>
  <div class="form-actions"><button class="btn">Save Footer Content</button></div>
</form>

<h2 style="font-size:15px;margin:34px 0 12px;color:#5a544e;">Footer Navigation Links</h2>
<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="save_nav">
  <table>
    <tr><th>Label</th><th>Link URL</th><th style="width:70px;">Order</th><th style="width:80px;"></th></tr>
    <?php foreach ($nav as $n): ?>
      <tr>
        <td><input type="text" name="nav_title[<?= $n['id'] ?>]" value="<?= e($n['title']) ?>"></td>
        <td><input type="text" name="nav_url[<?= $n['id'] ?>]" value="<?= e($n['link_url']) ?>"></td>
        <td><input type="text" name="nav_sort[<?= $n['id'] ?>]" value="<?= (int)$n['sort'] ?>" style="width:56px;"></td>
        <td>
          <button form="del<?= $n['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this link?')">Delete</button>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <div class="form-actions"><button class="btn">Save Links</button></div>
</form>

<?php foreach ($nav as $n): ?>
  <form id="del<?= $n['id'] ?>" method="post" class="inline-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="delete_nav">
    <input type="hidden" name="id" value="<?= $n['id'] ?>">
  </form>
<?php endforeach; ?>

<h2 style="font-size:15px;margin:34px 0 12px;color:#5a544e;">Add a Link</h2>
<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="add_nav">
  <div class="row-actions">
    <input type="text" name="new_title" placeholder="Label" style="max-width:240px;">
    <input type="text" name="new_url" placeholder="# or https://…" style="max-width:240px;">
    <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
    <button class="btn btn-sm">Add</button>
  </div>
</form>
<?php include __DIR__ . '/layout_end.php'; ?>
