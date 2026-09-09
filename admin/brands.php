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
                    $id  = (int)$id;
                    $img = handle_upload('file_' . $id);
                    if ($img !== null) {
                        $q = db()->prepare('UPDATE blocks SET title=?, sort=?, image=? WHERE id=? AND block_type="brand"');
                        $q->execute([$title, (int)($_POST['sort'][$id] ?? 0), $img, $id]);
                    } else {
                        $q = db()->prepare('UPDATE blocks SET title=?, sort=? WHERE id=? AND block_type="brand"');
                        $q->execute([$title, (int)($_POST['sort'][$id] ?? 0), $id]);
                    }
                }
                header('Location: brands.php?saved=1'); exit;
            }
            if ($action === 'clear_img') {
                $q = db()->prepare('UPDATE blocks SET image="" WHERE id=? AND block_type="brand"');
                $q->execute([(int)($_POST['id'] ?? 0)]);
                header('Location: brands.php?saved=1'); exit;
            }
            if ($action === 'add') {
                $q = db()->prepare('INSERT INTO blocks (block_type,title,image,sort,active) VALUES ("brand",?, "", ?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'Brand'), (int)($_POST['new_sort'] ?? 99)]);
                header('Location: brands.php?saved=1'); exit;
            }
            if ($action === 'delete') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="brand"');
                $q->execute([(int)($_POST['id'] ?? 0)]);
                header('Location: brands.php?saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$brands = db()->query("SELECT * FROM blocks WHERE block_type='brand' ORDER BY sort ASC, id ASC")->fetchAll();
$page_title = 'Brand Logos';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<p class="muted" style="margin-bottom:18px;">The partner/brand marks in the footer. Upload a logo image, or leave it empty to show the name as text. Set order left-to-right.</p>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="save">
  <table>
    <tr><th style="width:130px;">Logo</th><th>Name</th><th style="width:70px;">Order</th><th style="width:190px;">Upload logo</th><th style="width:140px;"></th></tr>
    <?php foreach ($brands as $b): ?>
      <tr>
        <td><?php if ($b['image']): ?><img src="<?= e(asset($b['image'])) ?>" alt=""><?php else: ?><span class="muted">text</span><?php endif; ?></td>
        <td><input type="text" name="title[<?= $b['id'] ?>]" value="<?= e($b['title']) ?>"></td>
        <td><input type="text" name="sort[<?= $b['id'] ?>]" value="<?= (int)$b['sort'] ?>" style="width:56px;"></td>
        <td><input type="file" name="file_<?= $b['id'] ?>" accept="image/*"></td>
        <td class="row-actions">
          <?php if ($b['image']): ?><button form="clr<?= $b['id'] ?>" class="btn btn-sm btn-ghost">Clear</button><?php endif; ?>
          <button form="delb<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this brand?')">Delete</button>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <div class="form-actions"><button class="btn">Save Changes</button></div>
</form>

<?php foreach ($brands as $b): ?>
  <form id="clr<?= $b['id'] ?>" method="post" class="inline-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="clear_img"><input type="hidden" name="id" value="<?= $b['id'] ?>">
  </form>
  <form id="delb<?= $b['id'] ?>" method="post" class="inline-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $b['id'] ?>">
  </form>
<?php endforeach; ?>

<h2 style="font-size:15px;margin:34px 0 12px;color:#5a544e;">Add a Brand</h2>
<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="add">
  <div class="row-actions">
    <input type="text" name="new_title" placeholder="Brand name" style="max-width:240px;">
    <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
    <button class="btn btn-sm">Add</button>
  </div>
</form>
<?php include __DIR__ . '/layout_end.php'; ?>
