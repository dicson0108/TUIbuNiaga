<?php
/**
 * Member Workspace Header
 * Taylor's University Event Management & Ibu Niaga Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/i18n.php';

$user = current_user();
$unreadNotifCount = 0;
if (!empty($user['id'])) {
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
        $stmt->execute([$user['id']]);
        $unreadNotifCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {}
}

$wsTitle = 'User';
$wsRoleLabel = __('ws_member', 'USER WORKSPACE');
$wsAllowedRoles = ['user', 'moderator', 'admin'];
$wsNavItems = [
    ['label' => __('ws_dashboard', 'Dashboard'), 'url' => BASE_URL . '/member/index.php', 'pages' => ['index.php']],
    ['label' => 'My Profile', 'url' => BASE_URL . '/member/profile.php', 'pages' => ['profile.php', 'change_password.php']],
    ['label' => 'Program Registration Form', 'url' => BASE_URL . '/member/program_registration.php', 'pages' => ['program_registration.php']],
    ['label' => __('ws_applications', 'My Applications'), 'url' => BASE_URL . '/member/applications.php', 'pages' => ['applications.php']],
    ['label' => __('ws_events', 'My Events'), 'url' => BASE_URL . '/member/events.php', 'pages' => ['events.php', 'cancel_event.php', 'event_feedback.php']],
    ['label' => __('ws_bazaar', 'My Bazaar Listings'), 'url' => BASE_URL . '/member/bazaar.php', 'pages' => ['bazaar.php', 'listing_editor.php', 'delete_listing.php']],
    ['label' => __('ws_enquiries', 'Enquiries'), 'url' => BASE_URL . '/member/enquiries.php', 'pages' => ['enquiries.php']],
    ['label' => __('ws_notifications', 'Notifications'), 'url' => BASE_URL . '/member/notifications.php', 'pages' => ['notifications.php'], 'badge' => $unreadNotifCount],
    ['label' => __('ws_tickets', 'My Support Tickets'), 'url' => BASE_URL . '/member/tickets.php', 'pages' => ['tickets.php', 'ticket_create.php', 'ticket_thread.php']],
];

$wsExtraTopMenuHtml = '<a href="' . BASE_URL . '/member/notifications.php" style="font-size: 14px; font-weight: 600; color: var(--color-ink); display: flex; align-items: center; gap: 4px;"><span>Notifications</span>'
    . ($unreadNotifCount > 0 ? '<span class="nav-badge" style="background-color: var(--color-primary); color: #fff;">' . $unreadNotifCount . '</span>' : '')
    . '</a>';

require __DIR__ . '/workspace_header.php';
