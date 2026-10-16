<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/email-template.php';

$redirect = url('index.php') . '#stay';

function send_subscribe_notification(string $email): void
{
    $to = trim(setting('subscribe_notify_to_email'));
    if ($to === '') {
        return;
    }
    $body = email_p('A new subscriber joined the mailing list from the footer newsletter form.')
        . email_details_table(['Email' => $email]);
    $html = email_layout('New Newsletter Subscriber', $body);
    $headers = [
        'From: ' . SITE_NAME . ' <no-reply@' . preg_replace('/:\d+$/', '', preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST'] ?? 'localhost')) . '>',
        'Content-Type: text/html; charset=UTF-8',
    ];
    $cc = trim(setting('subscribe_notify_cc_email'));
    if ($cc !== '') {
        $headers[] = 'Cc: ' . $cc;
    }
    app_mail($to, 'New Newsletter Subscriber - ' . $email, $html, $headers);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . url('index.php') . '?sub=err#stay');
        exit;
    }
    try {
        $stmt = db()->prepare('INSERT INTO subscribers (email) VALUES (?)');
        $stmt->execute([$email]);
        send_subscribe_notification($email);
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
