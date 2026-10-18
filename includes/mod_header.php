<?php
/**
 * Moderator Workspace Header
 * Taylor's University Event Management & Ibu Niaga Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_role(['moderator', 'admin']);
$user = current_user();
$activePage = basename($_SERVER['PHP_SELF']);
$currentLang = current_lang();
$flash = get_flash();

// Fetch counts for moderator badges
$pendingAppsCount = 0;
$openTicketsCount = 0;
try {
    $pdo = get_db();
    $stmt = $pdo->query("SELECT COUNT(*) FROM applications WHERE status IN ('Submitted', 'In Review', 'Needs Documents')");
    $pendingAppsCount = (int)$stmt->fetchColumn();

    $stmt2 = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('Open', 'In Progress')");
    $openTicketsCount = (int)$stmt2->fetchColumn();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="<?= e($currentLang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . " · Moderator Workspace" : "Moderator Workspace · Ibu Niaga" ?></title>
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
      <div class="sidebar-title"><?= __('ws_moderator', 'MODERATOR WORKSPACE') ?></div>
    </div>

    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>/moderator/index.php" class="sidebar-nav-item <?= ($activePage === 'index.php') ? 'active' : '' ?>">
        <span>Moderator Dashboard</span>
      </a>
      <a href="<?= BASE_URL ?>/moderator/applications.php" class="sidebar-nav-item <?= ($activePage === 'applications.php' || $activePage === 'application_detail.php') ? 'active' : '' ?>">
        <span>Application Review</span>
        <?php if ($pendingAppsCount > 0): ?>
          <span class="nav-badge"><?= $pendingAppsCount ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= BASE_URL ?>/moderator/participants.php" class="sidebar-nav-item <?= ($activePage === 'participants.php' || $activePage === 'participant_editor.php') ? 'active' : '' ?>">
        <span>Participant Records</span>
      </a>
      <a href="<?= BASE_URL ?>/moderator/bazaar_moderation.php" class="sidebar-nav-item <?= ($activePage === 'bazaar_moderation.php') ? 'active' : '' ?>">
        <span>Bazaar Moderation</span>
      </a>
      <a href="<?= BASE_URL ?>/moderator/feedback_moderation.php" class="sidebar-nav-item <?= ($activePage === 'feedback_moderation.php') ? 'active' : '' ?>">
        <span>Feedback Moderation</span>
      </a>
      <a href="<?= BASE_URL ?>/moderator/tickets.php" class="sidebar-nav-item <?= ($activePage === 'tickets.php' || $activePage === 'ticket_detail.php') ? 'active' : '' ?>">
        <span>Ticket Management</span>
        <?php if ($openTicketsCount > 0): ?>
          <span class="nav-badge"><?= $openTicketsCount ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= BASE_URL ?>/moderator/content.php" class="sidebar-nav-item <?= ($activePage === 'content.php' || $activePage === 'content_editor.php') ? 'active' : '' ?>">
        <span>Content Management</span>
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
        Ibu Niaga / <strong>Moderator</strong>
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
          <?= e($user['name']) ?> (Moderator)
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
