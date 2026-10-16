<?php
require_once __DIR__ . '/auth.php';

if (current_admin()) {
    header('Location: index.php');
    exit;
}

const OTP_MAX_ATTEMPTS = 5;

$error = '';
$info  = '';

if (!empty($_GET['expired'])) {
    $info = "You've been signed out after 30 days - please sign in again.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_check()) {
    $error = 'Session expired. Please try again.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'password';

    if ($action === 'password') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);
        $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && $admin['password_hash'] !== '' && password_verify($password, $admin['password_hash'])) {
            if ($remember) {
                // "Remember me" skips the email code entirely.
                complete_admin_login($admin, true);
                header('Location: index.php');
                exit;
            }
            if (empty($admin['email'])) {
                $error = "This account needs an email on file to sign in (so a sign-in code can be sent) - ask an admin to add one, or tick \"Remember me\" if you're the only admin.";
            } else {
                $code = create_admin_otp((int) $admin['id']);
                send_admin_otp_email($admin, $code);
                $_SESSION['pending_otp_admin_id']  = (int) $admin['id'];
                $_SESSION['pending_otp_username']  = $admin['username'];
                header('Location: login.php'); exit;
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }

    if ($action === 'verify_otp' && !empty($_SESSION['pending_otp_admin_id'])) {
        $stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
        $stmt->execute([$_SESSION['pending_otp_admin_id']]);
        $admin = $stmt->fetch();
        $code  = trim($_POST['code'] ?? '');

        if (!$admin) {
            unset($_SESSION['pending_otp_admin_id'], $_SESSION['pending_otp_username']);
            $error = 'Session expired. Please sign in again.';
        } elseif ((int) $admin['otp_attempts'] >= OTP_MAX_ATTEMPTS) {
            db()->prepare('UPDATE admins SET otp_code = NULL, otp_expires = NULL, otp_attempts = 0 WHERE id = ?')->execute([$admin['id']]);
            unset($_SESSION['pending_otp_admin_id'], $_SESSION['pending_otp_username']);
            $error = 'Too many incorrect attempts. Please sign in again.';
        } elseif ($admin['otp_code'] === null || $admin['otp_expires'] === null) {
            $error = 'That code has expired. Request a new one below.';
        } else {
            // otp_expires is stored in UTC (create_admin_otp uses gmdate()) -
            // tell strtotime() that explicitly, since the app's own default
            // timezone (config/config.php) is Australia/Sydney.
            $stillValid = strtotime($admin['otp_expires'] . ' UTC') > time();

            if ($stillValid && hash_equals((string) $admin['otp_code'], $code)) {
                db()->prepare('UPDATE admins SET otp_code = NULL, otp_expires = NULL, otp_attempts = 0 WHERE id = ?')->execute([$admin['id']]);
                complete_admin_login($admin, false);
                header('Location: index.php');
                exit;
            }
            if (!$stillValid) {
                $error = 'That code has expired. Request a new one below.';
            } else {
                db()->prepare('UPDATE admins SET otp_attempts = otp_attempts + 1 WHERE id = ?')->execute([$admin['id']]);
                $remaining = OTP_MAX_ATTEMPTS - ((int) $admin['otp_attempts'] + 1);
                $error = 'Incorrect code' . ($remaining > 0 ? ' - ' . $remaining . ' attempt' . ($remaining === 1 ? '' : 's') . ' left.' : '.');
            }
        }
    }

    if ($action === 'resend_otp' && !empty($_SESSION['pending_otp_admin_id'])) {
        $stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
        $stmt->execute([$_SESSION['pending_otp_admin_id']]);
        $admin = $stmt->fetch();
        if ($admin) {
            $code = create_admin_otp((int) $admin['id']);
            send_admin_otp_email($admin, $code);
            $info = 'A new code has been sent to ' . $admin['email'] . '.';
        }
    }

    if ($action === 'cancel_otp') {
        unset($_SESSION['pending_otp_admin_id'], $_SESSION['pending_otp_username']);
    }
}

$awaitingOtp = !empty($_SESSION['pending_otp_admin_id']);
$csrf = csrf_token();
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
    <?php if ($info): ?><div class="alert ok"><?= e($info) ?></div><?php endif; ?>

    <?php if ($awaitingOtp): ?>
      <p class="hint" style="margin:0 0 16px;">We've emailed a 6-digit code to the address on file for <strong><?= e($_SESSION['pending_otp_username']) ?></strong>. Enter it below to finish signing in.</p>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="verify_otp">
        <label>Sign-in code
          <input type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required autofocus style="letter-spacing:4px;font-size:18px;text-align:center;">
        </label>
        <button type="submit" class="btn">Verify &amp; Sign In</button>
      </form>
      <form method="post" class="inline-form" style="margin-top:14px;display:flex;gap:16px;justify-content:center;">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="resend_otp">
        <button type="submit" class="hint" style="background:none;border:none;text-decoration:underline;cursor:pointer;padding:0;">Resend code</button>
      </form>
      <form method="post" class="inline-form" style="margin-top:4px;display:flex;justify-content:center;">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="cancel_otp">
        <button type="submit" class="hint" style="background:none;border:none;text-decoration:underline;cursor:pointer;padding:0;">Use a different account</button>
      </form>
    <?php else: ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="password">
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
      <p class="hint">Unless "Remember me" is ticked, a one-time code is emailed to your account to finish signing in.</p>
      <p class="hint"><a href="forgot-password.php">Forgot password?</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
