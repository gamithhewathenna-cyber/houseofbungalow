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
    ['href' => 'index.php',       'label' => 'Dashboard',   'icon' => 'grid'],
    ['href' => 'homepage.php',    'label' => 'Home Page',   'icon' => 'home'],
    ['href' => 'subscribers.php', 'label' => 'Subscribers', 'icon' => 'mail'],
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
