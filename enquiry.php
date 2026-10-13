<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$email    = trim($_POST['email'] ?? '');
$eventType = trim($_POST['event_type'] ?? '');
$space     = trim($_POST['preferred_space'] ?? '');
$date      = trim($_POST['preferred_date'] ?? '');
$time      = trim($_POST['preferred_time'] ?? '');
$guests    = trim($_POST['guests'] ?? '');
$budget    = trim($_POST['budget'] ?? '');
$message   = trim($_POST['message'] ?? '');
$source    = trim($_POST['source'] ?? '');

if ($fullName === '' || $phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || $eventType === '' || $space === '' || $date === '' || $guests === '') {
    echo json_encode(['ok' => false, 'error' => 'Please fill in all required fields with a valid email address.']);
    exit;
}

try {
    $stmt = db()->prepare(
        'INSERT INTO enquiries (full_name, phone, email, event_type, preferred_space, preferred_date, preferred_time, guests, budget, message, source)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([$fullName, $phone, $email, $eventType, $space, $date, $time, $guests, $budget, $message, $source]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'Something went wrong. Please try again.']);
}
