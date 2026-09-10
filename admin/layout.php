<?php
require_once __DIR__ . '/auth.php';
require_login();

/**
 * Usage:
 *   $page_title = 'Dashboard';
 *   include 'layout.php';   // opens the shell
 *   ... page content ...
 *   include 'layout_end.php';
 */
$page_title = $page_title ?? 'Admin';
$current    = basename($_SERVER['PHP_SELF']);

$mainNav = [
    ['href' => 'index.php',       'label' => 'Dashboard',          'icon' => 'grid'],
    ['href' => 'homepage.php',    'label' => 'Home Page',          'icon' => 'home'],
    ['href' => 'restaurant.php',  'label' => 'Restaurant & Menus', 'icon' => 'fork'],
    ['href' => 'below.php',       'label' => 'Below',              'icon' => 'moon'],
    ['href' => 'whatson.php',     'label' => "What's On",          'icon' => 'ticket'],
    ['href' => 'brunch.php',      'label' => 'Brunch',             'icon' => 'coffee'],
    ['href' => 'dinnerparty.php', 'label' => 'Dinner Party',       'icon' => 'glass'],
    ['href' => 'privateevents.php', 'label' => 'Private Events',  'icon' => 'gift'],
    ['href' => 'lineup.php',      'label' => "Week's Line-Up",     'icon' => 'calendar'],
    ['href' => 'subscribers.php', 'label' => 'Subscribers',        'icon' => 'mail'],
];
$systemNav = [
    ['href' => 'section.php?s=website', 'label' => 'Website Settings', 'icon' => 'gear'],
    ['href' => 'users.php',             'label' => 'Admin Users',      'icon' => 'users'],
    ['href' => 'account.php',           'label' => 'Account',          'icon' => 'user'],
];

function nav_icon(string $key): void
{
    $icons = [
        'grid'  => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'home'  => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9.5V20h13V9.5"/><path d="M9.5 20v-6h5v6"/>',
        'mail'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M4 6.5l8 6.5 8-6.5"/>',
        'gear'  => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>',
        'users' => '<circle cx="8.5" cy="8" r="3"/><path d="M2.5 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M14.5 20c.3-2.6 2.2-4.6 4.7-4.9"/>',
        'user'  => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/>',
        'fork'  => '<path d="M6 2v8a2 2 0 0 0 4 0V2M8 2v20M18 2c-2 1.5-2 4-2 6 0 2.5 1 3 2 3.5V22"/>',
        'moon'  => '<path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5Z"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'ticket' => '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8Z"/><path d="M13 6v2M13 11v2M13 16v2"/>',
        'coffee' => '<path d="M4 9h13v6a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V9Z"/><path d="M17 10h1.5a2.5 2.5 0 0 1 0 5H17"/><path d="M7 2c0 1.2-1 1.3-1 2.5S7 6.3 7 7.5M11 2c0 1.2-1 1.3-1 2.5S11 6.3 11 7.5"/>',
        'glass' => '<path d="M5 3h14l-2.2 9.2a4.8 4.8 0 0 1-9.6 0L5 3Z"/><path d="M12 15v6M8 21h8"/>',
        'gift'  => '<rect x="3" y="9" width="18" height="4" rx="1"/><rect x="4" y="13" width="16" height="8" rx="1"/><path d="M12 9v12M12 9C9.5 9 8 7.5 8 6a2.5 2.5 0 0 1 4-2c1 1.2 1 3 0 5ZM12 9c2.5 0 4-1.5 4-3a2.5 2.5 0 0 0-4-2c-1 1.2-1 3 0 5Z"/>',
    ];
    echo '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . ($icons[$key] ?? '') . '</svg>';
}

function nav_link(array $item, string $current): void
{
    $base   = strtok($item['href'], '?');
    $active = $current === $base;
    ?>
    <a href="<?= e($item['href']) ?>" class="nav-item<?= $active ? ' active' : '' ?>">
      <?php nav_icon($item['icon']); ?>
      <span><?= e($item['label']) ?></span>
    </a>
    <?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?> — <?= e(SITE_NAME) ?> Admin</title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">
<aside class="sidebar">
  <div class="sidebar-brand">House of Bungalow<span>Admin</span></div>
  <nav class="sidebar-nav">
    <?php foreach ($mainNav as $item): nav_link($item, $current); endforeach; ?>

    <div class="nav-section-label">System</div>
    <?php foreach ($systemNav as $item): nav_link($item, $current); endforeach; ?>
  </nav>
  <a class="sidebar-logout" href="logout.php">Log out</a>
</aside>
<main class="content">
  <div class="topbar">
    <h1><?= e($page_title) ?></h1>
    <div class="topbar-right">
      <a href="../index.php" target="_blank" class="btn btn-ghost">View Site ↗</a>
      <span class="who"><?= e(current_admin()['username']) ?></span>
    </div>
  </div>
  <div class="panel">
    <?php if (!empty($_GET['saved'])): ?>
      <div class="alert ok">Changes saved.</div>
    <?php endif; ?>
