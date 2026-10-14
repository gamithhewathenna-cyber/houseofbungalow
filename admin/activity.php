<?php
require_once __DIR__ . '/auth.php';
require_role('admin');

$filterUser = trim($_GET['user'] ?? '');

$sql    = 'SELECT * FROM activity_log';
$params = [];
if ($filterUser !== '') {
    $sql .= ' WHERE username = ?';
    $params[] = $filterUser;
}
$sql .= ' ORDER BY created_at DESC LIMIT 300';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$usernames = db()->query('SELECT DISTINCT username FROM activity_log ORDER BY username ASC')->fetchAll(PDO::FETCH_COLUMN);

$page_title = 'Activity Log';
include __DIR__ . '/layout.php';
?>
<p class="muted" style="margin-bottom:16px;">Who did what, and when - every content save, enquiry deletion and admin change is recorded here automatically. Showing the most recent 300 entries.</p>

<form method="get" style="margin-bottom:16px;display:flex;gap:10px;align-items:center;">
  <select name="user" onchange="this.form.submit()" style="width:auto;margin-top:0;">
    <option value="">All admins</option>
    <?php foreach ($usernames as $u): ?>
      <option value="<?= e($u) ?>"<?= $filterUser === $u ? ' selected' : '' ?>><?= e($u) ?></option>
    <?php endforeach; ?>
  </select>
  <?php if ($filterUser !== ''): ?><a href="activity.php" class="btn btn-sm btn-ghost">Clear filter</a><?php endif; ?>
</form>

<?php if (!$rows): ?>
  <p class="muted">No activity recorded yet.</p>
<?php else: ?>
<table>
  <tr>
    <th style="width:150px;">When</th>
    <th>Admin</th>
    <th>Page</th>
    <th>Action</th>
    <th style="width:130px;">IP Address</th>
  </tr>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td class="muted"><?= e($r['created_at']) ?></td>
      <td><?= e($r['username']) ?></td>
      <td><?= e($r['page']) ?></td>
      <td><?= e($r['action']) ?><?php if ($r['details']): ?><br><span class="muted" style="font-size:12px;"><?= e($r['details']) ?></span><?php endif; ?></td>
      <td class="muted"><?= e($r['ip_address']) ?></td>
    </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/layout_end.php'; ?>
