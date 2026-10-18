<?php
/**
 * Help Center Page
 * Matches Figma: 17 Help Center.png
 */

$pageTitle = "Help Center";
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM help_articles WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR body LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like]);
}

if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY sort_order ASC, id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();
?>

<section class="section">
  <div class="container" style="max-width: 900px;">
    <div style="text-align: center; max-width: 680px; margin: 0 auto 40px;">
      <div class="card-category" style="margin-bottom: 12px;">FAQS & GUIDELINES</div>
      <h1 class="display-title" style="margin-bottom: 16px;">How can we help you?</h1>
      <p style="font-size: 17px; color: var(--color-muted);">
        Find clear answers about program application requirements, event attendance, waitlists, and open bazaar selling.
      </p>

      <!-- Search Bar -->
      <form method="GET" action="<?= BASE_URL ?>/help.php" style="margin-top: 24px; display: flex; gap: 12px;">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search articles, keywords, or topics..." class="form-input" style="flex: 1; padding: 14px 18px; font-size: 15px;">
        <button type="submit" class="btn btn-primary btn-lg">Search</button>
      </form>
    </div>

    <!-- Category Pills -->
    <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 40px;">
      <a href="<?= BASE_URL ?>/help.php" class="btn <?= ($category === '') ? 'btn-primary' : 'btn-outline' ?> btn-sm">All Topics</a>
      <a href="<?= BASE_URL ?>/help.php?category=Program+Registration" class="btn <?= ($category === 'Program Registration') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Program Registration</a>
      <a href="<?= BASE_URL ?>/help.php?category=Events+%26+Workshops" class="btn <?= ($category === 'Events & Workshops') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Events & Workshops</a>
      <a href="<?= BASE_URL ?>/help.php?category=Open+Bazaar" class="btn <?= ($category === 'Open Bazaar') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Open Bazaar</a>
    </div>

    <!-- Articles List -->
    <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 48px;">
      <?php if (empty($articles)): ?>
        <div style="padding: 40px; text-align: center; color: var(--color-muted); background: #fafafa; border-radius: var(--radius-md); border: 1px dashed var(--color-line);">
          No help articles found matching your query.
        </div>
      <?php else: ?>
        <?php foreach ($articles as $art): ?>
          <a href="<?= BASE_URL ?>/help_article.php?id=<?= $art['id'] ?>" style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-sm); padding: 22px 28px; display: flex; justify-content: space-between; align-items: center; transition: all 0.15s ease;" onmouseover="this.style.borderColor='var(--color-primary)'" onmouseout="this.style.borderColor='var(--color-line)'">
            <div>
              <div class="card-category" style="margin-bottom: 4px;"><?= e($art['category']) ?></div>
              <div style="font-size: 17px; font-weight: 700; color: var(--color-ink);">
                <?= e($art['title']) ?>
              </div>
            </div>
            <div style="color: var(--color-primary); font-size: 18px; font-weight: 700;">
              →
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Ticket support callout -->
    <div class="callout-peach">
      <div>
        <h3>Still need assistance?</h3>
        <p style="color: var(--color-muted); font-size: 15px;">
          Our community operations team typically responds within one business day.
        </p>
      </div>
      <div>
        <?php if (is_logged_in()): ?>
          <a href="<?= BASE_URL ?>/member/ticket_create.php" class="btn btn-primary">
            Create Support Ticket
          </a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/contact.php" class="btn btn-primary">
            Contact Support Team
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
