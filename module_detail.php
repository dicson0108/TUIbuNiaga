<?php
/**
 * Module Detail Page
 * Matches Figma: 12 Module Detail.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
$stmt->execute([$id]);
$module = $stmt->fetch();

if (!$module) {
    header("Location: " . BASE_URL . "/modules.php");
    exit;
}

$pageTitle = $module['title'];
require_once __DIR__ . '/includes/header.php';

// Fetch lessons
$stmtLes = $pdo->prepare("SELECT * FROM module_lessons WHERE module_id = ? ORDER BY sort_order ASC");
$stmtLes->execute([$id]);
$lessons = $stmtLes->fetchAll();
?>

<section class="section">
  <div class="container" style="max-width: 900px;">
    <div style="margin-bottom: 24px;">
      <a href="<?= BASE_URL ?>/modules.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to all modules</a>
    </div>

    <div class="card-category" style="margin-bottom: 8px;">
      <?= e($module['category']) ?> · <?= count($lessons) ?> LESSONS
    </div>
    <h1 class="display-title" style="margin-bottom: 20px; font-size: 38px;"><?= e($module['title']) ?></h1>

    <img src="<?= BASE_URL ?>/<?= e($module['poster_path'] ?: 'assets/images/20 Sample Photography - Kitchen.png') ?>" alt="<?= e($module['title']) ?>" style="width: 100%; height: 380px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 32px; border: 1px solid var(--color-line);">

    <div style="background-color: var(--color-cream); border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 28px; margin-bottom: 36px;">
      <h3 style="margin-bottom: 12px;">Module Overview</h3>
      <p style="font-size: 16px; line-height: 1.6; color: var(--color-ink); margin-bottom: 20px;">
        <?= e($module['overview']) ?>
      </p>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; border-top: 1px solid var(--color-line); padding-top: 20px;">
        <div>
          <span class="text-caption">Schedule</span>
          <div style="font-size: 15px; font-weight: 600; color: var(--color-ink); margin-top: 2px;">
            <?= e($module['schedule'] ?: 'Weekly flexible sessions') ?>
          </div>
        </div>
        <div>
          <span class="text-caption">Learning Resources</span>
          <div style="font-size: 15px; font-weight: 600; color: var(--color-ink); margin-top: 2px;">
            <?= e($module['resources'] ?: 'Official course workbook & guides') ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Curriculum Lessons List -->
    <h2 style="margin-bottom: 20px;">Syllabus & Lesson Plan</h2>

    <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 40px;">
      <?php if (empty($lessons)): ?>
        <p style="color: var(--color-muted);">Detailed lesson syllabus will be published shortly before cohort start.</p>
      <?php else: ?>
        <?php foreach ($lessons as $idx => $les): ?>
          <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-sm); padding: 20px; display: flex; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
              <div style="font-size: 16px; font-weight: 700; color: var(--color-ink); margin-bottom: 4px;">
                <?= e($les['title']) ?>
              </div>
              <div style="font-size: 14px; color: var(--color-muted);">
                <?= e($les['content']) ?>
              </div>
            </div>
            <div style="white-space: nowrap;">
              <span class="badge badge-neutral"><?= e($les['duration']) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="callout-peach">
      <div>
        <h3>Ready to take part in this module?</h3>
        <p style="color: var(--color-muted); font-size: 15px;">Registered participants can apply to join this module pathway directly in their Member Workspace.</p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/member/program_registration.php" class="btn btn-primary btn-lg">Apply for Module</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
