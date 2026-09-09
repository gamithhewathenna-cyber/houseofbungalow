<?php
require_once __DIR__ . '/includes/functions.php';

$redirect = url('index.php') . '#stay';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . url('index.php') . '?sub=err#stay');
        exit;
    }
    try {
        $stmt = db()->prepare('INSERT INTO subscribers (email) VALUES (?)');
        $stmt->execute([$email]);
        header('Location: ' . url('index.php') . '?sub=ok#stay');
    } catch (PDOException $e) {
        // Duplicate email (unique key) or other error
        if ($e->getCode() === '23000') {
            header('Location: ' . url('index.php') . '?sub=dupe#stay');
        } else {
            header('Location: ' . url('index.php') . '?sub=err#stay');
        }
    }
    exit;
}
header('Location: ' . url('index.php'));
exit;
