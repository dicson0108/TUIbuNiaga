<?php
/**
 * Project Detail Page
 * Matches Figma: 09 Project Detail.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: " . BASE_URL . "/projects.php");
    exit;
}

$pageTitle = $project['title'];
require_once __DIR__ . '/includes/header.php';
$gallery = json_decode($project['gallery_json'] ?? '[]', true);
?>

<section class="section">
  <div class="container" style="max-width: 900px;">
    <div style="margin-bottom: 24px;">
      <a href="<?= BASE_URL ?>/projects.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to all projects</a>
    </div>

    <div class="card-category" style="margin-bottom: 8px;">
      <?= e($project['category']) ?> · <?= e($project['year']) ?>
    </div>
    <h1 class="display-title" style="margin-bottom: 24px;"><?= e($project['title']) ?></h1>

    <img src="<?= BASE_URL ?>/<?= e($project['poster_path'] ?: 'assets/images/20 Sample Photography - Kitchen.png') ?>" alt="<?= e($project['title']) ?>" style="width: 100%; height: 420px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 32px;">

    <div style="background-color: var(--color-cream); border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 24px; margin-bottom: 32px;">
      <h3 style="margin-bottom: 12px; color: var(--color-ink);">Impact Summary</h3>
      <p style="font-size: 16px; line-height: 1.6; margin-bottom: 16px; color: var(--color-ink);"><?= e($project['summary']) ?></p>
      <div style="background-color: var(--badge-positive-bg); color: var(--badge-positive-text); padding: 12px 16px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 600;">
        Key Result: <?= e($project['outcomes']) ?>
      </div>
    </div>

    <?php if (!empty($gallery)): ?>
      <h3 style="margin-bottom: 16px;">Project Gallery</h3>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 40px;">
        <?php foreach ($gallery as $img): ?>
          <img src="<?= BASE_URL ?>/<?= e($img) ?>" alt="Project photo" style="width: 100%; height: 200px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--color-line);">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="callout-peach">
      <div>
        <h3>Want to partner with Taylor's Ibu Niaga?</h3>
        <p style="color: var(--color-muted); font-size: 14px;">We actively collaborate with community organizations, brands, and culinary mentors.</p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/contact.php" class="btn btn-primary">Get in touch</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
