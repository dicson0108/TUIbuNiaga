<?php
/**
 * Member Workspace Header
 * Taylor's University Event Management & Ibu Niaga Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_role(['user', 'moderator', 'admin']);
$user = current_user();
$activePage = basename($_SERVER['PHP_SELF']);
$currentLang = current_lang();
$flash = get_flash();

// Fetch unread notifications count
$unreadNotifCount = 0;
try {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
    $stmt->execute([$user['id']]);
    $unreadNotifCount = (int)$stmt->fetchColumn();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="<?= e($currentLang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . " · Member Workspace" : "Member Workspace · Ibu Niaga" ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/logo/logo.png">
</head>
<body>

<div class="workspace-wrapper">
  <!-- Sidebar -->
  <aside class="workspace-sidebar">
    <div class="sidebar-brand">
      <a href="<?= BASE_URL ?>/index.php" style="display: block; text-decoration: none;">
        <?= render_site_brand_html('', 'Ibu Niaga') ?>
      </a>
      <div class="sidebar-title"><?= __('ws_member', 'USER WORKSPACE') ?></div>
    </div>

    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>/member/index.php" class="sidebar-nav-item <?= ($activePage === 'index.php') ? 'active' : '' ?>">
        <span><?= __('ws_dashboard', 'Dashboard') ?></span>
      </a>
      <a href="<?= BASE_URL ?>/member/profile.php" class="sidebar-nav-item <?= ($activePage === 'profile.php' || $activePage === 'change_password.php') ? 'active' : '' ?>">
        <span>My Profile</span>
      </a>
      <a href="<?= BASE_URL ?>/member/program_registration.php" class="sidebar-nav-item <?= ($activePage === 'program_registration.php') ? 'active' : '' ?>">
        <span>Program Registration Form</span>
      </a>
      <a href="<?= BASE_URL ?>/member/applications.php" class="sidebar-nav-item <?= ($activePage === 'applications.php') ? 'active' : '' ?>">
        <span><?= __('ws_applications', 'My Applications') ?></span>
      </a>
      <a href="<?= BASE_URL ?>/member/events.php" class="sidebar-nav-item <?= ($activePage === 'events.php' || $activePage === 'cancel_event.php' || $activePage === 'event_feedback.php') ? 'active' : '' ?>">
        <span><?= __('ws_events', 'My Events') ?></span>
      </a>
      <a href="<?= BASE_URL ?>/member/bazaar.php" class="sidebar-nav-item <?= ($activePage === 'bazaar.php' || $activePage === 'listing_editor.php' || $activePage === 'delete_listing.php') ? 'active' : '' ?>">
        <span><?= __('ws_bazaar', 'My Bazaar Listings') ?></span>
      </a>
      <a href="<?= BASE_URL ?>/member/enquiries.php" class="sidebar-nav-item <?= ($activePage === 'enquiries.php') ? 'active' : '' ?>">
        <span><?= __('ws_enquiries', 'Enquiries') ?></span>
      </a>
      <a href="<?= BASE_URL ?>/member/notifications.php" class="sidebar-nav-item <?= ($activePage === 'notifications.php') ? 'active' : '' ?>">
        <span><?= __('ws_notifications', 'Notifications') ?></span>
        <?php if ($unreadNotifCount > 0): ?>
          <span class="nav-badge"><?= $unreadNotifCount ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= BASE_URL ?>/member/tickets.php" class="sidebar-nav-item <?= ($activePage === 'tickets.php' || $activePage === 'ticket_create.php' || $activePage === 'ticket_thread.php') ? 'active' : '' ?>">
        <span><?= __('ws_tickets', 'My Support Tickets') ?></span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <div style="font-size: 13px; color: var(--color-muted); margin-bottom: 8px;">
        Need a hand?<br>Our community team is here.
      </div>
      <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary btn-sm" style="width: 100%;">
        Visit public website
      </a>
      <div style="margin-top: 10px;">
        <a href="<?= BASE_URL ?>/logout.php" class="text-caption" style="color: var(--color-primary); font-weight: 600;">Log out</a>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="workspace-main">
    <header class="workspace-topbar">
      <div class="topbar-breadcrumb">
        Ibu Niaga / <strong>User</strong>
      </div>

      <div class="topbar-user-menu">
        <div class="lang-switcher">
          <a href="?lang=en" class="lang-btn <?= ($currentLang === 'en') ? 'active' : '' ?>">EN</a>
          <span>/</span>
          <a href="?lang=bm" class="lang-btn <?= ($currentLang === 'bm') ? 'active' : '' ?>">BM</a>
          <span>/</span>
          <a href="?lang=zh" class="lang-btn <?= ($currentLang === 'zh') ? 'active' : '' ?>">中文</a>
        </div>

        <a href="<?= BASE_URL ?>/member/notifications.php" style="font-size: 14px; font-weight: 600; color: var(--color-ink); display: flex; align-items: center; gap: 4px;">
          <span>Notifications</span>
          <?php if ($unreadNotifCount > 0): ?>
            <span class="nav-badge" style="background-color: var(--color-primary); color: #fff;"><?= $unreadNotifCount ?></span>
          <?php endif; ?>
        </a>

        <div style="font-size: 14px; font-weight: 700; color: var(--color-ink);">
          <?= e($user['name']) ?>
        </div>
      </div>
    </header>

    <?php if ($flash): ?>
      <div style="padding: 24px 32px 0;">
        <div class="alert alert-<?= e($flash['type']) ?>">
          <span><?= e($flash['message']) ?></span>
        </div>
      </div>
    <?php endif; ?>

    <div class="workspace-content">
