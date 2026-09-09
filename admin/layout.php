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

$nav = [
    'index.php'       => 'Dashboard',
    'section.php?s=header'  => 'Header & Nav',
    'section.php?s=hero'    => 'Hero',
    'section.php?s=intro'   => 'Intro',
    'spaces.php'      => 'Three Spaces',
    'section.php?s=door'    => 'The Door',
    'section.php?s=mood'    => 'Every Mood',
    'section.php?s=whatson' => "What's On",
    'section.php?s=events'  => 'Private Events',
    'footer.php'      => 'Footer & Nav',
    'brands.php'      => 'Brand Logos',
    'subscribers.php' => 'Subscribers',
    'account.php'     => 'Account',
];
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
    <?php foreach ($nav as $href => $label):
        $active = ($current . (isset($_GET['s']) ? '?s=' . $_GET['s'] : '')) === $href
               || ($href === 'index.php' && $current === 'index.php' && !isset($_GET['s']));
    ?>
      <a href="<?= e($href) ?>" class="<?= $active ? 'active' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
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
