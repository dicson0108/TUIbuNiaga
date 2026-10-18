<?php
/**
 * Story Detail Page
 * Matches Figma: 10 Story Detail.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT s.*, m.name as member_name, m.business_name, m.location, m.id as mem_id FROM success_stories s LEFT JOIN members m ON s.member_id = m.id WHERE s.id = ?");
$stmt->execute([$id]);
$story = $stmt->fetch();

if (!$story) {
    header("Location: " . BASE_URL . "/stories.php");
    exit;
}

$pageTitle = $story['title'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 820px;">
    <div style="margin-bottom: 24px;">
      <a href="<?= BASE_URL ?>/stories.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to all stories</a>
    </div>

    <div class="card-category" style="margin-bottom: 8px;">
      <?= e($story['member_name']) ?> · <?= e($story['business_name']) ?> · <?= date('M Y', strtotime($story['published_at'])) ?>
    </div>

    <h1 class="display-title" style="margin-bottom: 20px; font-size: 38px;"><?= e($story['title']) ?></h1>

    <div style="font-size: 22px; font-weight: 600; color: var(--color-primary); line-height: 1.4; border-left: 4px solid var(--color-primary); padding-left: 20px; margin: 24px 0 32px; font-style: italic;">
      “<?= e($story['quote']) ?>”
    </div>

    <img src="<?= BASE_URL ?>/<?= e($story['photo_path'] ?: 'assets/images/22 Sample Photography - Community.png') ?>" alt="<?= e($story['title']) ?>" style="width: 100%; height: 440px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 32px; border: 1px solid var(--color-line);">

    <div style="font-size: 17px; line-height: 1.8; color: var(--color-ink); margin-bottom: 48px;">
      <?= nl2br(e($story['body'])) ?>
    </div>

    <?php if (!empty($story['mem_id'])): ?>
      <div class="callout-peach">
        <div>
          <h3>Support <?= e($story['business_name']) ?></h3>
          <p style="color: var(--color-muted); font-size: 14px;">Browse their offerings in the Open Bazaar and explore their community profile.</p>
        </div>
        <div>
          <a href="<?= BASE_URL ?>/member_profile.php?id=<?= $story['mem_id'] ?>" class="btn btn-primary">
            View Member Profile
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
