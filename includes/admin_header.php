<?php
/**
 * Admin Workspace Header
 * Taylor's University Event Management & Ibu Niaga Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/i18n.php';

$wsTitle = 'Admin';
$wsRoleLabel = __('ws_admin', 'ADMIN WORKSPACE');
$wsAllowedRoles = ['admin'];
$wsUserSuffix = '(Admin)';
$wsNavItems = [
    ['label' => 'Admin Dashboard / Analytics', 'url' => BASE_URL . '/admin/index.php', 'pages' => ['index.php']],
    ['label' => 'User & Role Management', 'url' => BASE_URL . '/admin/users.php', 'pages' => ['users.php', 'user_editor.php']],
    ['label' => 'Event Management', 'url' => BASE_URL . '/admin/events.php', 'pages' => ['events.php', 'event_editor.php', 'event_attendees.php']],
    ['label' => 'Module Management', 'url' => BASE_URL . '/admin/modules.php', 'pages' => ['modules.php', 'module_editor.php']],
    ['label' => 'Reminder & Notification Settings', 'url' => BASE_URL . '/admin/reminders.php', 'pages' => ['reminders.php']],
    ['label' => 'Translation Management', 'url' => BASE_URL . '/admin/translations.php', 'pages' => ['translations.php']],
    ['label' => 'System Settings', 'url' => BASE_URL . '/admin/settings.php', 'pages' => ['settings.php']],
    ['label' => 'Audit Log', 'url' => BASE_URL . '/admin/audit_logs.php', 'pages' => ['audit_logs.php']],
];

require __DIR__ . '/workspace_header.php';
