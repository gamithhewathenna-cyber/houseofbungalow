<?php
require_once __DIR__ . '/auth.php';
require_role('content');
require_once __DIR__ . '/helpers.php';

$fieldGroups = [
    'herovideo' => ['wo_hero_video'],
    'intro'     => ['wo_heading', 'wo_subheading', 'wo_lede', 'wo_tag2_label', 'wo_tag2_url', 'wo_tag3_label', 'wo_tag3_url'],
    'happyhour' => ['wo_happyhour_eyebrow', 'wo_happyhour_heading', 'wo_happyhour_p1', 'wo_happyhour_p2', 'wo_happyhour_btn_label', 'wo_happyhour_btn_url', 'wo_happyhour_image'],
    'friday'    => ['wo_friday_eyebrow', 'wo_friday_heading', 'wo_friday_p1', 'wo_friday_p2', 'wo_friday_btn_label', 'wo_friday_btn_url', 'wo_friday_image'],
    'brunch'    => ['wo_brunch_eyebrow', 'wo_brunch_heading', 'wo_brunch_hours', 'wo_brunch_p1', 'wo_brunch_btn_label', 'wo_brunch_btn_url', 'wo_brunch_image'],
    'livelate'  => ['wo_livelate_heading', 'wo_livelate_p1', 'wo_livelate_btn_label', 'wo_livelate_btn_url'],
];
// Reorderable content sections (admin > Section Order, admins only) - Hero
// and Intro always stay fixed at the top, so they're left out of this list.
$sectionOrderKeys = ['wo_features', 'wo_happyhour', 'wo_friday', 'wo_brunch', 'wo_livelate', 'wo_lineup', 'wo_faq'];
$visibilitySections = [
    'wo_hero'      => 'Hero',
    'wo_intro'     => 'Intro',
    'wo_features'  => 'Featured Event Sections',
    'wo_happyhour' => 'Happy Hour',
    'wo_friday'    => 'Friday & Saturday Dinner Party',
    'wo_brunch'    => 'Weekend Bottomless Brunch',
    'wo_livelate'  => 'Guest Artists',
    'wo_lineup'    => "See This Week's Line-Up",
    'wo_faq'       => 'FAQs',
];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            // ---- Section visibility ----
            if ($action === 'save_visibility') {
                foreach ($visibilitySections as $vkey => $vlabel) {
                    save_setting($vkey . '_visible', isset($_POST['vis_' . $vkey]) ? '1' : '0');
                }
                header('Location: whatson.php?tab=visibility&saved=1'); exit;
            }

            // ---- Section order (admins only) ----
            if ($action === 'save_section_order') {
                if (!admin_can('admin')) {
                    $error = 'Only full Admins can reorder sections.';
                } else {
                    $order = array_values(array_intersect(array_filter(array_map('trim', explode(',', $_POST['order'] ?? ''))), $sectionOrderKeys));
                    save_setting('whatson_section_order', implode(',', $order));
                    header('Location: whatson.php?tab=order&saved=1'); exit;
                }
            }

            if ($action === 'save_section') {
                $group = $_POST['group'] ?? '';
                if (isset($fieldGroups[$group])) {
                    $keys = $fieldGroups[$group];
                    $placeholders = implode(',', array_fill(0, count($keys), '?'));
                    $stmt = db()->prepare("SELECT * FROM settings WHERE skey IN ($placeholders)");
                    $stmt->execute($keys);
                    foreach ($stmt->fetchAll() as $f) {
                        $key = $f['skey']; $type = $f['field_type'];
                        if ($type === 'image') {
                            $uploaded = handle_upload('file_' . $key);
                            if ($uploaded !== null) {
                                save_setting($key, $uploaded);
                            }
                        } elseif ($type === 'video') {
                            $uploaded = handle_video_upload('file_' . $key);
                            if ($uploaded !== null) {
                                save_setting($key, $uploaded);
                            } elseif (isset($_POST['remove_' . $key])) {
                                save_setting($key, '');
                            }
                        } else {
                            if (array_key_exists($key, $_POST)) {
                                save_setting($key, $_POST[$key]);
                            }
                        }
                    }
                }
                header('Location: whatson.php?tab=' . urlencode($group) . '&saved=1'); exit;
            }

            if ($action === 'save_features') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    $fields = ['title=?', 'subtitle=?', 'body=?', 'link_url=?', 'link_url2=?', 'sort=?'];
                    $params = [trim($title), trim($_POST['subtitle'][$id] ?? ''), trim($_POST['desc'][$id] ?? ''), trim($_POST['link'][$id] ?? ''), trim($_POST['link2'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0)];
                    if ($img !== null) {
                        $fields[] = 'image=?';
                        $params[] = $img;
                    }
                    $params[] = $id;
                    $q = db()->prepare('UPDATE blocks SET ' . implode(',', $fields) . ' WHERE id=? AND block_type="wo_feature"');
                    $q->execute($params);
                }
                header('Location: whatson.php?tab=features&saved=1'); exit;
            }
            if ($action === 'add_feature') {
                $img = handle_upload('new_file');
                $q = db()->prepare('INSERT INTO blocks (block_type,title,subtitle,body,link_url,link_url2,image,sort,active) VALUES ("wo_feature",?,?,?,?,?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New Section'), trim($_POST['new_subtitle'] ?? ''), trim($_POST['new_desc'] ?? ''), trim($_POST['new_link'] ?? '#'), trim($_POST['new_link2'] ?? ''), $img ?? '', (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: whatson.php?tab=features&saved=1'); exit;
            }
            if ($action === 'delete_feature') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="wo_feature"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: whatson.php?tab=features&saved=1'); exit;
            }

            // ---- Live & Late cards ----
            if ($action === 'save_livelate_cards') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id  = (int) $id;
                    $img = handle_upload('file_' . $id);
                    $fields = ['title=?', 'subtitle=?', 'body=?', 'link_url=?', 'sort=?'];
                    $params = [trim($title), trim($_POST['day'][$id] ?? ''), trim($_POST['time'][$id] ?? ''), trim($_POST['artist'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0)];
                    if ($img !== null) {
                        $fields[] = 'image=?';
                        $params[] = $img;
                    }
                    $params[] = $id;
                    $q = db()->prepare('UPDATE blocks SET ' . implode(',', $fields) . ' WHERE id=? AND block_type="wo_livelate_card"');
                    $q->execute($params);
                }
                header('Location: whatson.php?tab=livelatecards&saved=1'); exit;
            }
            if ($action === 'add_livelate_card') {
                $img = handle_upload('new_file');
                $q = db()->prepare('INSERT INTO blocks (block_type,title,subtitle,body,link_url,image,sort,active) VALUES ("wo_livelate_card",?,?,?,?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New Session'), trim($_POST['new_day'] ?? ''), trim($_POST['new_time'] ?? ''), trim($_POST['new_artist'] ?? ''), $img ?? '', (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: whatson.php?tab=livelatecards&saved=1'); exit;
            }
            if ($action === 'delete_livelate_card') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="wo_livelate_card"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: whatson.php?tab=livelatecards&saved=1'); exit;
            }

            if ($action === 'save_faqs') {
                foreach (($_POST['title'] ?? []) as $id => $title) {
                    $id = (int) $id;
                    $q = db()->prepare('UPDATE blocks SET title=?, body=?, sort=? WHERE id=? AND block_type="whatson_faq"');
                    $q->execute([trim($title), trim($_POST['desc'][$id] ?? ''), (int) ($_POST['sort'][$id] ?? 0), $id]);
                }
                header('Location: whatson.php?tab=faqs&saved=1'); exit;
            }
            if ($action === 'add_faq') {
                $q = db()->prepare('INSERT INTO blocks (block_type,title,body,sort,active) VALUES ("whatson_faq",?,?,?,1)');
                $q->execute([trim($_POST['new_title'] ?? 'New Question'), trim($_POST['new_desc'] ?? ''), (int) ($_POST['new_sort'] ?? 99)]);
                header('Location: whatson.php?tab=faqs&saved=1'); exit;
            }
            if ($action === 'delete_faq') {
                $q = db()->prepare('DELETE FROM blocks WHERE id=? AND block_type="whatson_faq"');
                $q->execute([(int) ($_POST['id'] ?? 0)]);
                header('Location: whatson.php?tab=faqs&saved=1'); exit;
            }
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$allRows = db()->query("SELECT * FROM settings WHERE section='whatson_page'")->fetchAll();
$bySkey = [];
foreach ($allRows as $r) {
    $bySkey[$r['skey']] = $r;
}
$faqs = db()->query("SELECT * FROM blocks WHERE block_type='whatson_faq' ORDER BY sort ASC, id ASC")->fetchAll();
$features = db()->query("SELECT * FROM blocks WHERE block_type='wo_feature' ORDER BY sort ASC, id ASC")->fetchAll();
$livelateCards = db()->query("SELECT * FROM blocks WHERE block_type='wo_livelate_card' ORDER BY sort ASC, id ASC")->fetchAll();

$tabs = [
    'herovideo' => 'Hero Video',
    'intro'     => 'Intro',
    'features'  => 'Featured Event Sections',
    'happyhour' => 'Happy Hour',
    'friday'    => 'Friday & Saturday Dinner Party',
    'brunch'    => 'Weekend Bottomless Brunch',
    'livelate'  => 'Live & Late',
    'livelatecards' => 'Guest Artists',
    'faqs'      => 'FAQs',
    'visibility' => 'Section Visibility',
];
if (admin_can('admin')) {
    $tabs['order'] = 'Section Order';
}
$activeTab = $_GET['tab'] ?? 'herovideo';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'herovideo';
}
$currentSectionOrder = section_order('whatson', $sectionOrderKeys);

function render_wo_field(array $f): void
{
    $key = $f['skey']; $val = $f['svalue']; $type = $f['field_type'];
    ?>
    <div class="field-row">
      <label>
        <?= e($f['label'] ?: $key) ?>
        <?php if ($type === 'textarea'): ?>
          <textarea name="<?= e($key) ?>"><?= e($val) ?></textarea>
        <?php elseif ($type === 'image'): ?>
          <?php if ($val): ?>
            <div class="thumb-preview"><img src="<?= e(asset($val)) ?>" alt=""></div>
          <?php endif; ?>
          <input type="file" name="file_<?= e($key) ?>" accept="image/*" data-crop-ratio="<?= e(crop_ratio_for_key($key)) ?>">
          <span class="help">Current: <code><?= e($val ?: 'none') ?></code>. Leave empty to keep it.</span>
        <?php elseif ($type === 'video'): ?>
          <?php if ($val): ?>
            <div class="thumb-preview">
              <video src="<?= e(asset($val)) ?>" style="max-height:160px;max-width:100%;" controls muted></video>
            </div>
            <span class="help" style="display:block;margin:6px 0;">
              <input type="checkbox" name="remove_<?= e($key) ?>" value="1"> Remove this video (revert to the static hero image)
            </span>
          <?php endif; ?>
          <input type="file" name="file_<?= e($key) ?>" accept="video/mp4,video/webm,video/quicktime">
          <span class="help"><strong>MP4 (H.264) recommended</strong> - WEBM will not play in Safari. Ideally 1280×720 (720p), short and compressed (max 60&nbsp;MB) so it loads quickly. Leave empty to keep the current video.</span>
        <?php else: ?>
          <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
        <?php endif; ?>
      </label>
    </div>
    <?php
}

$csrf = csrf_token();
$page_title = "What's On";
include __DIR__ . '/layout.php';
?>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<div class="tab-nav">
  <?php foreach ($tabs as $key => $label): ?>
    <button type="button" class="tab-btn<?= $activeTab === $key ? ' active' : '' ?>" data-tab="<?= e($key) ?>"><?= e($label) ?></button>
  <?php endforeach; ?>
</div>

<?php foreach ($fieldGroups as $group => $keys): ?>
  <div class="tab-panel settings-group<?= $activeTab === $group ? ' active' : '' ?>" data-tab="<?= e($group) ?>">
    <h3><?= e($tabs[$group]) ?></h3>
    <?php if ($group === 'happyhour' || $group === 'friday' || $group === 'brunch'): ?>
      <p class="group-help">"INFORMATION" is the small bold label shown under the title - leave it as-is or customise it.</p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="action" value="save_section">
      <input type="hidden" name="group" value="<?= e($group) ?>">
      <?php foreach ($keys as $key): if (!isset($bySkey[$key])) continue; render_wo_field($bySkey[$key]); endforeach; ?>
      <div class="form-actions"><button type="submit" class="btn">Save Changes</button></div>
    </form>
  </div>
<?php endforeach; ?>

<!-- Featured Event Sections tab -->
<div class="tab-panel settings-group<?= $activeTab === 'features' ? ' active' : '' ?>" data-tab="features">
  <h3>Featured Event Sections</h3>
  <p class="group-help">Shown as image + text sections on the What's On page, between the intro and "Happy Hour". Add as many as you like, in any order - each alternates image-left/image-right automatically. Leave the button label empty to hide the button on that section.</p>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_features">
    <div class="dj-admin-list">
      <?php foreach ($features as $feature): ?>
        <div class="dj-admin-card">
          <div class="dj-admin-media">
            <div class="thumb-preview<?= $feature['image'] ? '' : ' empty' ?>">
              <?php if ($feature['image']): ?><img src="<?= e(asset($feature['image'])) ?>" alt=""><?php else: ?>No photo yet<?php endif; ?>
            </div>
            <input type="file" name="file_<?= $feature['id'] ?>" accept="image/*" data-crop-ratio="3:2">
            <span class="help">Replace photo</span>
          </div>
          <div class="dj-admin-fields">
            <div class="field-grid">
              <label>Eyebrow label (optional)
                <input type="text" name="subtitle[<?= $feature['id'] ?>]" value="<?= e($feature['subtitle']) ?>">
              </label>
              <label>Heading
                <input type="text" name="title[<?= $feature['id'] ?>]" value="<?= e($feature['title']) ?>">
              </label>
            </div>
            <div class="field-row">
              <label>Paragraph text (leave a blank line between paragraphs)
                <textarea name="desc[<?= $feature['id'] ?>]" rows="4"><?= e($feature['body']) ?></textarea>
              </label>
            </div>
            <div class="field-grid">
              <label>Button label (leave empty to hide the button)
                <input type="text" name="link2[<?= $feature['id'] ?>]" value="<?= e($feature['link_url2']) ?>">
              </label>
              <label>Button URL
                <input type="text" name="link[<?= $feature['id'] ?>]" value="<?= e($feature['link_url']) ?>">
              </label>
            </div>
            <div class="field-row">
              <label>Order
                <input type="text" name="sort[<?= $feature['id'] ?>]" value="<?= (int)$feature['sort'] ?>" style="max-width:100px;">
              </label>
            </div>
          </div>
          <div class="dj-admin-actions">
            <button form="delfeature<?= $feature['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this section?')">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($features as $feature): ?>
    <form id="delfeature<?= $feature['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_feature"><input type="hidden" name="id" value="<?= $feature['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h3 style="margin-top:30px;">Add a Section</h3>
  <form method="post" enctype="multipart/form-data" class="dj-admin-card dj-admin-add">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_feature">
    <div class="dj-admin-media">
      <div class="thumb-preview empty">No photo yet</div>
      <input type="file" name="new_file" accept="image/*" data-crop-ratio="3:2">
      <span class="help">Upload photo</span>
    </div>
    <div class="dj-admin-fields">
      <div class="field-grid">
        <label>Eyebrow label (optional)
          <input type="text" name="new_subtitle" placeholder="e.g. Melbourne Cup">
        </label>
        <label>Heading
          <input type="text" name="new_title" placeholder="e.g. Melbourne Cup">
        </label>
      </div>
      <div class="field-row">
        <label>Paragraph text (leave a blank line between paragraphs)
          <textarea name="new_desc" rows="4"></textarea>
        </label>
      </div>
      <div class="field-grid">
        <label>Button label (leave empty to hide the button)
          <input type="text" name="new_link2" placeholder="e.g. DISCOVER MORE">
        </label>
        <label>Button URL
          <input type="text" name="new_link" placeholder="#">
        </label>
      </div>
      <div class="field-row">
        <label>Order
          <input type="text" name="new_sort" value="99" style="max-width:100px;">
        </label>
      </div>
    </div>
    <div class="dj-admin-actions">
      <button class="btn btn-sm">Add Section</button>
    </div>
  </form>
</div>

<!-- Live & Late Cards tab (labeled "Guest Artists" in the sidebar) -->
<div class="tab-panel settings-group<?= $activeTab === 'livelatecards' ? ' active' : '' ?>" data-tab="livelatecards">
  <h3>Guest Artists</h3>
  <p class="group-help">The session cards shown in the "Live & Late" section (e.g. Saturday "DJ Sessions" / Sunday "Sunday Sessions"). This is its own separate list - not connected to "Week's Line-Up" in the sidebar. Leave the photo empty to show an "Artist Photo" placeholder.</p>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_livelate_cards">
    <div class="dj-admin-list">
      <?php foreach ($livelateCards as $card): ?>
        <div class="dj-admin-card">
          <div class="dj-admin-media">
            <div class="thumb-preview<?= $card['image'] ? '' : ' empty' ?>">
              <?php if ($card['image']): ?><img src="<?= e(asset($card['image'])) ?>" alt=""><?php else: ?>No photo yet<?php endif; ?>
            </div>
            <input type="file" name="file_<?= $card['id'] ?>" accept="image/*" data-crop-ratio="4:3">
            <span class="help">Replace photo</span>
          </div>
          <div class="dj-admin-fields">
            <div class="field-grid">
              <label>Day (e.g. SATURDAY)
                <input type="text" name="day[<?= $card['id'] ?>]" value="<?= e($card['subtitle']) ?>">
              </label>
              <label>Session Title (e.g. DJ Sessions)
                <input type="text" name="title[<?= $card['id'] ?>]" value="<?= e($card['title']) ?>">
              </label>
            </div>
            <div class="field-grid">
              <label>Time (e.g. 12:00 - 3:00 PM)
                <input type="text" name="time[<?= $card['id'] ?>]" value="<?= e($card['body']) ?>">
              </label>
              <label>Artist Name
                <input type="text" name="artist[<?= $card['id'] ?>]" value="<?= e($card['link_url']) ?>">
              </label>
            </div>
            <div class="field-row">
              <label>Order
                <input type="text" name="sort[<?= $card['id'] ?>]" value="<?= (int)$card['sort'] ?>" style="max-width:100px;">
              </label>
            </div>
          </div>
          <div class="dj-admin-actions">
            <button form="dellivelatecard<?= $card['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this card?')">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($livelateCards as $card): ?>
    <form id="dellivelatecard<?= $card['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_livelate_card"><input type="hidden" name="id" value="<?= $card['id'] ?>">
    </form>
  <?php endforeach; ?>

  <h3 style="margin-top:30px;">Add a Card</h3>
  <form method="post" enctype="multipart/form-data" class="dj-admin-card dj-admin-add">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_livelate_card">
    <div class="dj-admin-media">
      <div class="thumb-preview empty">No photo yet</div>
      <input type="file" name="new_file" accept="image/*" data-crop-ratio="4:3">
      <span class="help">Upload photo</span>
    </div>
    <div class="dj-admin-fields">
      <div class="field-grid">
        <label>Day (e.g. SATURDAY)
          <input type="text" name="new_day" placeholder="e.g. SATURDAY">
        </label>
        <label>Session Title (e.g. DJ Sessions)
          <input type="text" name="new_title" placeholder="e.g. DJ Sessions">
        </label>
      </div>
      <div class="field-grid">
        <label>Time (e.g. 12:00 - 3:00 PM)
          <input type="text" name="new_time" placeholder="e.g. 12:00 - 3:00 PM">
        </label>
        <label>Artist Name
          <input type="text" name="new_artist" placeholder="Artist Name">
        </label>
      </div>
      <div class="field-row">
        <label>Order
          <input type="text" name="new_sort" value="99" style="max-width:100px;">
        </label>
      </div>
    </div>
    <div class="dj-admin-actions">
      <button class="btn btn-sm">Add Card</button>
    </div>
  </form>
</div>

<!-- FAQs tab -->
<div class="tab-panel settings-group<?= $activeTab === 'faqs' ? ' active' : '' ?>" data-tab="faqs">
  <h3>FAQs</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_faqs">
    <table>
      <tr><th>Question</th><th>Answer</th><th style="width:70px;">Order</th><th style="width:80px;"></th></tr>
      <?php foreach ($faqs as $f): ?>
        <tr>
          <td><input type="text" name="title[<?= $f['id'] ?>]" value="<?= e($f['title']) ?>"></td>
          <td><input type="text" name="desc[<?= $f['id'] ?>]" value="<?= e($f['body']) ?>"></td>
          <td><input type="text" name="sort[<?= $f['id'] ?>]" value="<?= (int)$f['sort'] ?>" style="width:56px;"></td>
          <td><button form="delfaq<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this question?')">Delete</button></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="form-actions"><button class="btn">Save Changes</button></div>
  </form>
  <?php foreach ($faqs as $f): ?>
    <form id="delfaq<?= $f['id'] ?>" method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete_faq"><input type="hidden" name="id" value="<?= $f['id'] ?>">
    </form>
  <?php endforeach; ?>
  <h3 style="margin-top:24px;font-size:14px;">Add a Question</h3>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="add_faq">
    <div class="row-actions">
      <input type="text" name="new_title" placeholder="Question" style="max-width:260px;">
      <input type="text" name="new_desc" placeholder="Answer" style="max-width:320px;">
      <input type="text" name="new_sort" placeholder="Order" value="99" style="width:70px;">
      <button class="btn btn-sm">Add</button>
    </div>
  </form>
</div>

<!-- Section Visibility tab -->
<div class="tab-panel settings-group<?= $activeTab === 'visibility' ? ' active' : '' ?>" data-tab="visibility">
  <h3>Section Visibility</h3>
  <p class="group-help">Untick a section to hide it from the live What's On page without deleting its content - tick it again any time to bring it back.</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_visibility">
    <div class="visibility-list">
      <?php foreach ($visibilitySections as $vkey => $vlabel): ?>
        <label class="visibility-row">
          <input type="checkbox" name="vis_<?= e($vkey) ?>" value="1"<?= section_visible($vkey) ? ' checked' : '' ?>>
          <?= e($vlabel) ?>
        </label>
      <?php endforeach; ?>
    </div>
    <div class="form-actions"><button class="btn">Save Visibility</button></div>
  </form>
</div>

<?php if (admin_can('admin')): ?>
<!-- Section Order tab (admins only) -->
<div class="tab-panel settings-group<?= $activeTab === 'order' ? ' active' : '' ?>" data-tab="order">
  <h3>Section Order</h3>
  <p class="group-help">Drag sections up or down to change where they appear on the live page, below the Hero and Intro (which always stay at the top). Changes apply as soon as you save.</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save_section_order">
    <input type="hidden" name="order" id="sectionOrderInput" value="<?= e(implode(',', $currentSectionOrder)) ?>">
    <ul class="section-order-list" id="sectionOrderList">
      <?php foreach ($currentSectionOrder as $key): ?>
        <li class="section-order-row" draggable="true" data-key="<?= e($key) ?>">
          <span class="drag-handle">⠿</span>
          <span><?= e($visibilitySections[$key] ?? $key) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="form-actions"><button class="btn">Save Order</button></div>
  </form>
</div>
<script>
(function () {
  var list = document.getElementById('sectionOrderList');
  var input = document.getElementById('sectionOrderInput');
  if (!list || !input) return;
  var dragEl = null;

  function updateInput() {
    var keys = Array.prototype.map.call(list.querySelectorAll('.section-order-row'), function (r) { return r.dataset.key; });
    input.value = keys.join(',');
  }
  function afterElement(y) {
    var rows = Array.prototype.slice.call(list.querySelectorAll('.section-order-row:not(.dragging)'));
    return rows.reduce(function (closest, child) {
      var box = child.getBoundingClientRect();
      var offset = y - box.top - box.height / 2;
      if (offset < 0 && offset > closest.offset) {
        return { offset: offset, element: child };
      }
      return closest;
    }, { offset: -Infinity, element: null }).element;
  }

  list.querySelectorAll('.section-order-row').forEach(function (row) {
    row.addEventListener('dragstart', function (e) {
      dragEl = row;
      row.classList.add('dragging');
      e.dataTransfer.setData('text/plain', '');
    });
    row.addEventListener('dragend', function () {
      row.classList.remove('dragging');
      dragEl = null;
      updateInput();
    });
  });
  list.addEventListener('dragover', function (e) {
    e.preventDefault();
    if (!dragEl) return;
    var after = afterElement(e.clientY);
    if (after == null) {
      list.appendChild(dragEl);
    } else {
      list.insertBefore(dragEl, after);
    }
  });
})();
</script>
<?php endif; ?>

<p class="muted" style="margin-top:24px;">The "See This Week's Line-Up" section on this page is shared with the Below page - manage its content from <a href="lineup.php">Week's Line-Up</a> in the sidebar; its visibility toggle above is independent of Below's.</p>

<script>
document.querySelectorAll('.tab-btn').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });
    btn.classList.add('active');
    var panel = document.querySelector('.tab-panel[data-tab="' + btn.dataset.tab + '"]');
    if (panel) panel.classList.add('active');
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, '', '?tab=' + btn.dataset.tab);
    }
  });
});
</script>

<?php include __DIR__ . '/layout_end.php'; ?>
