<?php
/**
 * Handle an image upload from an admin form.
 * Returns a web-relative path (e.g. "uploads/xyz.jpg") on success,
 * or null if no file was uploaded, or throws on validation failure.
 */
function handle_upload(string $field): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed (error code ' . $f['error'] . ').');
    }
    if ($f['size'] > 8 * 1024 * 1024) {
        throw new RuntimeException('File is too large (max 8 MB).');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        'image/svg+xml' => 'svg',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($f['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Unsupported file type. Use JPG, PNG, WEBP, GIF or SVG.');
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

/**
 * Handle a video upload from an admin form (used for the hero background).
 * Returns a web-relative path (e.g. "uploads/xyz.mp4") on success,
 * or null if no file was uploaded, or throws on validation failure.
 */
function handle_video_upload(string $field): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('That video is larger than this server currently allows. Ask your host to raise upload_max_filesize / post_max_size, or compress the video further.');
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed (error code ' . $f['error'] . ').');
    }
    if ($f['size'] > 60 * 1024 * 1024) {
        throw new RuntimeException('Video is too large (max 60 MB). Export at 1280×720 (720p) and compress before uploading.');
    }

    $allowed = [
        'video/mp4'       => 'mp4',
        'video/webm'      => 'webm',
        'video/quicktime' => 'mov',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($f['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Unsupported video type. Use MP4 (H.264) or WEBM, ideally 1280×720.');
    }
    $ext = $allowed[$mime];

    if (!is_dir(UPLOAD_DIR)) {
        @mkdir(UPLOAD_DIR, 0755, true);
    }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = UPLOAD_DIR . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        throw new RuntimeException('Could not store the uploaded video.');
    }
    return 'uploads/' . $name;
}

/**
 * Update (or insert) a settings key.
 */
function save_setting(string $key, ?string $value): void
{
    $stmt = db()->prepare(
        'INSERT INTO settings (skey, svalue) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE svalue = :v2'
    );
    $stmt->execute([':k' => $key, ':v' => $value, ':v2' => $value]);
}
