<?php
require_once __DIR__ . '/auth.php';
require_role('enquiries');

// CSV export
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->query('SELECT * FROM press_enquiries ORDER BY created_at DESC')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="press-enquiries.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Full Name', 'Publication / Organisation', 'Email', 'Phone', 'Nature of Enquiry', 'Enquiry', 'Submitted At']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['full_name'], $r['organisation'], $r['email'], $r['phone'], $r['enquiry_type'], $r['message'], $r['created_at']]);
    }
    fclose($out);
    exit;
}

// Delete
require_once __DIR__ . '/helpers.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check() && ($_POST['action'] ?? '') === 'delete') {
    $q = db()->prepare('DELETE FROM press_enquiries WHERE id = ?');
    $q->execute([(int)($_POST['id'] ?? 0)]);
    header('Location: press-enquiries.php?saved=1'); exit;
}

// Notification email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check() && ($_POST['action'] ?? '') === 'save_notify') {
    save_setting('press_to_email', trim($_POST['to_email'] ?? ''));
    save_setting('press_cc_email', trim($_POST['cc_email'] ?? ''));
    header('Location: press-enquiries.php?saved=1'); exit;
}

$rows = db()->query('SELECT * FROM press_enquiries ORDER BY created_at DESC')->fetchAll();
db()->exec('UPDATE press_enquiries SET is_read = 1 WHERE is_read = 0');
$page_title = 'Press Enquiries';
include __DIR__ . '/layout.php';
?>
<div class="settings-group" style="margin-bottom:20px;">
  <h3 style="font-size:14px;">Notification Email</h3>
  <p class="group-help">Where new media enquiries get emailed to.</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="save_notify">
    <div class="field-grid">
      <label>Send to
        <input type="text" name="to_email" value="<?= e(setting('press_to_email')) ?>" placeholder="you@yourdomain.com">
      </label>
      <label>CC <span class="help">(optional)</span>
        <input type="text" name="cc_email" value="<?= e(setting('press_cc_email')) ?>" placeholder="team@yourdomain.com">
      </label>
    </div>
    <div class="form-actions"><button class="btn btn-sm">Save</button></div>
  </form>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
  <p class="muted" style="margin:0;"><?= count($rows) ?> enquir<?= count($rows) === 1 ? 'y' : 'ies' ?> submitted from the Press &amp; Media page.</p>
  <?php if ($rows): ?><a class="btn btn-sm" href="press-enquiries.php?export=csv">Export CSV</a><?php endif; ?>
</div>

<?php if (!$rows): ?>
  <p class="muted">No media enquiries yet.</p>
<?php else: ?>
<table>
  <tr>
    <th style="width:50px;">#</th>
    <th>Name</th>
    <th>Contact</th>
    <th>Publication / Org</th>
    <th>Nature of Enquiry</th>
    <th style="width:150px;">Submitted</th>
    <th style="width:90px;"></th>
  </tr>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= e($r['full_name']) ?></td>
      <td><?= e($r['email']) ?><?php if ($r['phone']): ?><br><span class="muted"><?= e($r['phone']) ?></span><?php endif; ?></td>
      <td><?= e($r['organisation']) ?></td>
      <td><?= e($r['enquiry_type']) ?></td>
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
    <?php if ($r['message']): ?>
      <tr><td></td><td colspan="6" class="muted" style="padding-top:0;"><?= e($r['message']) ?></td></tr>
    <?php endif; ?>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/layout_end.php'; ?>
