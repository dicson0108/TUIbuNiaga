<?php
/**
 * About Ibu Niaga Page
 * Matches Figma: 02 About Ibu Niaga.png
 */

$pageTitle = "About Ibu Niaga";
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container">
    <div style="max-width: 800px; margin-bottom: 48px;">
      <div class="card-category" style="margin-bottom: 12px;">OUR MISSION & VISION</div>
      <h1 class="display-title" style="margin-bottom: 20px;">Empowering women entrepreneurs through community, craft, and commerce.</h1>
      <p style="font-size: 18px; line-height: 1.6; color: var(--color-muted);">
        Taylor's University Event Management & Ibu Niaga Platform is a dedicated social innovation initiative incubated at Taylor's Lakeside Campus. We bring together academic culinary faculty, business mentors, and local aspiring women to transform small home ideas into thriving, sustainable enterprises.
      </p>
    </div>

    <div class="hero-grid" style="margin-bottom: 64px;">
      <div>
        <h2 style="margin-bottom: 16px;">Rooted in Taylor's University Purpose</h2>
        <p style="margin-bottom: 16px; line-height: 1.6;">
          Through our School of Food Studies & Gastronomy and the Taylor's Community Impact Lab, participants gain direct access to commercial-grade kitchen facilities, certified culinary masters, and business faculty.
        </p>
        <p style="line-height: 1.6;">
          We believe in practical dignity: not just giving handouts, but providing high-leverage skills, official certifications, and a verified community marketplace where members can sell their creations directly to university students, staff, and the wider Subang Jaya neighborhood.
        </p>
      </div>
      <div>
        <img src="<?= BASE_URL ?>/assets/images/22 Sample Photography - Community.png" alt="Ibu Niaga community meeting" class="hero-image" style="height: 360px;">
      </div>
    </div>

    <!-- 3 Pillars -->
    <div style="margin-bottom: 32px;">
      <h2>Our Core Pillars</h2>
    </div>
    <div class="card-grid" style="margin-bottom: 64px;">
      <div class="stat-card" style="padding: 32px;">
        <div style="font-size: 24px; font-weight: 700; color: var(--color-primary); margin-bottom: 8px;">01</div>
        <div class="card-title" style="margin-bottom: 10px;">Hands-On Skill Mastery</div>
        <p class="card-text">Direct training in pastry arts, commercial food hygiene, batch costing, and hospitality table etiquette in certified Taylor's kitchen studios.</p>
      </div>

      <div class="stat-card" style="padding: 32px;">
        <div style="font-size: 24px; font-weight: 700; color: var(--color-primary); margin-bottom: 8px;">02</div>
        <div class="card-title" style="margin-bottom: 10px;">Empathetic Peer Circles</div>
        <p class="card-text">Safe mental health and emotional resilience workshops that address the real-world anxieties of balancing home obligations and entrepreneurship.</p>
      </div>

      <div class="stat-card" style="padding: 32px;">
        <div style="font-size: 24px; font-weight: 700; color: var(--color-primary); margin-bottom: 8px;">03</div>
        <div class="card-title" style="margin-bottom: 10px;">Market Access & Bazaar</div>
        <p class="card-text">Showcase listings in our Open Bazaar, campus catering opportunities, and university pop-up stalls that generate immediate, verified income.</p>
      </div>
    </div>

    <!-- Call to action -->
    <div class="callout-peach">
      <div>
        <h3>Ready to start your journey?</h3>
        <p style="color: var(--color-muted); font-size: 15px;">Applications are open for upcoming cohorts and workshops.</p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-lg">Apply for Ibu Niaga</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
