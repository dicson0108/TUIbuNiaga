<?php
/**
 * Open Bazaar Discovery Page
 * Matches Figma: 08 Open Bazaar.png
 */

$pageTitle = "Open Bazaar";
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT b.*, u.name as seller_name, u.business_name FROM bazaar_listings b 
        JOIN users u ON b.user_id = u.id 
        WHERE b.status = 'published'";
$params = [];

if ($search !== '') {
    $sql .= " AND (b.title LIKE ? OR b.description LIKE ? OR b.location LIKE ? OR u.business_name LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($category !== '') {
    $sql .= " AND b.category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY b.is_featured DESC, b.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();
?>

<section class="section">
  <div class="container">
    <div style="max-width: 760px; margin-bottom: 32px;">
      <div class="card-category" style="margin-bottom: 12px;">COMMUNITY MARKETPLACE & SHOWCASE</div>
      <h1 class="display-title" style="margin-bottom: 16px;">Open Bazaar</h1>
      <p style="font-size: 18px; color: var(--color-muted); line-height: 1.6;">
        Discover culinary delights, fresh artisan baked goods, handcrafted delicacies, and bespoke catering services proudly made by Ibu Niaga members. Direct inquiries, no intermediary commissions.
      </p>
    </div>

    <!-- Search & Filter Controls -->
    <form method="GET" action="<?= BASE_URL ?>/bazaar.php" style="background: var(--color-sage); padding: 20px; border-radius: var(--radius-md); margin-bottom: 40px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
      <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search products, services, or sellers..." class="form-input" style="flex: 1; min-width: 240px; background: #fff;">
      
      <select name="category" class="form-select" style="width: auto; min-width: 180px; background: #fff;">
        <option value="">All Categories</option>
        <option value="Food & Beverages" <?= ($category === 'Food & Beverages') ? 'selected' : '' ?>>Food & Beverages</option>
        <option value="Home Bakery" <?= ($category === 'Home Bakery') ? 'selected' : '' ?>>Home Bakery</option>
        <option value="Services & Catering" <?= ($category === 'Services & Catering') ? 'selected' : '' ?>>Services & Catering</option>
      </select>

      <button type="submit" class="btn btn-primary">Filter</button>
      <?php if ($search || $category): ?>
        <a href="<?= BASE_URL ?>/bazaar.php" class="btn btn-outline" style="background: #fff;">Reset</a>
      <?php endif; ?>

      <?php if (is_logged_in()): ?>
        <a href="<?= BASE_URL ?>/member/listing_editor.php" class="btn btn-primary" style="margin-left: auto;">
          + Post a listing
        </a>
      <?php endif; ?>
    </form>

    <div class="card-grid">
      <?php if (empty($listings)): ?>
        <div style="grid-column: 1 / -1; padding: 48px; text-align: center; color: var(--color-muted); background: #fafafa; border-radius: var(--radius-md); border: 1px dashed var(--color-line);">
          No bazaar listings found matching your search.
        </div>
      <?php else: ?>
        <?php foreach ($listings as $item): ?>
          <?php 
            $photos = json_decode($item['photos_json'] ?? '[]', true);
            $photo = !empty($photos) ? $photos[0] : 'assets/images/21 Sample Photography - Breakfast.png';
          ?>
          <div class="content-card">
            <img src="<?= BASE_URL ?>/<?= e($photo) ?>" alt="<?= e($item['title']) ?>" class="card-cover">
            <div class="card-body">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-category"><?= e($item['category']) ?></div>
                <div class="text-caption"><?= e($item['location']) ?></div>
              </div>

              <div class="card-title"><?= e($item['title']) ?></div>
              <div style="font-size: 13px; font-weight: 600; color: var(--color-primary); margin-top: -4px;">
                by <?= e($item['business_name'] ?: $item['seller_name']) ?>
              </div>
              <div class="card-text"><?= e(substr($item['description'], 0, 110)) ?>...</div>

              <div style="font-size: 16px; font-weight: 700; color: var(--color-ink); margin-top: auto; padding-top: 10px; border-top: 1px solid var(--color-line);">
                <?= e($item['price_text']) ?>
              </div>
            </div>
            <div class="card-footer">
              <a href="<?= BASE_URL ?>/listing_detail.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
                View details & enquire →
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
