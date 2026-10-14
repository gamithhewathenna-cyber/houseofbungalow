<?php
require_once __DIR__ . '/auth.php';
log_activity('logout');
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
