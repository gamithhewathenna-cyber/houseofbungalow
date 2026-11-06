<?php
require_once __DIR__ . '/auth.php';
require_role('admin');
header('Content-Type: application/json');

if (!csrf_check()) {
    echo json_encode(['ok' => false, 'error' => 'Session expired. Please refresh the page and try again.']);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$stmt = db()->prepare('SELECT page_key FROM seo_meta WHERE id = ?');
$stmt->execute([$id]);
$key = $stmt->fetchColumn();

if ($key === false) {
    echo json_encode(['ok' => false, 'error' => 'That page could not be found.']);
    exit;
}
if ($key === 'index') {
    echo json_encode(['ok' => false, 'error' => "The homepage can't be hidden."]);
    exit;
}

$visible = !empty($_POST['page_visible']) ? 1 : 0;
$q = db()->prepare('UPDATE seo_meta SET page_visible = ? WHERE id = ?');
$q->execute([$visible, $id]);

echo json_encode(['ok' => true, 'visible' => (bool) $visible]);
