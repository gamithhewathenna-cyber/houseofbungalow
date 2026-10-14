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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'save') {
        $pages = db()->query('SELECT * FROM seo_meta ORDER BY id ASC')->fetchAll();

        // Build the full proposed state first, so slugs can be checked for
        // clashes against each other before anything is written.
        $proposed = [];
        foreach ($pages as $p) {
            $id    = $p['id'];
            $title = trim($_POST['seo_title'][$id] ?? '');
            $kp    = trim($_POST['seo_keyphrase'][$id] ?? '');
            $desc  = trim($_POST['seo_description'][$id] ?? '');
            $slug  = $p['page_key'] === 'index' ? '' : slugify($_POST['seo_slug'][$id] ?? '');
            $proposed[$id] = compact('title', 'kp', 'desc', 'slug') + ['page_key' => $p['page_key'], 'page_label' => $p['page_label']];
        }

        // Validate slugs: not reserved, and no two pages sharing one
        // (comparing against the custom slug where set, else the page's
        // own default key, since that's the URL it would actually use).
        $effective = [];
        foreach ($proposed as $id => $p) {
            $effective[$id] = $p['slug'] !== '' ? $p['slug'] : $p['page_key'];
        }
        $seen = [];
        foreach ($effective as $id => $slug) {
            if ($proposed[$id]['slug'] !== '' && in_array($proposed[$id]['slug'], $reserved, true)) {
                $error = '"' . $proposed[$id]['slug'] . '" is a reserved word and can\'t be used as a URL slug (' . $proposed[$id]['page_label'] . ').';
                break;
            }
            if (isset($seen[$slug])) {
                $error = 'The URL "/' . $slug . '" is used by both "' . $seen[$slug] . '" and "' . $proposed[$id]['page_label'] . '" - give one of them a different slug.';
                break;
            }
            $seen[$slug] = $proposed[$id]['page_label'];
        }

        if ($error === '') {
            foreach ($proposed as $id => $p) {
                $q = db()->prepare('UPDATE seo_meta SET seo_title=?, seo_keyphrase=?, seo_description=?, seo_slug=? WHERE id=?');
                $q->execute([$p['title'], $p['kp'], $p['desc'], $p['slug'], $id]);
            }
            header('Location: seo.php?saved=1'); exit;
        }
    }
}

$pages = db()->query('SELECT * FROM seo_meta ORDER BY id ASC')->fetchAll();

$page_title = 'SEO';
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<p class="muted" style="margin-bottom:18px;">Set a custom title, focus keyphrase, meta description and URL for each page. Leave a field blank to keep using that page's normal heading/intro text (or its default URL). Changes apply to the live site as soon as you save.</p>

<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="save">
  <div class="dj-admin-list">
    <?php foreach ($pages as $p): ?>
      <div class="dj-admin-card no-media" style="grid-template-columns:1fr;">
        <div class="dj-admin-fields">
          <div class="field-row" style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;">
            <strong style="font-size:14px;"><?= e($p['page_label']) ?></strong>
            <span class="muted" style="font-size:12px;"><?= e(site_origin() . url($p['page_file'])) ?></span>
          </div>
          <div class="field-grid">
            <label>SEO Title <span class="help">(site name is added automatically after this)</span>
              <input type="text" name="seo_title[<?= $p['id'] ?>]" value="<?= e($p['seo_title']) ?>" placeholder="<?= e($p['page_label']) ?>">
            </label>
            <label>SEO Keyphrase
              <input type="text" name="seo_keyphrase[<?= $p['id'] ?>]" value="<?= e($p['seo_keyphrase']) ?>" placeholder="e.g. restaurant surry hills">
            </label>
          </div>
          <div class="field-row">
            <label>Meta Description
              <textarea name="seo_description[<?= $p['id'] ?>]" rows="2" maxlength="320"><?= e($p['seo_description']) ?></textarea>
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
                  <input type="text" name="seo_slug[<?= $p['id'] ?>]" value="<?= e($p['seo_slug']) ?>" placeholder="<?= e($p['page_key']) ?>">
                </div>
              </label>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="form-actions"><button class="btn">Save Changes</button></div>
</form>
<?php include __DIR__ . '/layout_end.php'; ?>
