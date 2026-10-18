<?php
/**
 * Shared Workspace Header Layout
 * Taylor's University Event Management & Ibu Niaga Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$allowedRoles = $wsAllowedRoles ?? ['user', 'moderator', 'admin'];
require_role($allowedRoles);

$user = current_user();
$activePage = basename($_SERVER['PHP_SELF']);
$currentLang = current_lang();
$flash = get_flash();

$wsTitle = $wsTitle ?? 'Workspace';
$wsRoleLabel = $wsRoleLabel ?? strtoupper($user['role'] ?? 'USER') . ' WORKSPACE';
$wsNavItems = $wsNavItems ?? [];
$pageTitle = isset($pageTitle) ? $pageTitle . " · " . $wsTitle : $wsTitle . " · Ibu Niaga";
?>
<!DOCTYPE html>
<html lang="<?= e($currentLang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
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
      <div class="sidebar-title"><?= e($wsRoleLabel) ?></div>
    </div>

    <nav class="sidebar-nav">
      <?php foreach ($wsNavItems as $item): ?>
        <?php $isActive = in_array($activePage, $item['pages'] ?? [], true); ?>
        <a href="<?= e($item['url']) ?>" class="sidebar-nav-item <?= $isActive ? 'active' : '' ?>">
          <span><?= e($item['label']) ?></span>
          <?php if (!empty($item['badge'])): ?>
            <span class="nav-badge"><?= (int)$item['badge'] ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
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
        Ibu Niaga / <strong><?= e($wsTitle) ?></strong>
      </div>

      <div class="topbar-user-menu">
        <div class="lang-switcher">
          <a href="?lang=en" class="lang-btn <?= ($currentLang === 'en') ? 'active' : '' ?>">EN</a>
          <a href="?lang=bm" class="lang-btn <?= ($currentLang === 'bm') ? 'active' : '' ?>">BM</a>
          <a href="?lang=zh" class="lang-btn <?= ($currentLang === 'zh') ? 'active' : '' ?>">中文</a>
        </div>

        <?php if (!empty($wsExtraTopMenuHtml)): ?>
          <?= $wsExtraTopMenuHtml ?>
        <?php endif; ?>

        <div style="font-size: 14px; font-weight: 700; color: var(--color-ink);">
          <?= e($user['name']) ?><?= !empty($wsUserSuffix) ? ' ' . e($wsUserSuffix) : '' ?>
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
