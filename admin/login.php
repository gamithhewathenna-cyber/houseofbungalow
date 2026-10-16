<?php
require_once __DIR__ . '/auth.php';

if (current_admin()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();
    if ($admin && $admin['password_hash'] !== '' && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = ['id' => $admin['id'], 'username' => $admin['username'], 'role' => $admin['role'] ?? 'admin'];
        if (!empty($_POST['remember'])) {
            // Extend the session cookie to 30 days instead of the default
            // "until the browser closes" - everything else about the
            // session (path, domain, secure, httponly) stays the same.
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + 30 * 24 * 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        log_activity('login');
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="login-body">
  <div class="login-card">
    <h1>House of Bungalow</h1>
    <p class="login-sub">Admin Panel</p>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <label>Username or email
        <input type="text" name="username" required autofocus>
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <label style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13px;color:#5a544e;margin-bottom:18px;">
        <input type="checkbox" name="remember" value="1" style="width:auto;margin:0;">
        Remember me
      </label>
      <button type="submit" class="btn">Sign In</button>
    </form>
    <p class="hint"><a href="forgot-password.php">Forgot password?</a></p>
  </div>
</body>
</html>
