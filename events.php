<?php
/**
 * Events Directory & Discovery Page
 * Matches Figma: 07 Events.png
 */

$pageTitle = "Events & Workshops";
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id AND r.status = 'joined') as joined_count
        FROM events e WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (e.title LIKE ? OR e.description LIKE ? OR e.venue LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
}

if ($category !== '') {
    $sql .= " AND e.category = ?";
    $params[] = $category;
}

if ($type !== '') {
    $sql .= " AND e.type = ?";
    $params[] = $type;
}

$sql .= " ORDER BY e.start_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();
?>

<section class="section">
  <div class="container">
    <div style="max-width: 760px; margin-bottom: 32px;">
      <div class="card-category" style="margin-bottom: 12px;">COMMUNITY GATHERINGS & SESSIONS</div>
      <h1 class="display-title" style="margin-bottom: 16px;">Events & Workshops</h1>
      <p style="font-size: 18px; color: var(--color-muted); line-height: 1.6;">
        Discover interactive sessions, hands-on masterclasses at Taylor's culinary studios, and digital webinars. One-click registration with automatic waitlist management.
      </p>
    </div>

    <!-- Search & Filter Controls -->
    <form method="GET" action="<?= BASE_URL ?>/events.php" style="background: var(--color-sage); padding: 20px; border-radius: var(--radius-md); margin-bottom: 40px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
      <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search events by title or keyword..." class="form-input" style="flex: 1; min-width: 240px; background: #fff;">
      
      <select name="category" class="form-select" style="width: auto; min-width: 160px; background: #fff;">
        <option value="">All Categories</option>
        <option value="Workshop" <?= ($category === 'Workshop') ? 'selected' : '' ?>>Workshop</option>
        <option value="Financial Literacy" <?= ($category === 'Financial Literacy') ? 'selected' : '' ?>>Financial Literacy</option>
        <option value="Community" <?= ($category === 'Community') ? 'selected' : '' ?>>Community</option>
      </select>

      <select name="type" class="form-select" style="width: auto; min-width: 140px; background: #fff;">
        <option value="">All Types</option>
        <option value="Physical" <?= ($type === 'Physical') ? 'selected' : '' ?>>Physical</option>
        <option value="Online" <?= ($type === 'Online') ? 'selected' : '' ?>>Online</option>
        <option value="Hybrid" <?= ($type === 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
      </select>

      <button type="submit" class="btn btn-primary">Filter</button>
      <?php if ($search || $category || $type): ?>
        <a href="<?= BASE_URL ?>/events.php" class="btn btn-outline" style="background: #fff;">Reset</a>
      <?php endif; ?>
    </form>

    <div class="card-grid">
      <?php if (empty($events)): ?>
        <div style="grid-column: 1 / -1; padding: 48px; text-align: center; color: var(--color-muted); background: #fafafa; border-radius: var(--radius-md); border: 1px dashed var(--color-line);">
          No events found matching your search.
        </div>
      <?php else: ?>
        <?php foreach ($events as $ev): ?>
          <?php 
            $isFull = ($ev['joined_count'] >= $ev['capacity']);
            $spotsLeft = max(0, $ev['capacity'] - $ev['joined_count']);
          ?>
          <div class="content-card">
            <img src="<?= BASE_URL ?>/<?= e($ev['poster_path'] ?: 'assets/images/20 Sample Photography - Kitchen.png') ?>" alt="<?= e($ev['title']) ?>" class="card-cover">
            <div class="card-body">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-category">
                  <?= e($ev['category']) ?> · <?= date('d M Y', strtotime($ev['start_at'])) ?>
                </div>
                <?php if ($isFull): ?>
                  <span class="badge badge-attention">Waitlist Active</span>
                <?php else: ?>
                  <span class="badge badge-positive"><?= $spotsLeft ?> spots left</span>
                <?php endif; ?>
              </div>

              <div class="card-title"><?= e($ev['title']) ?></div>
              <div class="card-text"><?= e(substr($ev['description'], 0, 110)) ?>...</div>
              
              <div style="font-size: 13px; color: var(--color-muted); margin-top: auto; padding-top: 10px; border-top: 1px solid var(--color-line);">
                📍 <?= e($ev['venue']) ?>
              </div>
            </div>
            <div class="card-footer">
              <a href="<?= BASE_URL ?>/event_detail.php?id=<?= $ev['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
                View event details →
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
