<?php
/**
 * Configuration & Environment Setup
 * Taylor's University Event Management & Ibu Niaga Platform
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'tu_ibu_niaga');
define('DB_USER', 'root');
define('DB_PASS', '');

// Auto-detect base URL (works seamlessly in any XAMPP subdir or php -S root)
$basePath = '';
if (!empty($_SERVER['DOCUMENT_ROOT'])) {
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    $projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)));
    if ($docRoot && $projectRoot && str_starts_with($projectRoot, $docRoot)) {
        $basePath = rtrim(str_replace('\\', '/', substr($projectRoot, strlen($docRoot))), '/');
    }
}
if ($basePath === '' && !empty($_SERVER['SCRIPT_NAME'])) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $basePath = rtrim($scriptDir, '/');
    // Strip subfolders like /member, /moderator, /admin if current script is in workspace
    $basePath = preg_replace('#/(member|moderator|admin)$#', '', $basePath);
    if ($basePath === '.') {
        $basePath = '';
    }
}
define('BASE_URL', $basePath === '' ? '' : $basePath);

// App metadata
define('APP_NAME', "Taylor's Ibu Niaga");
define('APP_VERSION', '1.0.0');
define('MAX_UPLOAD_SIZE_MB', 5);

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            die('CSRF token validation failed. Please refresh and try again.');
        }
    }
}

// Flash messages
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
