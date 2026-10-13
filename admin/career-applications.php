<?php
require_once __DIR__ . '/auth.php';
require_login();

// CSV export
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->query('SELECT * FROM career_applications ORDER BY created_at DESC')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="career-applications.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Full Name', 'Email', 'Phone', 'Area Of Interest', 'Position', 'CV', 'Submitted At']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['full_name'], $r['email'], $r['phone'], $r['area_of_interest'], $r['position'], asset($r['cv_path']), $r['created_at']]);
    }
    fclose($out);
    exit;
}

// Delete
require_once __DIR__ . '/helpers.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check() && ($_POST['action'] ?? '') === 'delete') {
    $q = db()->prepare('DELETE FROM career_applications WHERE id = ?');
    $q->execute([(int)($_POST['id'] ?? 0)]);
    header('Location: career-applications.php?saved=1'); exit;
}

$rows = db()->query('SELECT * FROM career_applications ORDER BY created_at DESC')->fetchAll();
$page_title = 'Applications';
include __DIR__ . '/layout.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
  <p class="muted" style="margin:0;"><?= count($rows) ?> application<?= count($rows) === 1 ? '' : 's' ?> submitted from the Join Our Team page.</p>
  <?php if ($rows): ?><a class="btn btn-sm" href="career-applications.php?export=csv">Export CSV</a><?php endif; ?>
</div>

<?php if (!$rows): ?>
  <p class="muted">No applications yet.</p>
<?php else: ?>
<table>
  <tr>
    <th style="width:50px;">#</th>
    <th>Name</th>
    <th>Contact</th>
    <th>Area of Interest</th>
    <th>Position</th>
    <th style="width:90px;">CV</th>
    <th style="width:150px;">Submitted</th>
    <th style="width:90px;"></th>
  </tr>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= e($r['full_name']) ?></td>
      <td><?= e($r['email']) ?><?php if ($r['phone']): ?><br><span class="muted"><?= e($r['phone']) ?></span><?php endif; ?></td>
      <td><?= e($r['area_of_interest']) ?></td>
      <td><?= e($r['position']) ?></td>
      <td><?php if ($r['cv_path']): ?><a class="btn btn-sm" href="<?= e(asset($r['cv_path'])) ?>" target="_blank" rel="noopener">Download</a><?php endif; ?></td>
      <td class="muted"><?= e($r['created_at']) ?></td>
      <td>
        <form method="post" class="inline-form" onsubmit="return confirm('Delete this application?')">
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
