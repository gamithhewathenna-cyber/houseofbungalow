<?php
require_once __DIR__ . '/auth.php';
require_login();

$subCount   = (int) db()->query('SELECT COUNT(*) FROM subscribers')->fetchColumn();
$spaceCount = (int) db()->query("SELECT COUNT(*) FROM blocks WHERE block_type='space' AND active=1")->fetchColumn();
$navCount   = (int) db()->query("SELECT COUNT(*) FROM blocks WHERE block_type='footer_nav' AND active=1")->fetchColumn();

$page_title = 'Dashboard';
include __DIR__ . '/layout.php';
?>
<div class="dash-grid">
  <div class="dash-card"><div class="num"><?= $subCount ?></div><div class="lbl">Subscribers</div></div>
  <div class="dash-card"><div class="num"><?= $spaceCount ?></div><div class="lbl">Space Cards</div></div>
  <div class="dash-card"><div class="num"><?= $navCount ?></div><div class="lbl">Footer Links</div></div>
</div>

<h2 style="font-size:15px;margin:0 0 12px;color:#5a544e;">Edit Home Page Content</h2>
<div class="dash-links">
  <a href="homepage.php?tab=header">Header &amp; Nav</a>
  <a href="homepage.php?tab=hero">Hero Image</a>
  <a href="homepage.php?tab=intro">Intro</a>
  <a href="homepage.php?tab=spaces">Three Spaces</a>
  <a href="homepage.php?tab=door">The Door</a>
  <a href="homepage.php?tab=mood">Every Mood</a>
  <a href="homepage.php?tab=whatson">What's On</a>
  <a href="homepage.php?tab=events">Private Events</a>
  <a href="homepage.php?tab=footer">Footer &amp; Nav</a>
  <a href="homepage.php?tab=brands">Brand Logos</a>
</div>

<h2 style="font-size:15px;margin:24px 0 12px;color:#5a544e;">Edit Restaurant &amp; Menus</h2>
<div class="dash-links">
  <a href="restaurant.php?tab=herovideo">Hero Video</a>
  <a href="restaurant.php?tab=hero">Hero &amp; Intro</a>
  <a href="restaurant.php?tab=food">The Food</a>
  <a href="restaurant.php?tab=menu">Menu</a>
  <a href="restaurant.php?tab=room">The Room</a>
  <a href="restaurant.php?tab=fridaysat">Friday &amp; Saturday</a>
  <a href="restaurant.php?tab=happyhour">Happy Hour</a>
  <a href="restaurant.php?tab=chef">The Chef</a>
  <a href="restaurant.php?tab=hours">Restaurant Hours</a>
  <a href="restaurant.php?tab=gallery">Gallery</a>
</div>

<h2 style="font-size:15px;margin:24px 0 12px;color:#5a544e;">Edit Below</h2>
<div class="dash-links">
  <a href="below.php?tab=herovideo">Hero Video</a>
  <a href="below.php?tab=intro">Intro</a>
  <a href="below.php?tab=cocktails">From Cocktails To Late Night</a>
  <a href="below.php?tab=music">Music</a>
  <a href="below.php?tab=guest">Guest Artists</a>
  <a href="below.php?tab=vip">VIP Tables</a>
  <a href="below.php?tab=hours">Below Hours</a>
  <a href="below.php?tab=faqs">FAQs</a>
</div>

<h2 style="font-size:15px;margin:24px 0 12px;color:#5a544e;">Edit What's On</h2>
<div class="dash-links">
  <a href="whatson.php?tab=herovideo">Hero Video</a>
  <a href="whatson.php?tab=intro">Intro</a>
  <a href="whatson.php?tab=happyhour">Happy Hour</a>
  <a href="whatson.php?tab=friday">Friday &amp; Saturday Dinner Party</a>
  <a href="whatson.php?tab=brunch">Weekend Bottomless Brunch</a>
  <a href="whatson.php?tab=faqs">FAQs</a>
</div>

<h2 style="font-size:15px;margin:24px 0 12px;color:#5a544e;">Edit Brunch</h2>
<div class="dash-links">
  <a href="brunch.php?tab=herovideo">Hero Video</a>
  <a href="brunch.php?tab=intro">Intro</a>
  <a href="brunch.php?tab=gallery">Gallery</a>
  <a href="brunch.php?tab=menu">Menu</a>
  <a href="brunch.php?tab=guest">Guest Artists</a>
</div>

<h2 style="font-size:15px;margin:24px 0 12px;color:#5a544e;">Edit Dinner Party</h2>
<div class="dash-links">
  <a href="dinnerparty.php?tab=herovideo">Hero Video</a>
  <a href="dinnerparty.php?tab=intro">Intro</a>
  <a href="dinnerparty.php?tab=gallery">Gallery</a>
  <a href="dinnerparty.php?tab=menu">Menu</a>
  <a href="dinnerparty.php?tab=guest">Guest Artists</a>
</div>

<h2 style="font-size:15px;margin:24px 0 12px;color:#5a544e;">Edit Private Events</h2>
<div class="dash-links">
  <a href="privateevents.php?tab=herovideo">Hero Video</a>
  <a href="privateevents.php?tab=intro">Intro</a>
  <a href="privateevents.php?tab=cards">Event Cards</a>
  <a href="privateevents.php?tab=final">Make The House Yours</a>
</div>

<h2 style="font-size:15px;margin:24px 0 12px;color:#5a544e;">Week's Line-Up</h2>
<div class="dash-links">
  <a href="lineup.php">Manage This Week's Line-Up</a>
</div>

<h2 style="font-size:15px;margin:24px 0 12px;color:#5a544e;">System</h2>
<div class="dash-links">
  <a href="section.php?s=website">Website Settings</a>
  <a href="users.php">Admin Users</a>
  <a href="subscribers.php">Subscribers</a>
  <a href="account.php">Account</a>
</div>

<p class="muted" style="margin-top:24px;">Welcome back. Use the sidebar or the links above to manage the site. All changes appear on the live site immediately.</p>
<?php include __DIR__ . '/layout_end.php'; ?>
