<?php
/**
 * Member Profile Page
 * Matches Figma: 11 Member Profile.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    header("Location: " . BASE_URL . "/members.php");
    exit;
}

$pageTitle = $member['name'] . " (" . $member['business_name'] . ")";
require_once __DIR__ . '/includes/header.php';

// Fetch this member's bazaar listings if user_id linked
$listings = [];
if (!empty($member['user_id'])) {
    $stmtBaz = $pdo->prepare("SELECT * FROM bazaar_listings WHERE user_id = ? AND status = 'published'");
    $stmtBaz->execute([$member['user_id']]);
    $listings = $stmtBaz->fetchAll();
}
?>

<section class="section">
  <div class="container" style="max-width: 900px;">
    <div style="margin-bottom: 24px;">
      <a href="<?= BASE_URL ?>/members.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to Members Directory</a>
    </div>

    <div style="display: grid; grid-template-columns: 240px 1fr; gap: 36px; align-items: start; background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 36px; margin-bottom: 40px;">
      <div>
        <img src="<?= BASE_URL ?>/<?= e($member['photo_path'] ?: 'assets/images/21 Sample Photography - Breakfast.png') ?>" alt="<?= e($member['name']) ?>" style="width: 100%; height: 240px; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--color-line);">
        <div style="margin-top: 16px; text-align: center;">
          <span class="badge badge-positive">Verified Member</span>
        </div>
      </div>

      <div>
        <div class="card-category" style="margin-bottom: 6px;"><?= e($member['category']) ?> · <?= e($member['location']) ?></div>
        <h1 style="font-size: 32px; margin-bottom: 8px;"><?= e($member['name']) ?></h1>
        <div style="font-size: 18px; font-weight: 600; color: var(--color-primary); margin-bottom: 16px;">
          <?= e($member['business_name']) ?>
        </div>
        <p style="font-size: 15px; line-height: 1.6; color: var(--color-ink); margin-bottom: 20px;">
          <?= nl2br(e($member['bio'])) ?>
        </p>

        <div style="border-top: 1px solid var(--color-line); padding-top: 16px; display: flex; gap: 16px; flex-wrap: wrap;">
          <a href="<?= BASE_URL ?>/bazaar.php?category=<?= urlencode($member['category']) ?>" class="btn btn-outline btn-sm">
            Browse Category Listings
          </a>
          <a href="<?= BASE_URL ?>/contact.php" class="btn btn-secondary btn-sm">
            Contact University Coordinator
          </a>
        </div>
      </div>
    </div>

    <!-- Active Listings by this member -->
    <div style="margin-bottom: 24px;">
      <h2>Offerings by <?= e($member['business_name']) ?></h2>
    </div>

    <?php if (empty($listings)): ?>
      <div style="padding: 32px; text-align: center; color: var(--color-muted); background: #fafafa; border-radius: var(--radius-md); border: 1px dashed var(--color-line);">
        No active public listings currently published by this member.
      </div>
    <?php else: ?>
      <div class="card-grid">
        <?php foreach ($listings as $item): ?>
          <?php 
            $photos = json_decode($item['photos_json'] ?? '[]', true);
            $photo = !empty($photos) ? $photos[0] : 'assets/images/21 Sample Photography - Breakfast.png';
          ?>
          <div class="content-card">
            <img src="<?= BASE_URL ?>/<?= e($photo) ?>" alt="<?= e($item['title']) ?>" class="card-cover">
            <div class="card-body">
              <div class="card-category"><?= e($item['category']) ?></div>
              <div class="card-title"><?= e($item['title']) ?></div>
              <div class="card-text"><?= e(substr($item['description'], 0, 100)) ?>...</div>
              <div style="font-size: 14px; font-weight: 700; color: var(--color-ink); margin-top: auto;">
                <?= e($item['price_text']) ?>
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
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
