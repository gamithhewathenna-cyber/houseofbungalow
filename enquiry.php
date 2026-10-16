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

$fullName = trim($_POST['full_name'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$email    = trim($_POST['email'] ?? '');
$company   = trim($_POST['company'] ?? '');
$eventType = trim($_POST['event_type'] ?? '');
$space     = trim($_POST['preferred_space'] ?? '');
$date      = trim($_POST['preferred_date'] ?? '');
$time      = trim($_POST['preferred_time'] ?? '');
$guests    = trim($_POST['guests'] ?? '');
$message   = trim($_POST['message'] ?? '');
$source    = trim($_POST['source'] ?? '');

if ($fullName === '' || $phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || $eventType === '' || $space === '' || $date === '' || $guests === '') {
    echo json_encode(['ok' => false, 'error' => 'Please fill in all required fields with a valid email address.']);
    exit;
}

/**
 * Strip CR/LF from a value before it goes into a mail header, so
 * submitted text can't be used to inject extra headers.
 */
function header_safe(string $v): string
{
    return trim(str_replace(["\r", "\n"], '', $v));
}

/**
 * A plausible "From" address for mail sent by this server - uses the
 * requested domain so it lines up with where the site is hosted.
 */
function enquiry_mail_from(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host = preg_replace('/:\d+$/', '', $host);
    $host = preg_replace('/^www\./i', '', $host);
    return 'no-reply@' . $host;
}

/**
 * Notify the House (if a "send enquiries to" address is configured) with
 * the full enquiry details, Cc'd to any configured address, replying
 * straight to the person who submitted it.
 */
function send_enquiry_notification(array $d): void
{
    $to = trim(setting('pe_enquiry_to_email'));
    if ($to === '') {
        return;
    }
    $body = email_p('A new enquiry was submitted on the Private Events page.')
        . email_details_table([
            'Name'              => $d['full_name'],
            'Phone'             => $d['phone'],
            'Email'             => $d['email'],
            'Company'           => $d['company'],
            'Type of event'     => $d['event_type'],
            'Preferred space'   => $d['preferred_space'],
            'Preferred date'    => $d['preferred_date'],
            'Preferred time'    => $d['preferred_time'],
            'Estimated guests'  => $d['guests'],
            'Event card'        => $d['source'],
        ]);
    if ($d['message'] !== '') {
        $body .= '<p style="margin:0 0 6px;font-size:13px;color:#9a9089;">Message</p>' . email_p($d['message']);
    }
    $html = email_layout('New Private Events Enquiry', $body, [
        'label' => 'Reply to ' . $d['full_name'],
        'url'   => 'mailto:' . $d['email'],
    ]);

    $headers = [
        'From: ' . SITE_NAME . ' <' . enquiry_mail_from() . '>',
        'Reply-To: ' . header_safe($d['full_name']) . ' <' . header_safe($d['email']) . '>',
        'Content-Type: text/html; charset=UTF-8',
    ];
    $cc = trim(setting('pe_enquiry_cc_email'));
    if ($cc !== '') {
        $headers[] = 'Cc: ' . $cc;
    }
    app_mail($to, 'New Private Events Enquiry - ' . header_safe($d['full_name']), $html, $headers);
}

/**
 * Send the client a thank-you confirmation, using the admin-editable
 * subject/message. Skipped if the message has been cleared out.
 */
function send_enquiry_thankyou(array $d): void
{
    $template = setting('pe_enquiry_thankyou_message');
    if (trim($template) === '') {
        return;
    }
    $subject = setting('pe_enquiry_thankyou_subject', 'Thanks for your enquiry');
    $message = str_replace('{name}', $d['full_name'], $template);
    $html = email_layout($subject, email_p($message));
    $headers = [
        'From: ' . SITE_NAME . ' <' . enquiry_mail_from() . '>',
        'Content-Type: text/html; charset=UTF-8',
    ];
    app_mail($d['email'], $subject, $html, $headers);
}

try {
    $stmt = db()->prepare(
        'INSERT INTO enquiries (full_name, phone, email, company, event_type, preferred_space, preferred_date, preferred_time, guests, message, source)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([$fullName, $phone, $email, $company, $eventType, $space, $date, $time, $guests, $message, $source]);

    $data = [
        'full_name' => $fullName, 'phone' => $phone, 'email' => $email, 'company' => $company,
        'event_type' => $eventType, 'preferred_space' => $space, 'preferred_date' => $date,
        'preferred_time' => $time, 'guests' => $guests, 'message' => $message, 'source' => $source,
    ];
    send_enquiry_notification($data);
    send_enquiry_thankyou($data);

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'Something went wrong. Please try again.']);
}
