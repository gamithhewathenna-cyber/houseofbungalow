<?php
require_once __DIR__ . '/auth.php';
require_login();

// CSV export
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->query('SELECT email, created_at FROM subscribers ORDER BY created_at DESC')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="subscribers.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Email', 'Subscribed At']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['email'], $r['created_at']]);
    }
    fclose($out);
    exit;
}

// Delete
require_once __DIR__ . '/helpers.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check() && ($_POST['action'] ?? '') === 'delete') {
    $q = db()->prepare('DELETE FROM subscribers WHERE id = ?');
    $q->execute([(int)($_POST['id'] ?? 0)]);
    header('Location: subscribers.php?saved=1'); exit;
}

$rows = db()->query('SELECT * FROM subscribers ORDER BY created_at DESC')->fetchAll();
$page_title = 'Subscribers';
include __DIR__ . '/layout.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
  <p class="muted" style="margin:0;"><?= count($rows) ?> subscriber<?= count($rows) === 1 ? '' : 's' ?> collected from the footer newsletter form.</p>
  <?php if ($rows): ?><a class="btn btn-sm" href="subscribers.php?export=csv">Export CSV</a><?php endif; ?>
</div>

<?php if (!$rows): ?>
  <p class="muted">No subscribers yet.</p>
<?php else: ?>
<table>
  <tr><th style="width:50px;">#</th><th>Email</th><th style="width:200px;">Subscribed</th><th style="width:90px;"></th></tr>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= e($r['email']) ?></td>
      <td class="muted"><?= e($r['created_at']) ?></td>
      <td>
        <form method="post" class="inline-form" onsubmit="return confirm('Delete this subscriber?')">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-danger">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/layout_end.php'; ?>
