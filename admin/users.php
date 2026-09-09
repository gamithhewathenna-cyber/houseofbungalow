<?php
require_once __DIR__ . '/auth.php';
require_login();

$error = '';
$ok    = '';
$me    = current_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $username = trim($_POST['new_username'] ?? '');
            $password = $_POST['new_password'] ?? '';
            $confirm  = $_POST['new_password_confirm'] ?? '';
            if ($username === '') {
                $error = 'Username cannot be empty.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Password and confirmation do not match.';
            } else {
                try {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $q = db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
                    $q->execute([$username, $hash]);
                    header('Location: users.php?saved=1'); exit;
                } catch (PDOException $ex) {
                    $error = $ex->getCode() === '23000'
                        ? 'That username is already taken.'
                        : 'Could not create the admin account.';
                }
            }
        }

        if ($action === 'update') {
            foreach (($_POST['username'] ?? []) as $id => $username) {
                $id       = (int) $id;
                $username = trim($username);
                $password = $_POST['password'][$id] ?? '';
                if ($username === '') {
                    continue;
                }
                try {
                    if ($password !== '') {
                        if (strlen($password) < 6) {
                            $error = 'New passwords must be at least 6 characters (skipped for "' . $username . '").';
                            continue;
                        }
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $q = db()->prepare('UPDATE admins SET username=?, password_hash=? WHERE id=?');
                        $q->execute([$username, $hash, $id]);
                    } else {
                        $q = db()->prepare('UPDATE admins SET username=? WHERE id=?');
                        $q->execute([$username, $id]);
                    }
                    if ($id === (int) $me['id']) {
                        $_SESSION['admin']['username'] = $username;
                    }
                } catch (PDOException $ex) {
                    $error = $ex->getCode() === '23000' ? 'Username already taken.' : 'Update failed.';
                }
            }
            if ($error === '') {
                header('Location: users.php?saved=1'); exit;
            }
        }

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $total = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
            if ($id === (int) $me['id']) {
                $error = "You can't delete your own account while logged in.";
            } elseif ($total <= 1) {
                $error = 'At least one admin account must remain.';
            } else {
                $q = db()->prepare('DELETE FROM admins WHERE id=?');
                $q->execute([$id]);
                header('Location: users.php?saved=1'); exit;
            }
        }
    }
}

$admins = db()->query('SELECT * FROM admins ORDER BY id ASC')->fetchAll();

$page_title = 'Admin Users';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<?php if ($ok): ?><div class="alert ok"><?= e($ok) ?></div><?php endif; ?>

<p class="muted" style="margin-bottom:18px;">Everyone listed here can log into this admin panel. Leave a password field blank to keep that account's current password.</p>

<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="update">
  <table>
    <tr><th>Username</th><th>New Password</th><th style="width:140px;">Created</th><th style="width:90px;"></th></tr>
    <?php foreach ($admins as $a): ?>
      <tr>
        <td><input type="text" name="username[<?= $a['id'] ?>]" value="<?= e($a['username']) ?>"></td>
        <td><input type="password" name="password[<?= $a['id'] ?>]" placeholder="Leave blank to keep"></td>
        <td class="muted"><?= e(substr($a['created_at'] ?? '', 0, 10)) ?></td>
        <td>
          <?php if ((int)$a['id'] !== (int)$me['id']): ?>
            <button form="del<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this admin account?')">Delete</button>
          <?php else: ?>
            <span class="muted">You</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <div class="form-actions"><button class="btn">Save Changes</button></div>
</form>

<?php foreach ($admins as $a): ?>
  <form id="del<?= $a['id'] ?>" method="post" class="inline-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= $a['id'] ?>">
  </form>
<?php endforeach; ?>

<h2 style="font-size:15px;margin:34px 0 12px;color:#5a544e;">Add an Admin</h2>
<form method="post" style="max-width:420px;">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="add">
  <div class="field-row"><label>Username
    <input type="text" name="new_username" required></label></div>
  <div class="field-row"><label>Password
    <input type="password" name="new_password" required></label></div>
  <div class="field-row"><label>Confirm password
    <input type="password" name="new_password_confirm" required></label></div>
  <div class="form-actions"><button class="btn">Add Admin</button></div>
</form>
<?php include __DIR__ . '/layout_end.php'; ?>
