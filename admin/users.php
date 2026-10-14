<?php
require_once __DIR__ . '/auth.php';
require_role('admin');

$roles = [
    'admin'     => role_label('admin'),
    'content'   => role_label('content'),
    'enquiries' => role_label('enquiries'),
];

$error = '';
$ok    = '';
$me    = current_admin();

/** Clean a submitted roles[] array down to known, de-duplicated role keys. */
function clean_roles(array $submitted, array $known): array
{
    $clean = array_values(array_unique(array_intersect($submitted, array_keys($known))));
    return $clean ?: ['content'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $username = trim($_POST['new_username'] ?? '');
            $password = $_POST['new_password'] ?? '';
            $confirm  = $_POST['new_password_confirm'] ?? '';
            $roleList = clean_roles((array) ($_POST['new_roles'] ?? []), $roles);
            if ($username === '') {
                $error = 'Username cannot be empty.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Password and confirmation do not match.';
            } else {
                try {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $q = db()->prepare('INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)');
                    $q->execute([$username, $hash, implode(',', $roleList)]);
                    header('Location: users.php?saved=1'); exit;
                } catch (PDOException $ex) {
                    $error = $ex->getCode() === '23000'
                        ? 'That username is already taken.'
                        : 'Could not create the admin account.';
                }
            }
        }

        if ($action === 'update') {
            // Guard against saving a form state that would leave zero accounts with the "Admin" role.
            $wouldBeAdminCount = 0;
            foreach (($_POST['roles'] ?? []) as $id => $submitted) {
                if (in_array('admin', (array) $submitted, true)) {
                    $wouldBeAdminCount++;
                }
            }
            if ($wouldBeAdminCount < 1) {
                $error = 'At least one account must keep the "Admin" role.';
            } else {
                foreach (($_POST['username'] ?? []) as $id => $username) {
                    $id       = (int) $id;
                    $username = trim($username);
                    $password = $_POST['password'][$id] ?? '';
                    $roleList = clean_roles((array) ($_POST['roles'][$id] ?? []), $roles);
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
                            $q = db()->prepare('UPDATE admins SET username=?, password_hash=?, role=? WHERE id=?');
                            $q->execute([$username, $hash, implode(',', $roleList), $id]);
                        } else {
                            $q = db()->prepare('UPDATE admins SET username=?, role=? WHERE id=?');
                            $q->execute([$username, implode(',', $roleList), $id]);
                        }
                        if ($id === (int) $me['id']) {
                            $_SESSION['admin']['username'] = $username;
                            $_SESSION['admin']['role']     = implode(',', $roleList);
                        }
                    } catch (PDOException $ex) {
                        $error = $ex->getCode() === '23000' ? 'Username already taken.' : 'Update failed.';
                    }
                }
                if ($error === '') {
                    header('Location: users.php?saved=1'); exit;
                }
            }
        }

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $total = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
            $adminRoleCount = (int) db()->query("SELECT COUNT(*) FROM admins WHERE FIND_IN_SET('admin', role) > 0")->fetchColumn();
            $stmt = db()->prepare('SELECT role, username FROM admins WHERE id=?');
            $stmt->execute([$id]);
            $target = $stmt->fetch();
            $targetIsAdmin = $target && in_array('admin', array_filter(array_map('trim', explode(',', $target['role'] ?? ''))), true);
            if ($id === (int) $me['id']) {
                $error = "You can't delete your own account while logged in.";
            } elseif ($total <= 1) {
                $error = 'At least one admin account must remain.';
            } elseif ($targetIsAdmin && $adminRoleCount <= 1) {
                $error = 'At least one account must keep the "Admin" role.';
            } else {
                $q = db()->prepare('DELETE FROM admins WHERE id=?');
                $q->execute([$id]);
                header('Location: users.php?saved=1'); exit;
            }
        }
    }
}

$admins = db()->query('SELECT * FROM admins ORDER BY id ASC')->fetchAll();
foreach ($admins as &$a) {
    $a['role_list'] = array_filter(array_map('trim', explode(',', $a['role'] ?? 'admin')));
}
unset($a);

$page_title = 'Admin Users';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<?php if ($ok): ?><div class="alert ok"><?= e($ok) ?></div><?php endif; ?>

<p class="muted" style="margin-bottom:18px;">Everyone listed here can log into this admin panel. Tick as many access roles as an account needs - e.g. someone can be both "Content Editor" and "Enquiries" at once. "Admin" always includes full access regardless of what else is ticked. Leave the password field blank to keep an account's current password.</p>

<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="update">
  <div class="dj-admin-list">
    <?php foreach ($admins as $a): ?>
      <div class="dj-admin-card no-media">
        <div class="dj-admin-fields">
          <div class="user-card-head">
            <span class="user-avatar"><?= e(strtoupper(substr($a['username'], 0, 1))) ?></span>
            <div class="field-row">
              <label>Username
                <input type="text" name="username[<?= $a['id'] ?>]" value="<?= e($a['username']) ?>">
              </label>
            </div>
          </div>
          <div class="field-row">
            <span class="help" style="display:block;margin-bottom:6px;font-weight:600;color:#5a544e;">Access</span>
            <div class="role-checks">
              <?php foreach ($roles as $rkey => $rlabel): ?>
                <label class="checkbox-row">
                  <input type="checkbox" name="roles[<?= $a['id'] ?>][]" value="<?= e($rkey) ?>"<?= in_array($rkey, $a['role_list'], true) ? ' checked' : '' ?>>
                  <?= e($rlabel) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="field-row">
            <label>New Password <span class="help">(optional - leave blank to keep the current one)</span>
              <input type="password" name="password[<?= $a['id'] ?>]" placeholder="Leave blank to keep">
            </label>
          </div>
          <p class="muted" style="font-size:11px;margin:0;">Created <?= e(substr($a['created_at'] ?? '', 0, 10)) ?></p>
        </div>
        <div class="dj-admin-actions">
          <?php if ((int)$a['id'] !== (int)$me['id']): ?>
            <button form="del<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this admin account?')">Delete</button>
          <?php else: ?>
            <span class="muted">You</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
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
<form method="post" class="dj-admin-card no-media dj-admin-add">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="add">
  <div class="dj-admin-fields">
    <div class="field-row"><label>Username
      <input type="text" name="new_username" required></label></div>
    <div class="field-row">
      <span class="help" style="display:block;margin-bottom:6px;font-weight:600;color:#5a544e;">Access</span>
      <div class="role-checks">
        <?php foreach ($roles as $rkey => $rlabel): ?>
          <label class="checkbox-row">
            <input type="checkbox" name="new_roles[]" value="<?= e($rkey) ?>"<?= $rkey === 'content' ? ' checked' : '' ?>>
            <?= e($rlabel) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="field-grid">
      <label>Password
        <input type="password" name="new_password" required></label>
      <label>Confirm password
        <input type="password" name="new_password_confirm" required></label>
    </div>
  </div>
  <div class="dj-admin-actions">
    <button class="btn btn-sm">Add Admin</button>
  </div>
</form>
<?php include __DIR__ . '/layout_end.php'; ?>
