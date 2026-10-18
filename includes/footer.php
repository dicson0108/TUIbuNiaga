<?php
/**
 * Public Site Footer
 * Taylor's University Event Management & Ibu Niaga Platform
 * Refined to match Taylor's Campus Events design system & PRD specifications
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/helpers.php';
?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-top-grid">
      <!-- Brand & Mission Column -->
      <div class="footer-brand-col">
        <a href="<?= BASE_URL ?>/index.php" class="brand-link" style="margin-bottom: 16px;" aria-label="Taylor's Ibu Niaga Home">
          <?= render_site_brand_html('', 'Ibu Niaga') ?>
        </a>
        <p class="footer-brand-desc">
          A community initiative where women entrepreneurs build skills, find their people, and turn business dreams into everyday possibilities.
        </p>
        <div class="footer-taylors-initiative">
          <span class="footer-badge">Taylor's Impact Lab</span>
          <span style="font-size: 13px; color: var(--color-muted);">Lakeside Campus, Malaysia</span>
        </div>
      </div>

      <!-- Pathways & Discover Column -->
      <div>
        <h4 class="footer-col-title"><?= __('footer_explore', 'Explore & Learn') ?></h4>
        <ul class="footer-links-list">
          <li><a href="<?= BASE_URL ?>/modules.php" class="footer-link"><?= __('nav_modules', 'Skills Pathways') ?></a></li>
          <li><a href="<?= BASE_URL ?>/events.php" class="footer-link"><?= __('nav_events', 'Workshops & Events') ?></a></li>
          <li><a href="<?= BASE_URL ?>/bazaar.php" class="footer-link"><?= __('nav_bazaar', 'Open Bazaar') ?></a></li>
          <li><a href="<?= BASE_URL ?>/members.php" class="footer-link"><?= __('nav_members', 'Member Directory') ?></a></li>
          <li><a href="<?= BASE_URL ?>/stories.php" class="footer-link"><?= __('nav_stories', 'Success Stories') ?></a></li>
        </ul>
      </div>

      <!-- Support & Community Column -->
      <div>
        <h4 class="footer-col-title"><?= __('footer_support', 'Support & Community') ?></h4>
        <ul class="footer-links-list">
          <li><a href="<?= BASE_URL ?>/about.php" class="footer-link"><?= __('nav_about', 'About Ibu Niaga') ?></a></li>
          <li><a href="<?= BASE_URL ?>/projects.php" class="footer-link"><?= __('nav_projects', 'Community Projects') ?></a></li>
          <li><a href="<?= BASE_URL ?>/help.php" class="footer-link"><?= __('nav_help', 'Help Center & FAQs') ?></a></li>
          <li><a href="<?= BASE_URL ?>/contact.php" class="footer-link"><?= __('nav_contact', 'Contact University Team') ?></a></li>
          <?php if (!is_logged_in()): ?>
            <li><a href="<?= BASE_URL ?>/register.php" class="footer-link" style="color: var(--color-primary); font-weight: 600;"><?= __('btn_register', 'Join Community') ?> →</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- Bottom Bar with Copyright, Legal & Location -->
    <div class="footer-bottom-bar">
      <div>
        <span>© <?= date('Y') ?> Ibu Niaga · Taylor's University. All rights reserved.</span>
      </div>
      <div class="footer-bottom-links">
        <a href="<?= BASE_URL ?>/about.php#privacy" class="footer-link">Privacy notice (PDPA)</a>
        <span class="footer-sep">·</span>
        <a href="<?= BASE_URL ?>/about.php#terms" class="footer-link">Terms of use</a>
        <span class="footer-sep">·</span>
        <a href="mailto:ibuniaga@taylors.edu.my" class="footer-link">ibuniaga@taylors.edu.my</a>
      </div>
    </div>
  </div>
</footer>

<!-- Floating Back To Top Button (Apple Frosted Glass Style) -->
<button type="button" id="back-to-top" class="back-to-top-btn" aria-label="Back to top" title="Back to top">
  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>
</button>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
