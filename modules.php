<?php
/**
 * Skill Development Modules Page
 * Matches Figma: 06 Skill Development Modules.png
 */

$pageTitle = "Skill Development Modules";
require_once __DIR__ . '/includes/header.php';
$pdo = get_db();

$modules = [];
try {
    $stmt = $pdo->query("SELECT * FROM modules ORDER BY sort_order ASC, id ASC");
    $modules = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<section class="section">
  <div class="container">
    <div style="max-width: 760px; margin-bottom: 40px;">
      <div class="card-category" style="margin-bottom: 12px;">CURRICULUM & PATHWAYS</div>
      <h1 class="display-title" style="margin-bottom: 16px;">Skill Development Modules</h1>
      <p style="font-size: 18px; color: var(--color-muted); line-height: 1.6;">
        Structured micro-credential pathways designed by Taylor's University culinary chefs and business faculty to elevate your skills from domestic hobby to commercial viability.
      </p>
    </div>

    <div class="card-grid">
      <?php foreach ($modules as $mod): ?>
        <div class="content-card">
          <img src="<?= BASE_URL ?>/<?= e($mod['poster_path'] ?: 'assets/images/20 Sample Photography - Kitchen.png') ?>" alt="<?= e($mod['title']) ?>" class="card-cover">
          <div class="card-body">
            <div class="card-category"><?= e($mod['category']) ?> · <?= e($mod['lessons_count']) ?> LESSONS</div>
            <div class="card-title"><?= e($mod['title']) ?></div>
            <div class="card-text"><?= e($mod['overview']) ?></div>
            
            <div style="font-size: 13px; color: var(--color-ink); margin-top: auto; padding-top: 10px; border-top: 1px solid var(--color-line);">
              <strong>Schedule:</strong> <?= e($mod['schedule']) ?>
            </div>
          </div>
          <div class="card-footer">
            <a href="<?= BASE_URL ?>/module_detail.php?id=<?= $mod['id'] ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
              View module curriculum →
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="callout-peach">
      <div>
        <h3>Want to register for these training tracks?</h3>
        <p style="color: var(--color-muted); font-size: 15px;">
          Create a member account and submit the Program Registration Form to secure subsidized tuition and campus kitchen lab slots.
        </p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-lg">Apply for Enrollment</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
