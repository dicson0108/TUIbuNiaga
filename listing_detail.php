<?php
/**
 * Listing Detail Page
 * Matches Figma: 15 Listing Detail.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT b.*, u.name as seller_name, u.business_name, u.email as seller_email, u.phone as seller_phone, u.bio as seller_bio FROM bazaar_listings b JOIN users u ON b.user_id = u.id WHERE b.id = ?");
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing) {
    header("Location: " . BASE_URL . "/bazaar.php");
    exit;
}

// Increment views count
$pdo->prepare("UPDATE bazaar_listings SET views_count = views_count + 1 WHERE id = ?")->execute([$id]);

$photos = json_decode($listing['photos_json'] ?? '[]', true);
$mainPhoto = !empty($photos) ? $photos[0] : 'assets/images/21 Sample Photography - Breakfast.png';

$pageTitle = $listing['title'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 960px;">
    <div style="margin-bottom: 24px;">
      <a href="<?= BASE_URL ?>/bazaar.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to Open Bazaar</a>
    </div>

    <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 40px; align-items: start; margin-bottom: 48px;">
      <!-- Photos -->
      <div>
        <img src="<?= BASE_URL ?>/<?= e($mainPhoto) ?>" alt="<?= e($listing['title']) ?>" style="width: 100%; height: 380px; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--color-line); margin-bottom: 16px;">
        
        <?php if (count($photos) > 1): ?>
          <div style="display: flex; gap: 12px; overflow-x: auto;">
            <?php foreach ($photos as $p): ?>
              <img src="<?= BASE_URL ?>/<?= e($p) ?>" alt="Photo thumbnail" style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--color-line);">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Listing Details -->
      <div>
        <div class="card-category" style="margin-bottom: 8px;">
          <?= e($listing['category']) ?> · <?= e($listing['location']) ?>
        </div>
        <h1 style="font-size: 32px; margin-bottom: 12px;"><?= e($listing['title']) ?></h1>
        
        <div style="font-size: 28px; font-weight: 800; color: var(--color-ink); margin-bottom: 20px;">
          <?= e($listing['price_text']) ?>
        </div>

        <div style="background: var(--color-cream); border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 20px; margin-bottom: 24px;">
          <div class="text-caption" style="margin-bottom: 4px;">Seller Information</div>
          <div style="font-size: 16px; font-weight: 700; color: var(--color-ink);">
            <?= e($listing['business_name'] ?: $listing['seller_name']) ?>
          </div>
          <div style="font-size: 14px; color: var(--color-muted); margin-top: 4px;">
            <?= e($listing['seller_bio'] ?: 'Proud verified member of Taylor\'s Ibu Niaga initiative.') ?>
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
          <a href="<?= BASE_URL ?>/enquiry.php?id=<?= $listing['id'] ?>" class="btn btn-primary btn-lg" style="width: 100%;">
            Send Enquiry / Order Request
          </a>
          <button onclick="navigator.clipboard.writeText(window.location.href); alert('Shareable listing link copied to clipboard!');" class="btn btn-outline" style="width: 100%;">
            🔗 Copy Shareable Link
          </button>
        </div>
      </div>
    </div>

    <!-- Long Description -->
    <div style="border-top: 1px solid var(--color-line); padding-top: 36px;">
      <h2 style="margin-bottom: 16px;">Product / Service Description</h2>
      <div style="font-size: 16px; line-height: 1.8; color: var(--color-ink);">
        <?= nl2br(e($listing['description'])) ?>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
