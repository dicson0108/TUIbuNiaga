<?php
/**
 * Bazaar Listing Editor (Create / Edit)
 * Matches Figma: 02 Member workspace/13 Listing Editor.png
 */

require_once __DIR__ . '/../includes/user_header.php';
$pdo = get_db();
$userId = $user['id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$listing = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM bazaar_listings WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    $listing = $stmt->fetch();
}

$pageTitle = $listing ? "Edit Listing · " . $listing['title'] : "Add New Listing";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $priceText = trim($_POST['price_text'] ?? '');
    $location = trim($_POST['location'] ?? 'Subang Jaya');
    $description = trim($_POST['description'] ?? '');

    if (!$title || !$category || !$priceText || !$description) {
        $errors[] = 'Please provide all mandatory fields (Title, Category, Price, and Description).';
    }

    if (empty($errors)) {
        // Photos JSON
        $photos = $listing ? json_decode($listing['photos_json'] ?? '[]', true) : [];
        if (!is_array($photos)) $photos = [];

        // Handle photo upload
        if (!empty($_FILES['photo']['name'])) {
            $res = handle_file_upload($_FILES['photo'], 'uploads/photos/', ['jpg', 'jpeg', 'png']);
            if ($res['success']) {
                array_unshift($photos, $res['file_path']);
            } else {
                $errors[] = 'Photo upload error: ' . $res['error'];
            }
        }

        // Fallback default image if none uploaded
        if (empty($photos)) {
            $photos[] = 'assets/images/21 Sample Photography - Breakfast.png';
        }

        // Check moderation setting (User choice: Configurable in Admin Settings with 'Require Moderator Approval' enabled by default)
        $approvalReq = get_setting('bazaar_approval_required', '1');
        $status = ($approvalReq == '1') ? 'pending' : 'published';

        if ($listing) {
            $stmtUp = $pdo->prepare("UPDATE bazaar_listings SET 
                title = ?, category = ?, price_text = ?, location = ?, description = ?, photos_json = ?, status = ?
                WHERE id = ? AND user_id = ?");
            $stmtUp->execute([
                $title, $category, $priceText, $location, $description, json_encode($photos), $status, $listing['id'], $userId
            ]);
            log_audit('UPDATE_LISTING', 'bazaar_listings', $listing['id'], "Updated listing: {$title}");
            set_flash('success', $status === 'pending' ? 'Listing updated! Submitted to moderators for review.' : 'Listing updated and published!');
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO bazaar_listings 
                (user_id, title, category, price_text, location, description, photos_json, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmtIns->execute([
                $userId, $title, $category, $priceText, $location, $description, json_encode($photos), $status
            ]);
            $newId = $pdo->lastInsertId();
            log_audit('CREATE_LISTING', 'bazaar_listings', $newId, "Created listing: {$title}");
            set_flash('success', $status === 'pending' ? 'New listing created! Submitted to moderators for review.' : 'Your listing is now live in the Open Bazaar!');
        }

        header('Location: ' . BASE_URL . '/member/bazaar.php');
        exit;
    }
}
?>

<div style="max-width: 760px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/member/bazaar.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to my listings</a>
  </div>

  <div style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin-bottom: 6px;"><?= $listing ? 'Edit Listing' : 'Add New Listing' ?></h1>
    <p style="color: var(--color-muted);">
      Create or update your showcase item. Listings are displayed to campus visitors and potential buyers.
    </p>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <ul style="margin-left: 20px;">
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="form-card" style="max-width: 100%;">
    <form method="POST" action="" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div class="form-group">
        <label class="form-label">Listing Title *</label>
        <input type="text" name="title" value="<?= e($listing['title'] ?? '') ?>" class="form-input" required placeholder="e.g. Homemade breakfast box or Artisan sourdough">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Category *</label>
          <select name="category" class="form-select" required>
            <option value="Food & Beverages" <?= (($listing['category'] ?? '') === 'Food & Beverages') ? 'selected' : '' ?>>Food & Beverages</option>
            <option value="Home Bakery" <?= (($listing['category'] ?? '') === 'Home Bakery') ? 'selected' : '' ?>>Home Bakery</option>
            <option value="Services & Catering" <?= (($listing['category'] ?? '') === 'Services & Catering') ? 'selected' : '' ?>>Services & Catering</option>
            <option value="Handmade Crafts" <?= (($listing['category'] ?? '') === 'Handmade Crafts') ? 'selected' : '' ?>>Handmade Crafts</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Price / Pricing Display *</label>
          <input type="text" name="price_text" value="<?= e($listing['price_text'] ?? 'From RM 25') ?>" class="form-input" required placeholder="e.g. From RM 25 or Request a quote">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Location / Pickup Area</label>
        <input type="text" name="location" value="<?= e($listing['location'] ?? 'Subang Jaya · Taylor\'s Lakeside') ?>" class="form-input" placeholder="e.g. Subang Jaya or Campus pickup">
      </div>

      <div class="form-group">
        <label class="form-label">Detailed Description *</label>
        <textarea name="description" class="form-textarea" required style="min-height: 140px;" placeholder="Describe what makes this item special, ingredients used, order lead time, or package details..."><?= e($listing['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Product / Service Photo</label>
        <input type="file" name="photo" class="form-input" accept=".jpg,.jpeg,.png">
        <div class="form-hint">Accepted formats: JPG, PNG. Recommended dimension: 800x600 px.</div>

        <?php if ($listing && !empty($listing['photos_json'])): ?>
          <?php $currentPhotos = json_decode($listing['photos_json'], true); ?>
          <?php if (!empty($currentPhotos)): ?>
            <div style="margin-top: 12px; display: flex; gap: 10px; align-items: center;">
              <span class="text-caption">Current photo:</span>
              <img src="<?= BASE_URL ?>/<?= e($currentPhotos[0]) ?>" alt="Current preview" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid var(--color-line);">
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--color-line);">
        <a href="<?= BASE_URL ?>/member/bazaar.php" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
          <?= $listing ? 'Save Changes' : 'Publish Listing' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
