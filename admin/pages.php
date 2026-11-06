<?php
require_once __DIR__ . '/auth.php';
require_role('content');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT page_key FROM seo_meta WHERE id = ?');
        $stmt->execute([$id]);
        $key = $stmt->fetchColumn();
        if ($key === 'index') {
            $error = 'The homepage can\'t be hidden.';
        } elseif ($key !== false) {
            $visible = isset($_POST['page_visible']) ? 1 : 0;
            $q = db()->prepare('UPDATE seo_meta SET page_visible = ? WHERE id = ?');
            $q->execute([$visible, $id]);
            header('Location: pages.php?saved=1'); exit;
        }
    }
}

$pages = db()->query('SELECT * FROM seo_meta ORDER BY id ASC')->fetchAll();

$page_title = 'Main Pages';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<p class="muted" style="margin-bottom:18px;">Show or hide each page on the live site. A hidden page is removed from the site menu and footer, and shows a "Page not found" message to customers - you can still open and edit it here while it's hidden, and preview it on the live site while logged in.</p>

<div class="dj-admin-list">
  <?php foreach ($pages as $p): $isHome = $p['page_key'] === 'index'; $visible = (int) $p['page_visible'] === 1; ?>
    <form method="post" class="toggle-row" style="background:#fff;border:1px solid var(--line);border-radius:8px;padding:16px 18px;">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="toggle">
      <input type="hidden" name="id" value="<?= $p['id'] ?>">
      <div class="toggle-copy">
        <strong><?= e($p['page_label']) ?></strong>
        <span><?= e(site_origin() . url($p['page_file'])) ?></span>
      </div>
      <?php if ($isHome): ?>
        <span class="muted" style="font-size:12px;">Always visible</span>
      <?php else: ?>
        <label class="toggle">
          <input type="checkbox" name="page_visible" value="1" onchange="this.form.submit()" <?= $visible ? 'checked' : '' ?>>
          <span class="toggle-slider"></span>
        </label>
      <?php endif; ?>
    </form>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/layout_end.php'; ?>
