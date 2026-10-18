<?php
/**
 * Moderator Open Bazaar Moderation Queue
 * Matches Figma: 03 Moderator workspace/06 Bazaar Moderation.png
 */

$pageTitle = "Bazaar Moderation";
require_once __DIR__ . '/../includes/mod_header.php';
$pdo = get_db();

// Handle moderation actions (Approve, Reject, Unpublish)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();
    $listId = (int)$_POST['listing_id'];
    $act = $_POST['action'];

    $stmtGet = $pdo->prepare("SELECT b.*, u.email as seller_email, u.name as seller_name FROM bazaar_listings b JOIN users u ON b.user_id = u.id WHERE b.id = ?");
    $stmtGet->execute([$listId]);
    $listing = $stmtGet->fetch();

    if ($listing) {
        if ($act === 'approve') {
            $pdo->prepare("UPDATE bazaar_listings SET status = 'published' WHERE id = ?")->execute([$listId]);
            create_notification($listing['user_id'], 'bazaar', 'Listing Approved!', "Your bazaar listing '{$listing['title']}' has been approved and is now live to the public.", "listing_detail.php?id={$listId}");
            log_audit('APPROVE_LISTING', 'bazaar_listings', $listId, "Approved listing #{$listId}");
            set_flash('success', "Listing '{$listing['title']}' approved and published.");
        } elseif ($act === 'reject') {
            $pdo->prepare("UPDATE bazaar_listings SET status = 'rejected' WHERE id = ?")->execute([$listId]);
            create_notification($listing['user_id'], 'bazaar', 'Listing Not Approved', "Your bazaar listing '{$listing['title']}' was reviewed and requires modifications.", "member/bazaar.php");
            log_audit('REJECT_LISTING', 'bazaar_listings', $listId, "Rejected listing #{$listId}");
            set_flash('info', "Listing marked as rejected.");
        } elseif ($act === 'unpublish') {
            $pdo->prepare("UPDATE bazaar_listings SET status = 'archived' WHERE id = ?")->execute([$listId]);
            log_audit('UNPUBLISH_LISTING', 'bazaar_listings', $listId, "Unpublished listing #{$listId}");
            set_flash('info', "Listing unpublished from the Open Bazaar.");
        }
        header('Location: ' . BASE_URL . '/moderator/bazaar_moderation.php');
        exit;
    }
}

$statusFilter = $_GET['status'] ?? 'pending';

$sql = "SELECT b.*, u.name as seller_name, u.business_name, u.email as seller_email 
        FROM bazaar_listings b 
        JOIN users u ON b.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($statusFilter !== '') {
    $sql .= " AND b.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY b.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Bazaar Moderation</h1>
    <p style="color: var(--color-muted);">Review submitted product and service listings before they go live on the public campus marketplace.</p>
  </div>
</div>

<div style="display: flex; gap: 10px; margin-bottom: 24px; border-bottom: 1px solid var(--color-line); padding-bottom: 12px;">
  <a href="?status=pending" class="btn <?= ($statusFilter === 'pending') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Pending Review
  </a>
  <a href="?status=published" class="btn <?= ($statusFilter === 'published') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Published Active
  </a>
  <a href="?status=rejected" class="btn <?= ($statusFilter === 'rejected') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Rejected
  </a>
  <a href="?status=" class="btn <?= ($statusFilter === '') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    All
  </a>
</div>

<div class="table-wrapper">
  <table class="data-table">
    <thead>
      <tr>
        <th>Listing & Seller</th>
        <th>Category</th>
        <th>Price</th>
        <th>Status</th>
        <th>Submitted Date</th>
        <th style="text-align: right;">Moderation Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($listings)): ?>
        <tr>
          <td colspan="6" style="text-align: center; color: var(--color-muted); padding: 48px;">
            No listings currently in this moderation queue.
          </td>
        </tr>
      <?php else: ?>
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
                  <div class="text-caption">by <?= e($item['business_name'] ?: $item['seller_name']) ?> (<?= e($item['seller_email']) ?>)</div>
                </div>
              </div>
            </td>
            <td><?= e($item['category']) ?></td>
            <td style="font-weight: 700;"><?= e($item['price_text']) ?></td>
            <td><?= status_badge($item['status']) ?></td>
            <td style="color: var(--color-muted); font-size: 13px;">
              <?= date('d M Y, h:i A', strtotime($item['created_at'])) ?>
            </td>
            <td style="text-align: right;">
              <div style="display: inline-flex; gap: 6px;">
                <a href="<?= BASE_URL ?>/listing_detail.php?id=<?= $item['id'] ?>" target="_blank" class="btn btn-outline btn-sm">
                  View ↗
                </a>

                <?php if ($item['status'] === 'pending' || $item['status'] === 'rejected'): ?>
                  <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="listing_id" value="<?= $item['id'] ?>">
                    <button type="submit" class="btn btn-primary btn-sm" style="background: #2e7d32; border-color: #2e7d32;">
                      ✓ Approve
                    </button>
                  </form>
                <?php endif; ?>

                <?php if ($item['status'] === 'pending'): ?>
                  <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="listing_id" value="<?= $item['id'] ?>">
                    <button type="submit" class="btn btn-destructive btn-sm">
                      ✕ Reject
                    </button>
                  </form>
                <?php endif; ?>

                <?php if ($item['status'] === 'published'): ?>
                  <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="unpublish">
                    <input type="hidden" name="listing_id" value="<?= $item['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm">
                      Unpublish
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
