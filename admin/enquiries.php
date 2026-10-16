<?php
require_once __DIR__ . '/auth.php';
require_role('enquiries');

// CSV export
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->query('SELECT * FROM enquiries ORDER BY created_at DESC')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="enquiries.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Full Name', 'Phone', 'Email', 'Company', 'Type Of Event', 'Preferred Space', 'Preferred Date', 'Preferred Time', 'Guests', 'Message', 'Event Card', 'Submitted At']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['full_name'], $r['phone'], $r['email'], $r['company'], $r['event_type'], $r['preferred_space'], $r['preferred_date'], $r['preferred_time'], $r['guests'], $r['message'], $r['source'], $r['created_at']]);
    }
    fclose($out);
    exit;
}

// Delete
require_once __DIR__ . '/helpers.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check() && ($_POST['action'] ?? '') === 'delete') {
    $q = db()->prepare('DELETE FROM enquiries WHERE id = ?');
    $q->execute([(int)($_POST['id'] ?? 0)]);
    header('Location: enquiries.php?saved=1'); exit;
}

// Notification email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check() && ($_POST['action'] ?? '') === 'save_notify') {
    save_setting('pe_enquiry_to_email', trim($_POST['to_email'] ?? ''));
    save_setting('pe_enquiry_cc_email', trim($_POST['cc_email'] ?? ''));
    header('Location: enquiries.php?saved=1'); exit;
}

$rows = db()->query('SELECT * FROM enquiries ORDER BY created_at DESC')->fetchAll();
db()->exec('UPDATE enquiries SET is_read = 1 WHERE is_read = 0');
$page_title = 'Enquiries';
include __DIR__ . '/layout.php';
?>
<div class="settings-group" style="margin-bottom:20px;">
  <h3 style="font-size:14px;">Notification Email</h3>
  <p class="group-help">Where new enquiries get emailed to.</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="save_notify">
    <div class="field-grid">
      <label>Send to
        <input type="text" name="to_email" value="<?= e(setting('pe_enquiry_to_email')) ?>" placeholder="you@yourdomain.com">
      </label>
      <label>CC <span class="help">(optional)</span>
        <input type="text" name="cc_email" value="<?= e(setting('pe_enquiry_cc_email')) ?>" placeholder="team@yourdomain.com">
      </label>
    </div>
    <div class="form-actions"><button class="btn btn-sm">Save</button></div>
  </form>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
  <p class="muted" style="margin:0;"><?= count($rows) ?> enquir<?= count($rows) === 1 ? 'y' : 'ies' ?> submitted from the Private Events ENQUIRE popup.</p>
  <?php if ($rows): ?><a class="btn btn-sm" href="enquiries.php?export=csv">Export CSV</a><?php endif; ?>
</div>

<?php if (!$rows): ?>
  <p class="muted">No enquiries yet.</p>
<?php else: ?>
<table>
  <tr>
    <th style="width:50px;">#</th>
    <th>Name</th>
    <th>Contact</th>
    <th>Event</th>
    <th>Space</th>
    <th>Date / Time</th>
    <th>Guests</th>
    <th style="width:150px;">Submitted</th>
    <th style="width:90px;"></th>
  </tr>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= e($r['full_name']) ?><?php if ($r['source']): ?><br><span class="muted" style="font-size:12px;">via "<?= e($r['source']) ?>"</span><?php endif; ?></td>
      <td><?= e($r['phone']) ?><br><span class="muted"><?= e($r['email']) ?></span><?php if ($r['company']): ?><br><span class="muted"><?= e($r['company']) ?></span><?php endif; ?></td>
      <td><?= e($r['event_type']) ?></td>
      <td><?= e($r['preferred_space']) ?></td>
      <td><?= e($r['preferred_date']) ?><?php if ($r['preferred_time']): ?><br><span class="muted"><?= e($r['preferred_time']) ?></span><?php endif; ?></td>
      <td><?= e($r['guests']) ?></td>
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
      <tr><td></td><td colspan="8" class="muted" style="padding-top:0;"><?= e($r['message']) ?></td></tr>
    <?php endif; ?>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/layout_end.php'; ?>
