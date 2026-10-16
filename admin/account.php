<?php
require_once __DIR__ . '/auth.php';
require_login();

$error = ''; $ok = '';
$stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
$stmt->execute([current_admin()['id']]);
$me = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $current  = $_POST['current'] ?? '';
    $new      = $_POST['new'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!password_verify($current, $me['password_hash'])) {
        $error = 'Your current password is incorrect.';
    } elseif ($username === '') {
        $error = 'Username cannot be empty.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif ($new !== '' && strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New password and confirmation do not match.';
    } else {
        try {
            if ($new !== '') {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $q = db()->prepare('UPDATE admins SET username=?, email=?, password_hash=? WHERE id=?');
                $q->execute([$username, $email ?: null, $hash, $me['id']]);
            } else {
                $q = db()->prepare('UPDATE admins SET username=?, email=? WHERE id=?');
                $q->execute([$username, $email ?: null, $me['id']]);
            }
            $_SESSION['admin']['username'] = $username;
            $ok = 'Account updated.';
            $me['username'] = $username;
            $me['email']    = $email;
        } catch (PDOException $ex) {
            $error = $ex->getCode() === '23000' ? 'That username or email is already in use.' : 'Update failed.';
        }
    }
}

$page_title = 'Account';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
<?php if ($ok): ?><div class="alert ok"><?= e($ok) ?></div><?php endif; ?>

<p class="muted" style="margin-bottom:18px;">Change your login details. Add an email address so you can sign in with it and use "Forgot password" if you're ever locked out. Leave the password fields blank to keep your current password.</p>

<form method="post" style="max-width:420px;">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <div class="field-row"><label>Username
    <input type="text" name="username" value="<?= e($me['username']) ?>" required></label></div>
  <div class="field-row"><label>Email <span class="help">(used for "Forgot password")</span>
    <input type="email" name="email" value="<?= e($me['email'] ?? '') ?>"></label></div>
  <div class="field-row"><label>Current password
    <input type="password" name="current" required></label></div>
  <div class="field-row"><label>New password <span class="help">(optional)</span>
    <input type="password" name="new"></label></div>
  <div class="field-row"><label>Confirm new password
    <input type="password" name="confirm"></label></div>
  <div class="form-actions"><button class="btn">Update Account</button></div>
</form>
<?php include __DIR__ . '/layout_end.php'; ?>
