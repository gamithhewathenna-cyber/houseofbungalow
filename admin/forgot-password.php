<?php
require_once __DIR__ . '/auth.php';

if (current_admin()) {
    header('Location: index.php');
    exit;
}

$ok    = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please reload the page and try again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        if ($identifier !== '') {
            $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$identifier, $identifier]);
            $admin = $stmt->fetch();
            if ($admin && !empty($admin['email'])) {
                $token = create_admin_reset_token($admin['id']);
                send_admin_reset_email($admin, $token);
            }
        }
        // Same message either way - doesn't reveal whether an account exists.
        $ok = "If that account exists and has an email on file, we've sent a password reset link to it. Check your inbox (and spam folder).";
    }
}
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password - <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="login-body">
  <div class="login-card">
    <h1>House of Bungalow</h1>
    <p class="login-sub">Reset Password</p>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
    <?php if ($ok): ?>
      <div class="alert ok"><?= e($ok) ?></div>
      <p class="hint"><a href="login.php">Back to Sign In</a></p>
    <?php else: ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <label>Username or email
          <input type="text" name="identifier" required autofocus>
        </label>
        <button type="submit" class="btn">Send Reset Link</button>
      </form>
      <p class="hint"><a href="login.php">Back to Sign In</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
