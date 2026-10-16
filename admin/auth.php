<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/email-template.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function require_login(): void
{
    if (!current_admin()) {
        header('Location: login.php');
        exit;
    }
    // Automatically record every successful admin form submission as work
    // history - every admin page calls require_login() (directly or via
    // require_role()) before it handles its POST, so this one hook covers
    // every page without needing a log_activity() call added to each one.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
        $action = $_POST['action'] ?? '';
        if ($action !== '') {
            log_activity($action);
        }
    }
}

/**
 * Admin roles (an account can hold more than one at once, stored as a
 * comma-separated list in admins.role, e.g. "content,enquiries" - the
 * stored key is still "enquiries" for backward compatibility, it's only
 * the displayed label that reads "Lead Management"):
 *   admin     - full access to everything, including Admin Users and Website Settings
 *   content   - can edit page content across the site
 *   enquiries - can view/manage the whole Lead Management section (Enquiries, VIP Enquiries, Applications, Subscribers)
 */
function role_label(string $key): string
{
    $labels = ['admin' => 'Admin', 'content' => 'Content Editor', 'enquiries' => 'Lead Management'];
    return $labels[$key] ?? ucfirst($key);
}

/**
 * The current admin's roles, parsed from the comma-separated session value.
 */
function admin_roles(): array
{
    $raw = current_admin()['role'] ?? 'admin';
    $list = array_filter(array_map('trim', explode(',', $raw)));
    return $list ?: ['admin'];
}

/**
 * $area is one of: 'content', 'enquiries', 'admin'. An account with the
 * 'admin' role always has access to every area.
 */
function admin_can(string $area): bool
{
    $roles = admin_roles();
    return in_array('admin', $roles, true) || in_array($area, $roles, true);
}

/**
 * Like require_login(), but also blocks any role that doesn't have access
 * to $area ('content', 'enquiries' or 'admin'). Call this instead of
 * require_login() at the top of a page that belongs to one of those areas.
 */
function require_role(string $area): void
{
    require_login();
    if (!admin_can($area)) {
        http_response_code(403);
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Access denied - <?= e(SITE_NAME) ?> Admin</title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">
  <div style="max-width:420px;margin:90px auto;text-align:center;">
    <h1 style="font-size:19px;margin-bottom:10px;">Access denied</h1>
    <p class="muted">Your admin account doesn't have access to this page.</p>
    <p style="margin-top:18px;"><a href="index.php" class="btn btn-sm">Back to Dashboard</a></p>
  </div>
</body>
</html>
        <?php
        exit;
    }
}

/**
 * Records one row of "work history" - who did what, on which admin page.
 * Never throws: a logging failure should never break the admin panel.
 */
function log_activity(string $action, string $details = ''): void
{
    $admin = current_admin();
    if (!$admin) {
        return;
    }
    try {
        $stmt = db()->prepare(
            'INSERT INTO activity_log (admin_id, username, page, action, details, ip_address) VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([
            $admin['id'] ?? null,
            $admin['username'] ?? '',
            basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ($_SERVER['SCRIPT_NAME'] ?? '')),
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
    } catch (Throwable $e) {
        // ignore - logging must never break the admin panel
    }
}

/**
 * Creates a fresh, random "set/reset your password" token for an admin
 * account, valid for 48 hours, and saves it on that account. Used both
 * for a brand-new admin's first-time invite and for "forgot password".
 */
function create_admin_reset_token(int $adminId): string
{
    $token = bin2hex(random_bytes(32));
    // Stored (and compared, in set-password.php) in UTC - the app's
    // local timezone (config/config.php) shouldn't have to match
    // whatever timezone the MySQL server happens to default to.
    $expires = gmdate('Y-m-d H:i:s', time() + 48 * 3600);
    $q = db()->prepare('UPDATE admins SET reset_token = ?, reset_token_expires = ? WHERE id = ?');
    $q->execute([$token, $expires, $adminId]);
    return $token;
}

function admin_set_password_url(string $token): string
{
    return site_origin() . rtrim(BASE_URL, '/') . '/admin/set-password.php?token=' . $token;
}

/**
 * Emailed to a brand-new admin account so they can set their own password
 * and log in - the admin who created the account never sees or sets it.
 */
function send_admin_invite_email(array $admin, string $token): bool
{
    if (empty($admin['email'])) {
        return false;
    }
    $link = admin_set_password_url($token);
    $body = email_p('Hi ' . $admin['username'] . ',')
        . email_p('An admin account has been created for you on the ' . SITE_NAME . ' website admin panel. Set your password to activate it - this link works for 48 hours.')
        . email_p('Once that\'s done, sign in with your username or email: ' . $admin['username'], '#9a9089')
        . '<p style="margin:0;font-size:12px;color:#9a9089;">If you weren\'t expecting this, you can safely ignore this email.</p>';
    $html = email_layout('Set Up Your Admin Account', $body, ['label' => 'Set Your Password', 'url' => $link]);
    return app_mail($admin['email'], 'Set up your ' . SITE_NAME . ' admin account', $html, ['Content-Type: text/html; charset=UTF-8']);
}

/**
 * Emailed when someone uses "Forgot password" on the login page.
 */
function send_admin_reset_email(array $admin, string $token): bool
{
    if (empty($admin['email'])) {
        return false;
    }
    $link = admin_set_password_url($token);
    $body = email_p('Hi ' . $admin['username'] . ',')
        . email_p('We received a request to reset the password for your ' . SITE_NAME . ' admin account. This link works for 48 hours.')
        . '<p style="margin:0;font-size:12px;color:#9a9089;">If you didn\'t request this, you can ignore this email - your password won\'t change.</p>';
    $html = email_layout('Reset Your Password', $body, ['label' => 'Reset Password', 'url' => $link]);
    return app_mail($admin['email'], 'Reset your ' . SITE_NAME . ' admin password', $html, ['Content-Type: text/html; charset=UTF-8']);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}
