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
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$area     = trim($_POST['area_of_interest'] ?? '');
$position = trim($_POST['position'] ?? '');

if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $area === '') {
    echo json_encode(['ok' => false, 'error' => 'Please fill in all required fields with a valid email address.']);
    exit;
}

/**
 * Validates and stores the uploaded CV. Throws RuntimeException with a
 * guest-friendly message on any failure.
 */
function career_handle_cv_upload(string $field): string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('Please attach your CV.');
    }
    $f = $_FILES[$field];
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('That file is too large (max 8 MB).');
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('That file could not be uploaded. Please try again.');
    }
    if ($f['size'] > 8 * 1024 * 1024) {
        throw new RuntimeException('That file is too large (max 8 MB).');
    }

    $allowed = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($f['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Please upload a PDF, DOC or DOCX file.');
    }
    $ext = $allowed[$mime];

    if (!is_dir(UPLOAD_DIR)) {
        @mkdir(UPLOAD_DIR, 0755, true);
    }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = UPLOAD_DIR . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        throw new RuntimeException('Could not store the uploaded file.');
    }
    return 'uploads/' . $name;
}

function career_header_safe(string $v): string
{
    return trim(str_replace(["\r", "\n"], '', $v));
}

function career_mail_from(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host = preg_replace('/:\d+$/', '', $host);
    $host = preg_replace('/^www\./i', '', $host);
    return 'no-reply@' . $host;
}

/**
 * An absolute URL to a stored file, for use in emails (which have no
 * browser location to resolve a relative path against).
 */
function career_absolute_url(string $path): string
{
    $url = asset($path);
    if ($url === '' || preg_match('#^https?://#i', $url)) {
        return $url;
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . $url;
}

function send_career_notification(array $d): void
{
    $to = trim(setting('career_to_email'));
    if ($to === '') {
        return;
    }
    $body = email_p('A new Join Our Team application was submitted.')
        . email_details_table([
            'Name'             => $d['full_name'],
            'Email'            => $d['email'],
            'Phone'            => $d['phone'],
            'Area of interest' => $d['area_of_interest'],
            'Position'         => $d['position'],
        ]);
    $html = email_layout('New Join Our Team Application', $body, [
        'label' => 'View CV',
        'url'   => career_absolute_url($d['cv_path']),
    ]);

    $headers = [
        'From: ' . SITE_NAME . ' <' . career_mail_from() . '>',
        'Reply-To: ' . career_header_safe($d['full_name']) . ' <' . career_header_safe($d['email']) . '>',
        'Content-Type: text/html; charset=UTF-8',
    ];
    $cc = trim(setting('career_cc_email'));
    if ($cc !== '') {
        $headers[] = 'Cc: ' . $cc;
    }
    app_mail($to, 'New Join Our Team Application - ' . career_header_safe($d['full_name']), $html, $headers);
}

try {
    $cvPath = career_handle_cv_upload('cv');

    $stmt = db()->prepare(
        'INSERT INTO career_applications (full_name, email, phone, area_of_interest, position, cv_path)
         VALUES (?,?,?,?,?,?)'
    );
    $stmt->execute([$fullName, $email, $phone, $area, $position, $cvPath]);

    send_career_notification([
        'full_name' => $fullName, 'email' => $email, 'phone' => $phone,
        'area_of_interest' => $area, 'position' => $position, 'cv_path' => $cvPath,
    ]);

    echo json_encode(['ok' => true]);
} catch (RuntimeException $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'Something went wrong. Please try again.']);
}
