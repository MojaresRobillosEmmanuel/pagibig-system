<?php
// Application configuration
define('BASE_URL', 'http://localhost/Pag-ibig1.0/');
define('SITE_NAME', 'PAG-IBIG Remittances Generator');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone setting
date_default_timezone_set('Asia/Manila');

// Include database connection
require_once 'db_connect.php';
?>
