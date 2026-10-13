<?php
require_once __DIR__ . '/auth.php';
require_login();

// CSV export
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->query('SELECT * FROM vip_bottle_enquiries ORDER BY created_at DESC')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="vip-bottle-enquiries.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Full Name', 'Phone', 'Company', 'Occasion', 'Guests', 'Preferred Date', 'Submitted At']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['full_name'], $r['phone'], $r['company'], $r['occasion'], $r['guests'], $r['preferred_date'], $r['created_at']]);
    }
    fclose($out);
    exit;
}

// Delete
require_once __DIR__ . '/helpers.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check() && ($_POST['action'] ?? '') === 'delete') {
    $q = db()->prepare('DELETE FROM vip_bottle_enquiries WHERE id = ?');
    $q->execute([(int)($_POST['id'] ?? 0)]);
    header('Location: vip-bottle-enquiries.php?saved=1'); exit;
}

$rows = db()->query('SELECT * FROM vip_bottle_enquiries ORDER BY created_at DESC')->fetchAll();
$page_title = 'VIP Enquiries';
include __DIR__ . '/layout.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
  <p class="muted" style="margin:0;"><?= count($rows) ?> enquir<?= count($rows) === 1 ? 'y' : 'ies' ?> submitted from the Below page's "BOOK BOTTLE SERVICE" popup.</p>
  <?php if ($rows): ?><a class="btn btn-sm" href="vip-bottle-enquiries.php?export=csv">Export CSV</a><?php endif; ?>
</div>

<?php if (!$rows): ?>
  <p class="muted">No VIP Bottle Service enquiries yet.</p>
<?php else: ?>
<table>
  <tr>
    <th style="width:50px;">#</th>
    <th>Name</th>
    <th>Phone</th>
    <th>Company</th>
    <th>Occasion</th>
    <th>Guests</th>
    <th>Preferred Date</th>
    <th style="width:150px;">Submitted</th>
    <th style="width:90px;"></th>
  </tr>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= e($r['full_name']) ?></td>
      <td><?= e($r['phone']) ?></td>
      <td><?= e($r['company']) ?></td>
      <td><?= e($r['occasion']) ?></td>
      <td><?= e($r['guests']) ?></td>
      <td><?= e($r['preferred_date']) ?></td>
      <td class="muted"><?= e($r['created_at']) ?></td>
      <td>
        <form method="post" class="inline-form" onsubmit="return confirm('Delete this enquiry?')">
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
