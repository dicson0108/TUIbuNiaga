<?php
/**
 * Admin Module Management
 * Matches Figma: 04 Admin workspace/09 Module Management.png
 */

$pageTitle = "Module Management";
require_once __DIR__ . '/../includes/admin_header.php';
$pdo = get_db();

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    verify_csrf();
    $modId = (int)$_POST['module_id'];
    $pdo->prepare("DELETE FROM modules WHERE id = ?")->execute([$modId]);
    log_audit('DELETE_MODULE', 'modules', $modId, "Admin deleted module #{$modId}");
    set_flash('info', 'Module deleted successfully.');
    header('Location: ' . BASE_URL . '/admin/modules.php');
    exit;
}

$stmt = $pdo->query("SELECT m.*, (SELECT COUNT(*) FROM module_lessons l WHERE l.module_id = m.id) as actual_lessons FROM modules m ORDER BY m.sort_order ASC, m.id ASC");
$modules = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Module Management</h1>
    <p style="color: var(--color-muted);">Create and organize skill development pathways, syllabus lessons, schedules, and learning materials.</p>
  </div>

  <a href="<?= BASE_URL ?>/admin/module_editor.php" class="btn btn-primary">
    + Create New Module
  </a>
</div>

<div class="table-wrapper">
  <table class="data-table">
    <thead>
      <tr>
        <th>Module Title</th>
        <th>Category</th>
        <th>Lessons Count</th>
        <th>Schedule</th>
        <th>Sort Order</th>
        <th style="text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($modules)): ?>
        <tr>
          <td colspan="6" style="text-align: center; color: var(--color-muted); padding: 48px;">
            No skill modules created yet.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($modules as $m): ?>
          <tr>
            <td>
              <div style="font-weight: 700; color: var(--color-ink);"><?= e($m['title']) ?></div>
              <div class="text-caption"><?= e(substr($m['overview'], 0, 90)) ?>...</div>
            </td>
            <td><span class="badge badge-neutral"><?= e($m['category']) ?></span></td>
            <td style="font-weight: 600;"><?= max((int)$m['lessons_count'], (int)$m['actual_lessons']) ?> lessons</td>
            <td style="color: var(--color-muted); font-size: 13px;"><?= e($m['schedule'] ?: 'Flexible') ?></td>
            <td><?= (int)$m['sort_order'] ?></td>
            <td style="text-align: right; white-space: nowrap;">
              <a href="<?= BASE_URL ?>/admin/module_editor.php?id=<?= $m['id'] ?>" class="btn btn-secondary btn-sm">
                Edit Syllabus
              </a>
              <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this module and all associated lessons?');">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="module_id" value="<?= $m['id'] ?>">
                <button type="submit" class="btn btn-destructive btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
