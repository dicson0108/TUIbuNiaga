<?php
/**
 * Admin Workspace Header
 * Taylor's University Event Management & Ibu Niaga Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_role('admin');
$user = current_user();
$activePage = basename($_SERVER['PHP_SELF']);
$currentLang = current_lang();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="<?= e($currentLang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . " · Admin Workspace" : "Admin Workspace · Ibu Niaga" ?></title>
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
      <div class="sidebar-title"><?= __('ws_admin', 'ADMIN WORKSPACE') ?></div>
    </div>

    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>/admin/index.php" class="sidebar-nav-item <?= ($activePage === 'index.php') ? 'active' : '' ?>">
        <span>Admin Dashboard / Analytics</span>
      </a>
      <a href="<?= BASE_URL ?>/admin/users.php" class="sidebar-nav-item <?= ($activePage === 'users.php' || $activePage === 'user_editor.php') ? 'active' : '' ?>">
        <span>User & Role Management</span>
      </a>
      <a href="<?= BASE_URL ?>/admin/events.php" class="sidebar-nav-item <?= ($activePage === 'events.php' || $activePage === 'event_editor.php' || $activePage === 'event_attendees.php') ? 'active' : '' ?>">
        <span>Event Management</span>
      </a>
      <a href="<?= BASE_URL ?>/admin/modules.php" class="sidebar-nav-item <?= ($activePage === 'modules.php' || $activePage === 'module_editor.php') ? 'active' : '' ?>">
        <span>Module Management</span>
      </a>
      <a href="<?= BASE_URL ?>/admin/reminders.php" class="sidebar-nav-item <?= ($activePage === 'reminders.php') ? 'active' : '' ?>">
        <span>Reminder & Notification Settings</span>
      </a>
      <a href="<?= BASE_URL ?>/admin/translations.php" class="sidebar-nav-item <?= ($activePage === 'translations.php') ? 'active' : '' ?>">
        <span>Translation Management</span>
      </a>
      <a href="<?= BASE_URL ?>/admin/settings.php" class="sidebar-nav-item <?= ($activePage === 'settings.php') ? 'active' : '' ?>">
        <span>System Settings</span>
      </a>
      <a href="<?= BASE_URL ?>/admin/audit_logs.php" class="sidebar-nav-item <?= ($activePage === 'audit_logs.php') ? 'active' : '' ?>">
        <span>Audit Log</span>
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
        Ibu Niaga / <strong>Admin</strong>
      </div>

      <div class="topbar-user-menu">
        <div class="lang-switcher">
          <a href="?lang=en" class="lang-btn <?= ($currentLang === 'en') ? 'active' : '' ?>">EN</a>
          <span>/</span>
          <a href="?lang=bm" class="lang-btn <?= ($currentLang === 'bm') ? 'active' : '' ?>">BM</a>
          <span>/</span>
          <a href="?lang=zh" class="lang-btn <?= ($currentLang === 'zh') ? 'active' : '' ?>">中文</a>
        </div>

        <div style="font-size: 14px; font-weight: 700; color: var(--color-ink);">
          <?= e($user['name']) ?> (Admin)
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
