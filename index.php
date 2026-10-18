<?php
/**
 * Public Home Page
 * Matches Figma: 01 Home.png
 */

$pageTitle = "A community where possibilities grow";
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();

// Fetch upcoming events for home page
$upcomingEvents = [];
try {
    $stmt = $pdo->query("SELECT * FROM events WHERE status = 'Upcoming' ORDER BY start_at ASC LIMIT 3");
    $upcomingEvents = $stmt->fetchAll();
} catch (Exception $e) {}

// Fetch modules
$modules = [];
try {
    $stmt = $pdo->query("SELECT * FROM modules ORDER BY sort_order ASC LIMIT 3");
    $modules = $stmt->fetchAll();
} catch (Exception $e) {}

// Fetch featured bazaar listings
$bazaarListings = [];
try {
    $stmt = $pdo->query("SELECT * FROM bazaar_listings WHERE status = 'published' ORDER BY is_featured DESC, id DESC LIMIT 3");
    $bazaarListings = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<!-- Hero Section -->
<section class="section section-hero">
  <div class="container">
    <div class="hero-grid">
      <div>
        <div class="card-category" style="margin-bottom: 12px;">
          <?= __('hero_tag', 'WOMEN. COMMUNITY. POSSIBILITY.') ?>
        </div>
        <h1 class="display-title" style="margin-bottom: 20px;">
          <?= __('hero_title', 'Your next chapter starts here.') ?>
        </h1>
        <p style="font-size: 18px; line-height: 1.6; margin-bottom: 28px;">
          <?= __('hero_subtitle', 'Build skills, find your people and turn your business dreams into everyday possibilities with Ibu Niaga.') ?>
        </p>
        <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
          <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-lg">
            <?= __('btn_join_community', 'Join our community') ?>
          </a>
          <a href="<?= BASE_URL ?>/events.php" class="btn btn-secondary btn-lg">
            <?= __('btn_browse_events', 'Browse events') ?>
          </a>
        </div>
        <div class="text-caption" style="margin-top: 20px; color: var(--color-muted);">
          A community initiative by Taylor's University
        </div>
      </div>

      <div>
        <img src="<?= BASE_URL ?>/assets/images/20 Sample Photography - Kitchen.png" alt="Ibu Niaga culinary community" class="hero-image">
      </div>
    </div>

    <!-- 4 Stat Metric Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <span class="stat-label">A growing community</span>
        <span class="stat-value">240+</span>
        <span class="stat-sub"><?= __('stat_women', 'Women connected') ?></span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Practical learning</span>
        <span class="stat-value">4</span>
        <span class="stat-sub"><?= __('stat_pathways', 'Skills pathways') ?></span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Shared experiences</span>
        <span class="stat-value">30+</span>
        <span class="stat-sub"><?= __('stat_events', 'Community events') ?></span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Made with heart</span>
        <span class="stat-value">85+</span>
        <span class="stat-sub"><?= __('stat_businesses', 'Local businesses') ?></span>
      </div>
    </div>
  </div>
</section>

<!-- Big Dreams Section -->
<section class="section" style="background-color: #fafafa; border-top: 1px solid var(--color-line); border-bottom: 1px solid var(--color-line);">
  <div class="container">
    <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 48px; align-items: center; margin-bottom: 48px;">
      <div>
        <h2>Big dreams grow<br>better together.</h2>
      </div>
      <div>
        <p style="font-size: 16px;">
          Ibu Niaga brings women entrepreneurs together through practical learning, emotional support and opportunities to showcase what they create. Wherever you are in your journey, there is a place for you here.
        </p>
      </div>
    </div>

    <!-- Upcoming Events Grid -->
    <div style="margin-bottom: 24px;">
      <h3 style="margin-bottom: 8px;">Make room for something new</h3>
      <p class="text-caption" style="font-size: 14px;">Discover events made for your next step.</p>
    </div>

    <div class="card-grid">
      <?php foreach ($upcomingEvents as $ev): ?>
        <div class="content-card">
          <img src="<?= BASE_URL ?>/<?= e($ev['poster_path'] ?: 'assets/images/20 Sample Photography - Kitchen.png') ?>" alt="<?= e($ev['title']) ?>" class="card-cover">
          <div class="card-body">
            <div class="card-category">
              <?= e($ev['category']) ?> · <?= date('d M Y', strtotime($ev['start_at'])) ?>
            </div>
            <div class="card-title"><?= e($ev['title']) ?></div>
            <div class="card-text"><?= e(substr($ev['description'], 0, 120)) ?>...</div>
            <div style="font-size: 13px; color: var(--color-muted); margin-top: auto;">
              <?= e($ev['venue']) ?>
            </div>
          </div>
          <div class="card-footer">
            <a href="<?= BASE_URL ?>/event_detail.php?id=<?= $ev['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
              View details →
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Skills Pathways Section -->
<section class="section">
  <div class="container">
    <div style="margin-bottom: 32px;">
      <h2 style="margin-bottom: 8px;">Skills for business. Confidence for life.</h2>
      <p style="font-size: 15px; color: var(--color-muted);">Learn with mentors who understand your journey.</p>
    </div>

    <div class="card-grid">
      <?php foreach ($modules as $mod): ?>
        <div class="content-card">
          <img src="<?= BASE_URL ?>/<?= e($mod['poster_path'] ?: 'assets/images/21 Sample Photography - Breakfast.png') ?>" alt="<?= e($mod['title']) ?>" class="card-cover">
          <div class="card-body">
            <div class="card-category">
              <?= e($mod['category']) ?> · <?= e($mod['lessons_count']) ?> LESSONS
            </div>
            <div class="card-title"><?= e($mod['title']) ?></div>
            <div class="card-text"><?= e($mod['overview']) ?></div>
          </div>
          <div class="card-footer">
            <a href="<?= BASE_URL ?>/module_detail.php?id=<?= $mod['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
              View details →
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Community Spotlight Quote Banner -->
    <div class="spotlight-banner">
      <div>
        <img src="<?= BASE_URL ?>/assets/images/22 Sample Photography - Community.png" alt="Aina Rahman" class="spotlight-img">
      </div>
      <div>
        <div class="card-category" style="margin-bottom: 8px;">COMMUNITY SPOTLIGHT</div>
        <div class="spotlight-quote">
          “I started with a small idea.<br>Now I believe in what comes next.”
        </div>
        <p style="font-size: 14px; color: var(--color-muted); margin-bottom: 16px;">
          Aina Rahman · Aina's Kitchen
        </p>
        <a href="<?= BASE_URL ?>/stories.php" class="btn btn-outline btn-sm">
          Read Aina's story →
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Open Bazaar Showcase -->
<section class="section" style="background-color: #fafafa; border-top: 1px solid var(--color-line);">
  <div class="container">
    <div style="margin-bottom: 32px;">
      <h2 style="margin-bottom: 8px;">Made by our community</h2>
      <p style="font-size: 15px; color: var(--color-muted);">Shop small. Support someone's next chapter.</p>
    </div>

    <div class="card-grid">
      <?php foreach ($bazaarListings as $item): ?>
        <?php 
          $photos = json_decode($item['photos_json'] ?? '[]', true);
          $photo = !empty($photos) ? $photos[0] : 'assets/images/21 Sample Photography - Breakfast.png';
        ?>
        <div class="content-card">
          <img src="<?= BASE_URL ?>/<?= e($photo) ?>" alt="<?= e($item['title']) ?>" class="card-cover">
          <div class="card-body">
            <div class="card-category"><?= e($item['category']) ?></div>
            <div class="card-title"><?= e($item['title']) ?></div>
            <div class="card-text"><?= e(substr($item['description'], 0, 110)) ?>...</div>
            <div style="font-size: 14px; font-weight: 700; color: var(--color-ink); margin-top: auto;">
              <?= e($item['price_text']) ?> · <?= e($item['location']) ?>
            </div>
          </div>
          <div class="card-footer">
            <a href="<?= BASE_URL ?>/listing_detail.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
              View details →
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Peach CTA Callout -->
    <div class="callout-peach">
      <div>
        <h3 style="margin-bottom: 6px;">You don't have to do it all alone.</h3>
        <p style="color: var(--color-muted); font-size: 15px;">
          Join a community that learns together, supports one another and celebrates every milestone.
        </p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-lg">
          Register today
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
