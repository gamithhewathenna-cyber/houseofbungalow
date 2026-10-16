<?php
require_once __DIR__ . '/auth.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$admin = null;
$error = '';

if ($token !== '') {
    $stmt = db()->prepare('SELECT * FROM admins WHERE reset_token = ? AND reset_token_expires > UTC_TIMESTAMP() LIMIT 1');
    $stmt->execute([$token]);
    $admin = $stmt->fetch();
}

if (!$admin) {
    $error = 'This link is invalid or has expired. Ask an admin to resend your invite, or use "Forgot password" on the sign-in page for a fresh link.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please reload the page and try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';
        if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $q = db()->prepare('UPDATE admins SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?');
            $q->execute([$hash, $admin['id']]);
            complete_admin_login($admin, false);
            log_activity('set_password');
            header('Location: index.php');
            exit;
        }
    }
}
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Set Your Password - <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="login-body">
  <div class="login-card">
    <h1>House of Bungalow</h1>
    <p class="login-sub"><?= $admin ? 'Set Your Password' : 'Link Expired' ?></p>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
    <?php if ($admin): ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <p class="hint" style="margin:0 0 16px;">Setting the password for <strong><?= e($admin['username']) ?></strong>.</p>
        <label>New password
          <input type="password" name="password" required autofocus>
        </label>
        <label>Confirm password
          <input type="password" name="password_confirm" required>
        </label>
        <button type="submit" class="btn">Set Password &amp; Sign In</button>
      </form>
    <?php else: ?>
      <p class="hint"><a href="login.php">Back to Sign In</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
