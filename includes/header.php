<?php
/**
 * Public Site Header
 * Taylor's University Event Management & Ibu Niaga Platform
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$pageTitle = isset($pageTitle) ? $pageTitle . " · " . APP_NAME : APP_NAME . " · Taylor's University";
$currentLang = current_lang();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="<?= e($currentLang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/favicon.ico">
  <link rel="shortcut icon" type="image/x-icon" href="<?= BASE_URL ?>/favicon.ico">
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/logo/logo.png">
</head>
<body>

<header class="public-header">
  <div class="header-container">
    <a href="<?= BASE_URL ?>/index.php" class="brand-link" title="<?= APP_NAME ?>">
      <?= render_site_brand_html('', 'Ibu Niaga') ?>
    </a>

    <nav class="public-nav">
      <a href="<?= BASE_URL ?>/about.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : '' ?>"><?= __('nav_about', 'About') ?></a>
      <a href="<?= BASE_URL ?>/projects.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'projects.php' || basename($_SERVER['PHP_SELF']) == 'project_detail.php') ? 'active' : '' ?>"><?= __('nav_projects', 'Projects') ?></a>
      <a href="<?= BASE_URL ?>/members.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'members.php' || basename($_SERVER['PHP_SELF']) == 'member_profile.php') ? 'active' : '' ?>"><?= __('nav_members', 'Members') ?></a>
      <a href="<?= BASE_URL ?>/stories.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'stories.php' || basename($_SERVER['PHP_SELF']) == 'story_detail.php') ? 'active' : '' ?>"><?= __('nav_stories', 'Stories') ?></a>
      <a href="<?= BASE_URL ?>/modules.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'modules.php' || basename($_SERVER['PHP_SELF']) == 'module_detail.php') ? 'active' : '' ?>"><?= __('nav_modules', 'Modules') ?></a>
      <a href="<?= BASE_URL ?>/events.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'events.php' || basename($_SERVER['PHP_SELF']) == 'event_detail.php') ? 'active' : '' ?>"><?= __('nav_events', 'Events') ?></a>
      <a href="<?= BASE_URL ?>/bazaar.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'bazaar.php' || basename($_SERVER['PHP_SELF']) == 'listing_detail.php') ? 'active' : '' ?>"><?= __('nav_bazaar', 'Bazaar') ?></a>
      <a href="<?= BASE_URL ?>/help.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'help.php') ? 'active' : '' ?>"><?= __('nav_help', 'Help Center') ?></a>
      <a href="<?= BASE_URL ?>/contact.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : '' ?>"><?= __('nav_contact', 'Contact') ?></a>
    </nav>

    <div class="header-actions">
      <!-- Language Switcher Dropdown Menu -->
      <div class="lang-dropdown-wrapper">
        <?php
          $langLabels = [
            'en' => 'EN · English',
            'bm' => 'BM · Bahasa Melayu',
            'zh' => '中文 · Chinese'
          ];
          $currentLabel = match($currentLang) {
            'bm' => 'BM',
            'zh' => '中文',
            default => 'EN'
          };
        ?>
        <button type="button" class="lang-dropdown-btn" id="langDropdownTrigger" aria-expanded="false" aria-haspopup="true">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          <span><?= e($currentLabel) ?></span>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="lang-dropdown-menu" id="langDropdownMenu" role="menu">
          <a href="?lang=en" class="lang-dropdown-item <?= ($currentLang === 'en') ? 'active' : '' ?>" role="menuitem">
            <span>English (EN)</span>
            <?php if ($currentLang === 'en'): ?><span class="lang-check">✓</span><?php endif; ?>
          </a>
          <a href="?lang=bm" class="lang-dropdown-item <?= ($currentLang === 'bm') ? 'active' : '' ?>" role="menuitem">
            <span>Bahasa Melayu (BM)</span>
            <?php if ($currentLang === 'bm'): ?><span class="lang-check">✓</span><?php endif; ?>
          </a>
          <a href="?lang=zh" class="lang-dropdown-item <?= ($currentLang === 'zh') ? 'active' : '' ?>" role="menuitem">
            <span>中文 (Chinese)</span>
            <?php if ($currentLang === 'zh'): ?><span class="lang-check">✓</span><?php endif; ?>
          </a>
        </div>
      </div>


      <?php if (is_logged_in()): ?>
        <?php $u = current_user(); ?>
        <?php if ($u['role'] === 'admin'): ?>
          <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline btn-sm">Admin Workspace</a>
        <?php elseif ($u['role'] === 'moderator'): ?>
          <a href="<?= BASE_URL ?>/moderator/index.php" class="btn btn-outline btn-sm">Moderator Workspace</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/member/index.php" class="btn btn-outline btn-sm">My Dashboard</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-secondary btn-sm"><?= __('btn_logout', 'Log out') ?></a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-secondary btn-sm"><?= __('btn_login', 'Log in') ?></a>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-sm"><?= __('btn_register', 'Register') ?></a>
      <?php endif; ?>

      <!-- Mobile Menu Hamburger Button -->
      <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu" aria-expanded="false">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>
  </div>

  <!-- Apple-Style Mobile Navigation Sheet / Drawer -->
  <div class="mobile-nav-backdrop" id="mobileNavBackdrop"></div>
  <div class="mobile-nav-drawer" id="mobileNavDrawer" role="dialog" aria-modal="true" aria-label="Mobile Navigation">
    <div class="mobile-nav-header">
      <div style="font-weight: 700; font-size: 16px; color: var(--color-ink);">Menu</div>
      <button type="button" class="mobile-menu-btn" id="mobileMenuCloseBtn" aria-label="Close menu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <nav class="mobile-nav-links">
      <a href="<?= BASE_URL ?>/index.php" class="mobile-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">Home</a>
      <a href="<?= BASE_URL ?>/about.php" class="mobile-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : '' ?>"><?= __('nav_about', 'About') ?></a>
      <a href="<?= BASE_URL ?>/projects.php" class="mobile-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'projects.php' || basename($_SERVER['PHP_SELF']) == 'project_detail.php') ? 'active' : '' ?>"><?= __('nav_projects', 'Projects') ?></a>
      <a href="<?= BASE_URL ?>/members.php" class="mobile-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'members.php' || basename($_SERVER['PHP_SELF']) == 'member_profile.php') ? 'active' : '' ?>"><?= __('nav_members', 'Members') ?></a>
      <a href="<?= BASE_URL ?>/stories.php" class="mobile-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'stories.php' || basename($_SERVER['PHP_SELF']) == 'story_detail.php') ? 'active' : '' ?>"><?= __('nav_stories', 'Stories') ?></a>
      <a href="<?= BASE_URL ?>/modules.php" class="mobile-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'modules.php' || basename($_SERVER['PHP_SELF']) == 'module_detail.php') ? 'active' : '' ?>"><?= __('nav_modules', 'Modules') ?></a>
      <a href="<?= BASE_URL ?>/events.php" class="mobile-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'events.php' || basename($_SERVER['PHP_SELF']) == 'event_detail.php') ? 'active' : '' ?>"><?= __('nav_events', 'Events') ?></a>
      <a href="<?= BASE_URL ?>/bazaar.php" class="mobile-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'bazaar.php' || basename($_SERVER['PHP_SELF']) == 'listing_detail.php') ? 'active' : '' ?>"><?= __('nav_bazaar', 'Bazaar') ?></a>
      <a href="<?= BASE_URL ?>/help.php" class="mobile-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'help.php') ? 'active' : '' ?>"><?= __('nav_help', 'Help Center') ?></a>
      <a href="<?= BASE_URL ?>/contact.php" class="mobile-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : '' ?>"><?= __('nav_contact', 'Contact') ?></a>
    </nav>

    <div style="padding-top: 16px; border-top: 1px solid var(--color-line); display: flex; flex-direction: column; gap: 10px;">
      <?php if (is_logged_in()): ?>
        <?php $u = current_user(); ?>
        <?php if ($u['role'] === 'admin'): ?>
          <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline" style="width: 100%;">Admin Workspace</a>
        <?php elseif ($u['role'] === 'moderator'): ?>
          <a href="<?= BASE_URL ?>/moderator/index.php" class="btn btn-outline" style="width: 100%;">Moderator Workspace</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/member/index.php" class="btn btn-outline" style="width: 100%;">My Dashboard</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-secondary" style="width: 100%;"><?= __('btn_logout', 'Log out') ?></a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-secondary" style="width: 100%;"><?= __('btn_login', 'Log in') ?></a>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary" style="width: 100%;"><?= __('btn_register', 'Register') ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>

<?php if ($flash): ?>
  <div class="container" style="margin-top: 20px;">
    <div class="alert alert-<?= e($flash['type']) ?>">
      <span><?= e($flash['message']) ?></span>
    </div>
  </div>
<?php endif; ?>

<main class="site-main">

