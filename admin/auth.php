<?php
require_once __DIR__ . '/../includes/functions.php';

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
 * Admin roles:
 *   admin     - full access to everything, including Admin Users and Website Settings
 *   content   - can edit page content across the site, but not enquiries or system settings
 *   enquiries - can view/manage enquiries, VIP enquiries, applications and subscribers only
 */
function admin_role(): string
{
    return current_admin()['role'] ?? 'admin';
}

/**
 * $area is one of: 'content', 'enquiries', 'admin'.
 */
function admin_can(string $area): bool
{
    $role = admin_role();
    if ($role === 'admin') {
        return true;
    }
    return $role === $area;
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
