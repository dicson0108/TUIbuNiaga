<?php
/**
 * Authentication and Access Control
 * Taylor's University Event Management & Ibu Niaga Platform
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return !empty($_SESSION['user']['id']);
}

function has_role($roles) {
    if (!is_logged_in()) {
        return false;
    }
    if (is_string($roles)) {
        $roles = [$roles];
    }
    return in_array($_SESSION['user']['role'] ?? '', $roles, true);
}

function require_login($returnUrl = null) {
    if (!is_logged_in()) {
        $target = $returnUrl ?? $_SERVER['REQUEST_URI'] ?? '';
        set_flash('error', 'Please log in to access this page.');
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($target));
        exit;
    }
}

function require_role($roles) {
    require_login();
    if (!has_role($roles)) {
        set_flash('error', 'Access restricted. You do not have permission for that area.');
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function redirect_by_role($role) {
    switch ($role) {
        case 'admin':
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        case 'moderator':
            header('Location: ' . BASE_URL . '/moderator/index.php');
            exit;
        case 'user':
        default:
            header('Location: ' . BASE_URL . '/member/index.php');
            exit;
    }
}
