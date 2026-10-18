<?php
/**
 * Logout Handler
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

if (isset($_SESSION['user'])) {
    log_audit('USER_LOGOUT', 'users', $_SESSION['user']['id'], "User logged out");
}

session_unset();
session_destroy();

session_start();
set_flash('info', 'You have been successfully logged out.');
header('Location: ' . BASE_URL . '/index.php');
exit;
