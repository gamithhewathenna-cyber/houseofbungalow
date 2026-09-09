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
    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = ['id' => $admin['id'], 'username' => $admin['username']];
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
<title>Admin Login — <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="login-body">
  <div class="login-card">
    <h1>House of Bungalow</h1>
    <p class="login-sub">Admin Panel</p>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <label>Username
        <input type="text" name="username" required autofocus>
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <button type="submit" class="btn">Sign In</button>
    </form>
    <p class="hint">Default: <code>admin</code> / <code>admin123</code> — change after login.</p>
  </div>
</body>
</html>
