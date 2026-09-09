<?php
/**
 * House of Bungalow — Configuration
 * ---------------------------------------------------------------
 * Edit the four DB_* values below to match the database you create
 * in cPanel (MySQL Databases section). Everything else can stay.
 * ---------------------------------------------------------------
 */

// ---- Database (from cPanel > MySQL Databases) ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_db_name');      // e.g. cpaneluser_hob
define('DB_USER', 'your_db_user');      // e.g. cpaneluser_admin
define('DB_PASS', 'your_db_password');

// ---- Site ----
define('SITE_NAME', 'House of Bungalow');

// Base URL path. If the site lives in the domain root, leave as ''.
// If it lives in a sub-folder, e.g. https://example.com/hob/, set '/hob'.
define('BASE_URL', '');

// Absolute filesystem path to the project root (auto-detected).
define('APP_ROOT', dirname(__DIR__));

// Uploads directory (relative to project root, web-accessible).
define('UPLOAD_DIR', APP_ROOT . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Error display: set to false in production.
define('DEBUG', false);

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set('Australia/Sydney');
