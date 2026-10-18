<?php
/**
 * Help Article Detail Page
 * Matches Figma: 18 Help Article.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM help_articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header("Location: " . BASE_URL . "/help.php");
    exit;
}

$pageTitle = $article['title'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 820px;">
    <div style="margin-bottom: 24px;">
      <a href="<?= BASE_URL ?>/help.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to Help Center</a>
    </div>

    <div class="card-category" style="margin-bottom: 8px;"><?= e($article['category']) ?></div>
    <h1 class="display-title" style="margin-bottom: 24px; font-size: 36px;"><?= e($article['title']) ?></h1>

    <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 36px; font-size: 16px; line-height: 1.8; color: var(--color-ink); margin-bottom: 40px;">
      <?= nl2br(e($article['body'])) ?>
    </div>

    <div class="callout-peach">
      <div>
        <h3>Was this article helpful?</h3>
        <p style="color: var(--color-muted); font-size: 14px;">If you have further questions, submit a support ticket directly to our moderators.</p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/member/ticket_create.php" class="btn btn-primary">
          Submit a Ticket
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
