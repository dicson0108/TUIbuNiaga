<?php
/**
 * Past Projects Page
 * Matches Figma: 03 Past Projects.png
 */

$pageTitle = "Past Projects";
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();

$projects = [];
try {
    $stmt = $pdo->query("SELECT * FROM projects ORDER BY year DESC, id DESC");
    $projects = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<section class="section">
  <div class="container">
    <div style="max-width: 760px; margin-bottom: 40px;">
      <div class="card-category" style="margin-bottom: 12px;">COMMUNITY IMPACT & OUTCOMES</div>
      <h1 class="display-title" style="margin-bottom: 16px;">Past Projects</h1>
      <p style="font-size: 18px; color: var(--color-muted); line-height: 1.6;">
        Explore initiatives developed in partnership with Taylor's University students, faculty, and community micro-entrepreneurs.
      </p>
    </div>

    <div class="card-grid">
      <?php foreach ($projects as $proj): ?>
        <div class="content-card">
          <img src="<?= BASE_URL ?>/<?= e($proj['poster_path'] ?: 'assets/images/20 Sample Photography - Kitchen.png') ?>" alt="<?= e($proj['title']) ?>" class="card-cover">
          <div class="card-body">
            <div class="card-category"><?= e($proj['category']) ?> · <?= e($proj['year']) ?></div>
            <div class="card-title"><?= e($proj['title']) ?></div>
            <div class="card-text"><?= e($proj['summary']) ?></div>
            <div style="background-color: var(--color-sage); padding: 12px 14px; border-radius: var(--radius-sm); font-size: 13px; color: var(--color-ink); margin-top: auto;">
              <strong>Outcome:</strong> <?= e($proj['outcomes']) ?>
            </div>
          </div>
          <div class="card-footer">
            <a href="<?= BASE_URL ?>/project_detail.php?id=<?= $proj['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
              View project details →
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
