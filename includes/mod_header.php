<?php
/**
 * Moderator Workspace Header
 * Taylor's University Event Management & Ibu Niaga Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/i18n.php';

$pendingAppsCount = 0;
$openTicketsCount = 0;
try {
    $pdo = get_db();
    $stmt = $pdo->query("SELECT COUNT(*) FROM applications WHERE status IN ('Submitted', 'In Review', 'Needs Documents')");
    $pendingAppsCount = (int)$stmt->fetchColumn();

    $stmt2 = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('Open', 'In Progress')");
    $openTicketsCount = (int)$stmt2->fetchColumn();
} catch (Exception $e) {}

$wsTitle = 'Moderator';
$wsRoleLabel = __('ws_moderator', 'MODERATOR WORKSPACE');
$wsAllowedRoles = ['moderator', 'admin'];
$wsUserSuffix = '(Moderator)';
$wsNavItems = [
    ['label' => 'Moderator Dashboard', 'url' => BASE_URL . '/moderator/index.php', 'pages' => ['index.php']],
    ['label' => 'Application Review', 'url' => BASE_URL . '/moderator/applications.php', 'pages' => ['applications.php', 'application_detail.php'], 'badge' => $pendingAppsCount],
    ['label' => 'Participant Records', 'url' => BASE_URL . '/moderator/participants.php', 'pages' => ['participants.php', 'participant_editor.php']],
    ['label' => 'Bazaar Moderation', 'url' => BASE_URL . '/moderator/bazaar_moderation.php', 'pages' => ['bazaar_moderation.php']],
    ['label' => 'Feedback Moderation', 'url' => BASE_URL . '/moderator/feedback_moderation.php', 'pages' => ['feedback_moderation.php']],
    ['label' => 'Ticket Management', 'url' => BASE_URL . '/moderator/tickets.php', 'pages' => ['tickets.php', 'ticket_detail.php'], 'badge' => $openTicketsCount],
    ['label' => 'Content Management', 'url' => BASE_URL . '/moderator/content.php', 'pages' => ['content.php', 'content_editor.php']],
];

require __DIR__ . '/workspace_header.php';
