<?php
/**
 * Success Stories Page
 * Matches Figma: 05 Success Stories.png
 */

$pageTitle = "Success Stories";
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();

$stories = [];
try {
    $stmt = $pdo->query("SELECT s.*, m.name as member_name, m.business_name FROM success_stories s LEFT JOIN members m ON s.member_id = m.id ORDER BY s.published_at DESC");
    $stories = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<section class="section">
  <div class="container">
    <div style="max-width: 760px; margin-bottom: 40px;">
      <div class="card-category" style="margin-bottom: 12px;">VOICES OF TRANSFORMATION</div>
      <h1 class="display-title" style="margin-bottom: 16px;">Success Stories</h1>
      <p style="font-size: 18px; color: var(--color-muted); line-height: 1.6;">
        Real journeys of mothers and micro-entrepreneurs who transformed their passions into resilient businesses with Ibu Niaga.
      </p>
    </div>

    <!-- Featured Spotlight Story -->
    <?php if (!empty($stories)): ?>
      <?php $featured = $stories[0]; ?>
      <div class="spotlight-banner" style="margin-top: 0; margin-bottom: 48px;">
        <div>
          <img src="<?= BASE_URL ?>/<?= e($featured['photo_path'] ?: 'assets/images/22 Sample Photography - Community.png') ?>" alt="<?= e($featured['title']) ?>" class="spotlight-img" style="height: 320px;">
        </div>
        <div>
          <div class="card-category" style="margin-bottom: 8px;">FEATURED STORY</div>
          <h2 style="font-size: 26px; margin-bottom: 12px;"><?= e($featured['title']) ?></h2>
          <div class="spotlight-quote" style="font-size: 20px; color: var(--color-primary); margin-bottom: 14px;">
            “<?= e($featured['quote']) ?>”
          </div>
          <p style="font-size: 15px; color: var(--color-muted); margin-bottom: 20px;">
            <?= e(substr($featured['body'], 0, 180)) ?>...
          </p>
          <a href="<?= BASE_URL ?>/story_detail.php?id=<?= $featured['id'] ?>" class="btn btn-primary">
            Read full story →
          </a>
        </div>
      </div>
    <?php endif; ?>

    <div class="card-grid">
      <?php foreach ($stories as $story): ?>
        <div class="content-card">
          <img src="<?= BASE_URL ?>/<?= e($story['photo_path'] ?: 'assets/images/22 Sample Photography - Community.png') ?>" alt="<?= e($story['title']) ?>" class="card-cover">
          <div class="card-body">
            <div class="card-category">
              <?= e($story['member_name']) ?> · <?= e($story['business_name']) ?>
            </div>
            <div class="card-title"><?= e($story['title']) ?></div>
            <div style="font-style: italic; font-weight: 500; color: var(--color-primary); font-size: 14px;">
              “<?= e($story['quote']) ?>”
            </div>
            <div class="card-text"><?= e(substr($story['body'], 0, 130)) ?>...</div>
          </div>
          <div class="card-footer">
            <a href="<?= BASE_URL ?>/story_detail.php?id=<?= $story['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
              Read story →
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
