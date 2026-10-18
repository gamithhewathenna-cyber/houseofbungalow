<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/email-template.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
    exit;
}

$fullName     = trim($_POST['full_name'] ?? '');
$organisation = trim($_POST['organisation'] ?? '');
$email        = trim($_POST['email'] ?? '');
$phone        = trim($_POST['phone'] ?? '');
$enquiryType  = trim($_POST['enquiry_type'] ?? '');
$message      = trim($_POST['message'] ?? '');

if ($fullName === '' || $organisation === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || $enquiryType === '' || $message === '') {
    echo json_encode(['ok' => false, 'error' => 'Please fill in all required fields with a valid email address.']);
    exit;
}

/**
 * Strip CR/LF from a value before it goes into a mail header, so
 * submitted text can't be used to inject extra headers.
 */
function press_header_safe(string $v): string
{
    return trim(str_replace(["\r", "\n"], '', $v));
}

/**
 * A plausible "From" address for mail sent by this server - uses the
 * requested domain so it lines up with where the site is hosted.
 */
function press_mail_from(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host = preg_replace('/:\d+$/', '', $host);
    $host = preg_replace('/^www\./i', '', $host);
    return 'no-reply@' . $host;
}

/**
 * Notify the House (if a "send enquiries to" address is configured) with
 * the full media enquiry details, replying straight to the sender.
 */
function send_press_notification(array $d): void
{
    $to = trim(setting('press_to_email'));
    if ($to === '') {
        return;
    }
    $body = email_p('A new media enquiry was submitted on the Press & Media page.')
        . email_details_table([
            'Name'              => $d['full_name'],
            'Publication / Org' => $d['organisation'],
            'Email'             => $d['email'],
            'Phone'             => $d['phone'],
            'Nature of enquiry' => $d['enquiry_type'],
        ]);
    $body .= '<p style="margin:0 0 6px;font-size:13px;color:#9a9089;">Enquiry</p>' . email_p($d['message']);
    $html = email_layout('New Media Enquiry', $body, [
        'label' => 'Reply to ' . $d['full_name'],
        'url'   => 'mailto:' . $d['email'],
    ]);

    $headers = [
        'From: ' . SITE_NAME . ' <' . press_mail_from() . '>',
        'Reply-To: ' . press_header_safe($d['full_name']) . ' <' . press_header_safe($d['email']) . '>',
        'Content-Type: text/html; charset=UTF-8',
    ];
    $cc = trim(setting('press_cc_email'));
    if ($cc !== '') {
        $headers[] = 'Cc: ' . $cc;
    }
    app_mail($to, 'New Media Enquiry - ' . press_header_safe($d['full_name']), $html, $headers);
}

try {
    $stmt = db()->prepare(
        'INSERT INTO press_enquiries (full_name, organisation, email, phone, enquiry_type, message)
         VALUES (?,?,?,?,?,?)'
    );
    $stmt->execute([$fullName, $organisation, $email, $phone, $enquiryType, $message]);

    send_press_notification([
        'full_name' => $fullName, 'organisation' => $organisation, 'email' => $email,
        'phone' => $phone, 'enquiry_type' => $enquiryType, 'message' => $message,
    ]);

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'Something went wrong. Please try again.']);
}
