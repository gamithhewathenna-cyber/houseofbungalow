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
            if ($action === 'save') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id   = (int) $id;
                    $link = $_POST['link_url'][$id] ?? '#';
                    $sort = (int) ($_POST['sort'][$id] ?? 0);
                    $img  = handle_upload('file_' . $id);
                    if ($img !== null) {
                        $q = db()->prepare('UPDATE blocks SET title=?, link_url=?, sort=?, image=? WHERE id=? AND block_type="space"');
                        $q->execute([$title, $link, $sort, $img, $id]);
                    } else {
                        $q = db()->prepare('UPDATE blocks SET title=?, link_url=?, sort=? WHERE id=? AND block_type="space"');
                        $q->execute([$title, $link, $sort, $id]);
                    }
                }
                header('Location: spaces.php?saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$cards = db()->query("SELECT * FROM blocks WHERE block_type='space' ORDER BY sort ASC, id ASC")->fetchAll();

$page_title = 'Three Spaces';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<p class="muted" style="margin-bottom:18px;">The three cards shown under “Three Spaces. One House.” Update the label, image, link and display order.</p>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="save">
  <table>
    <tr><th style="width:110px;">Image</th><th>Label</th><th>Link URL</th><th style="width:70px;">Order</th><th style="width:180px;">Replace image</th></tr>
    <?php foreach ($cards as $c): ?>
      <tr>
        <td><?php if ($c['image']): ?><img src="<?= e(asset($c['image'])) ?>" alt=""><?php endif; ?></td>
        <td><input type="text" name="title[<?= $c['id'] ?>]" value="<?= e($c['title']) ?>"></td>
        <td><input type="text" name="link_url[<?= $c['id'] ?>]" value="<?= e($c['link_url']) ?>"></td>
        <td><input type="text" name="sort[<?= $c['id'] ?>]" value="<?= (int)$c['sort'] ?>" style="width:56px;"></td>
        <td><input type="file" name="file_<?= $c['id'] ?>" accept="image/*"></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <div class="form-actions">
    <button type="submit" class="btn">Save Changes</button>
  </div>
</form>
<?php include __DIR__ . '/layout_end.php'; ?>
