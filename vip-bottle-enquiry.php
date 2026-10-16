<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$company  = trim($_POST['company'] ?? '');
$occasion = trim($_POST['occasion'] ?? '');
$guests   = trim($_POST['guests'] ?? '');
$date     = trim($_POST['preferred_date'] ?? '');

if ($fullName === '' || $phone === '' || $guests === '' || $date === '') {
    echo json_encode(['ok' => false, 'error' => 'Please fill in all required fields.']);
    exit;
}

/**
 * Strip CR/LF from a value before it goes into a mail header, so
 * submitted text can't be used to inject extra headers.
 */
function vipbottle_header_safe(string $v): string
{
    return trim(str_replace(["\r", "\n"], '', $v));
}

/**
 * A plausible "From" address for mail sent by this server - uses the
 * requested domain so it lines up with where the site is hosted.
 */
function vipbottle_mail_from(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host = preg_replace('/:\d+$/', '', $host);
    $host = preg_replace('/^www\./i', '', $host);
    return 'no-reply@' . $host;
}

/**
 * Notify the House (if a "send enquiries to" address is configured) with
 * the VIP Bottle Service enquiry details. No email address is collected
 * on this form, so there is no client-facing confirmation email - the
 * popup itself shows the admin-editable thank-you message instead.
 */
function send_vipbottle_notification(array $d): void
{
    $to = trim(setting('below_vipbottle_enquiry_to_email'));
    if ($to === '') {
        return;
    }
    $lines = [
        'A new VIP Bottle Service enquiry was submitted on the Below page.',
        '',
        'Name: ' . $d['full_name'],
        'Phone: ' . $d['phone'],
    ];
    if ($d['company'] !== '') { $lines[] = 'Company: ' . $d['company']; }
    if ($d['occasion'] !== '') { $lines[] = 'Occasion: ' . $d['occasion']; }
    $lines[] = 'Number of guests: ' . $d['guests'];
    $lines[] = 'Preferred date: ' . $d['preferred_date'];

    $headers = [
        'From: ' . SITE_NAME . ' <' . vipbottle_mail_from() . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    $cc = trim(setting('below_vipbottle_enquiry_cc_email'));
    if ($cc !== '') {
        $headers[] = 'Cc: ' . $cc;
    }
    app_mail($to, 'New VIP Bottle Service Enquiry - ' . vipbottle_header_safe($d['full_name']), implode("\n", $lines), $headers);
}

try {
    $stmt = db()->prepare(
        'INSERT INTO vip_bottle_enquiries (full_name, phone, company, occasion, guests, preferred_date)
         VALUES (?,?,?,?,?,?)'
    );
    $stmt->execute([$fullName, $phone, $company, $occasion, $guests, $date]);

    send_vipbottle_notification([
        'full_name' => $fullName, 'phone' => $phone, 'company' => $company,
        'occasion' => $occasion, 'guests' => $guests, 'preferred_date' => $date,
    ]);

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'Something went wrong. Please try again.']);
}
