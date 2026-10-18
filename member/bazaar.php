<?php
/**
 * Member Bazaar Listings Management
 * Matches Figma: 02 Member workspace/12 My Bazaar Listings.png & 14 Delete Listing.png
 */

$pageTitle = "My Bazaar Listings";
require_once __DIR__ . '/../includes/user_header.php';
$pdo = get_db();
$userId = $user['id'];

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    verify_csrf();
    $listId = (int)$_POST['listing_id'];
    $stmtDel = $pdo->prepare("DELETE FROM bazaar_listings WHERE id = ? AND user_id = ?");
    $stmtDel->execute([$listId, $userId]);
    log_audit('DELETE_LISTING', 'bazaar_listings', $listId, "Deleted listing #{$listId}");
    set_flash('info', 'Your listing has been removed from the Open Bazaar.');
    header('Location: ' . BASE_URL . '/member/bazaar.php');
    exit;
}

// Fetch all listings for user
$stmtList = $pdo->prepare("SELECT b.*, 
    (SELECT COUNT(*) FROM enquiries e WHERE e.listing_id = b.id) as enquiry_count
    FROM bazaar_listings b 
    WHERE b.user_id = ? 
    ORDER BY b.id DESC");
$stmtList->execute([$userId]);
$listings = $stmtList->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">My Bazaar Listings</h1>
    <p style="color: var(--color-muted);">Manage your active products, services, photos, and customer visibility in the Open Bazaar.</p>
  </div>
  <a href="<?= BASE_URL ?>/member/listing_editor.php" class="btn btn-primary">
    + Add New Listing
  </a>
</div>

<?php if (empty($listings)): ?>
  <div style="background: #fff; border: 1px dashed var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center;">
    <h3 style="margin-bottom: 8px;">No listings created yet</h3>
    <p style="color: var(--color-muted); font-size: 15px; margin-bottom: 24px;">
      Publish your baked goods, delicacies, or catering packages to reach the Taylor's community.
    </p>
    <a href="<?= BASE_URL ?>/member/listing_editor.php" class="btn btn-primary">
      Create Your First Listing
    </a>
  </div>
<?php else: ?>
  <div class="table-wrapper">
    <div class="table-header-bar">
      <div style="font-size: 16px; font-weight: 700;">Listings (<?= count($listings) ?>)</div>
      <input type="text" placeholder="Search my listings..." class="table-search-input">
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Listing</th>
          <th>Category</th>
          <th>Price</th>
          <th>Status</th>
          <th>Views / Enquiries</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($listings as $item): ?>
          <?php 
            $photos = json_decode($item['photos_json'] ?? '[]', true);
            $photo = !empty($photos) ? $photos[0] : 'assets/images/21 Sample Photography - Breakfast.png';
          ?>
          <tr>
            <td>
              <div style="display: flex; align-items: center; gap: 14px;">
                <img src="<?= BASE_URL ?>/<?= e($photo) ?>" alt="<?= e($item['title']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid var(--color-line);">
                <div>
                  <div style="font-weight: 700; color: var(--color-ink);"><?= e($item['title']) ?></div>
                  <div class="text-caption"><?= e($item['location']) ?></div>
                </div>
              </div>
            </td>
            <td><?= e($item['category']) ?></td>
            <td style="font-weight: 700; color: var(--color-ink);"><?= e($item['price_text']) ?></td>
            <td><?= status_badge($item['status']) ?></td>
            <td style="color: var(--color-muted); font-size: 13px;">
              👁️ <?= (int)$item['views_count'] ?> views &nbsp;·&nbsp; 💬 <?= (int)$item['enquiry_count'] ?> enquiries
            </td>
            <td style="text-align: right;">
              <div style="display: inline-flex; gap: 8px;">
                <a href="<?= BASE_URL ?>/listing_detail.php?id=<?= $item['id'] ?>" target="_blank" class="btn btn-outline btn-sm" title="Preview public listing">
                  Preview ↗
                </a>
                <a href="<?= BASE_URL ?>/member/listing_editor.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">
                  Edit
                </a>
                <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this listing? This action cannot be undone.');" style="display: inline;">
                  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="listing_id" value="<?= $item['id'] ?>">
                  <button type="submit" class="btn btn-destructive btn-sm">
                    Delete
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
