<?php
/**
 * Members Directory Page
 * Matches Figma: 04 Members Directory.png
 */

$pageTitle = "Members Directory";
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM members WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR business_name LIKE ? OR bio LIKE ? OR location LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY featured DESC, id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();
?>

<section class="section">
  <div class="container">
    <div style="max-width: 760px; margin-bottom: 32px;">
      <div class="card-category" style="margin-bottom: 12px;">OUR COMMUNITY</div>
      <h1 class="display-title" style="margin-bottom: 16px;">Members Directory</h1>
      <p style="font-size: 18px; color: var(--color-muted); line-height: 1.6;">
        Meet the talented women, passionate artisans, and home-based business owners in the Ibu Niaga ecosystem.
      </p>
    </div>

    <!-- Search & Filter Bar -->
    <form method="GET" action="<?= BASE_URL ?>/members.php" style="background: var(--color-sage); padding: 20px; border-radius: var(--radius-md); margin-bottom: 40px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
      <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name, business, or keyword..." class="form-input" style="flex: 1; min-width: 240px; background: #fff;">
      
      <select name="category" class="form-select" style="width: auto; min-width: 180px; background: #fff;">
        <option value="">All Categories</option>
        <option value="Food & Beverages" <?= ($category === 'Food & Beverages') ? 'selected' : '' ?>>Food & Beverages</option>
        <option value="Home Bakery" <?= ($category === 'Home Bakery') ? 'selected' : '' ?>>Home Bakery</option>
        <option value="Services & Catering" <?= ($category === 'Services & Catering') ? 'selected' : '' ?>>Services & Catering</option>
      </select>

      <button type="submit" class="btn btn-primary">Filter</button>
      <?php if ($search || $category): ?>
        <a href="<?= BASE_URL ?>/members.php" class="btn btn-outline" style="background: #fff;">Reset</a>
      <?php endif; ?>
    </form>

    <div class="card-grid">
      <?php if (empty($members)): ?>
        <div style="grid-column: 1 / -1; padding: 48px; text-align: center; color: var(--color-muted); background: #fafafa; border-radius: var(--radius-md); border: 1px dashed var(--color-line);">
          No members found matching your search.
        </div>
      <?php else: ?>
        <?php foreach ($members as $mem): ?>
          <div class="content-card">
            <img src="<?= BASE_URL ?>/<?= e($mem['photo_path'] ?: 'assets/images/22 Sample Photography - Community.png') ?>" alt="<?= e($mem['name']) ?>" class="card-cover">
            <div class="card-body">
              <div class="card-category"><?= e($mem['category']) ?> · <?= e($mem['location']) ?></div>
              <div class="card-title"><?= e($mem['name']) ?></div>
              <div style="font-size: 15px; font-weight: 600; color: var(--color-primary); margin-top: -4px;">
                <?= e($mem['business_name']) ?>
              </div>
              <div class="card-text"><?= e(substr($mem['bio'], 0, 140)) ?>...</div>
            </div>
            <div class="card-footer">
              <a href="<?= BASE_URL ?>/member_profile.php?id=<?= $mem['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
                View member profile →
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
