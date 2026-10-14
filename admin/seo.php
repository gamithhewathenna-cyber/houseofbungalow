<?php
require_once __DIR__ . '/auth.php';
require_role('content');

/** Turn free-typed text into a safe, lowercase, hyphenated URL slug. */
function slugify(string $v): string
{
    $v = strtolower(trim($v));
    $v = preg_replace('/[^a-z0-9]+/', '-', $v);
    return trim($v, '-');
}

$reserved = ['admin', 'assets', 'uploads', 'includes', 'config', 'seo-router'];

$error  = '';
$savedId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM seo_meta WHERE id = ?');
        $stmt->execute([$id]);
        $page = $stmt->fetch();

        if (!$page) {
            $error = 'That page could not be found.';
        } else {
            $title = trim($_POST['seo_title'] ?? '');
            $kp    = trim($_POST['seo_keyphrase'] ?? '');
            $desc  = trim($_POST['seo_description'] ?? '');
            $slug  = $page['page_key'] === 'index' ? '' : slugify($_POST['seo_slug'] ?? '');

            if ($slug !== '' && in_array($slug, $reserved, true)) {
                $error = '"' . $slug . '" is a reserved word and can\'t be used as a URL slug.';
            } else {
                // Check the new slug (or, if left blank, this page's own default key)
                // doesn't clash with any OTHER page's current URL.
                $effective = $slug !== '' ? $slug : $page['page_key'];
                $others = db()->prepare(
                    "SELECT page_label, page_key, seo_slug FROM seo_meta WHERE id != ?"
                );
                $others->execute([$id]);
                foreach ($others->fetchAll() as $o) {
                    $otherEffective = $o['seo_slug'] !== '' ? $o['seo_slug'] : $o['page_key'];
                    if ($otherEffective === $effective) {
                        $error = 'The URL "/' . $effective . '" is already used by "' . $o['page_label'] . '" - choose a different slug.';
                        break;
                    }
                }
            }

            if ($error === '') {
                $q = db()->prepare('UPDATE seo_meta SET seo_title=?, seo_keyphrase=?, seo_description=?, seo_slug=? WHERE id=?');
                $q->execute([$title, $kp, $desc, $slug, $id]);
                header('Location: seo.php?saved=1#page-' . $id); exit;
            }
        }
    }
}

$pages = db()->query('SELECT * FROM seo_meta ORDER BY id ASC')->fetchAll();

$page_title = 'SEO';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<p class="muted" style="margin-bottom:18px;">Set a custom title, focus keyphrase, meta description and URL for each page, and save it on its own. Leave a field blank to keep using that page's normal heading/intro text (or its default URL). Changes apply to the live site as soon as you save that page's card.</p>

<div class="dj-admin-list">
  <?php foreach ($pages as $p): ?>
    <form method="post" class="dj-admin-card no-media" id="page-<?= $p['id'] ?>">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $p['id'] ?>">
      <div class="dj-admin-fields">
        <div class="field-row" style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;">
          <strong style="font-size:14px;"><?= e($p['page_label']) ?></strong>
          <span class="muted" style="font-size:12px;"><?= e(site_origin() . url($p['page_file'])) ?></span>
        </div>
        <div class="field-grid">
          <label>SEO Title <span class="help">(site name is added automatically after this)</span>
            <input type="text" name="seo_title" value="<?= e($p['seo_title']) ?>" placeholder="<?= e($p['page_label']) ?>">
          </label>
          <label>SEO Keyphrase
            <input type="text" name="seo_keyphrase" value="<?= e($p['seo_keyphrase']) ?>" placeholder="e.g. restaurant surry hills">
          </label>
        </div>
        <div class="field-row">
          <label>Meta Description
            <textarea name="seo_description" rows="2" maxlength="320"><?= e($p['seo_description']) ?></textarea>
          </label>
        </div>
        <div class="field-row">
          <?php if ($p['page_key'] === 'index'): ?>
            <label>URL Slug <span class="help">(the homepage always stays at the site root)</span>
              <input type="text" value="/" disabled style="max-width:220px;">
            </label>
          <?php else: ?>
            <label>URL Slug <span class="help">(letters, numbers and hyphens only)</span>
              <div style="display:flex;align-items:center;gap:6px;max-width:320px;">
                <span class="muted">/</span>
                <input type="text" name="seo_slug" value="<?= e($p['seo_slug']) ?>" placeholder="<?= e($p['page_key']) ?>">
              </div>
            </label>
          <?php endif; ?>
        </div>
      </div>
      <div class="dj-admin-actions">
        <button class="btn btn-sm">Save</button>
      </div>
    </form>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/layout_end.php'; ?>
