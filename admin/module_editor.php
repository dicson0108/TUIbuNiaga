<?php
/**
 * Admin Module & Lesson Editor
 * Matches Figma: 04 Admin workspace/10 Module Editor.png
 */

require_once __DIR__ . '/../includes/admin_header.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$module = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
    $stmt->execute([$id]);
    $module = $stmt->fetch();
}

$pageTitle = $module ? "Edit Module · " . $module['title'] : "Create Module";
$errors = [];

// Handle Adding a Lesson
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_lesson') {
    verify_csrf();
    $lessonTitle = trim($_POST['lesson_title'] ?? '');
    $duration = trim($_POST['duration'] ?? '45 mins');
    $content = trim($_POST['lesson_content'] ?? '');
    $order = (int)($_POST['sort_order'] ?? 1);

    if ($module && $lessonTitle) {
        $stmtLes = $pdo->prepare("INSERT INTO module_lessons (module_id, title, duration, content, sort_order) VALUES (?, ?, ?, ?, ?)");
        $stmtLes->execute([$module['id'], $lessonTitle, $duration, $content, $order]);
        set_flash('success', 'New lesson added to module syllabus.');
        header("Location: " . BASE_URL . "/admin/module_editor.php?id=" . $module['id']);
        exit;
    }
}

// Handle Delete Lesson
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_lesson') {
    verify_csrf();
    $lessonId = (int)$_POST['lesson_id'];
    $pdo->prepare("DELETE FROM module_lessons WHERE id = ? AND module_id = ?")->execute([$lessonId, $id]);
    set_flash('info', 'Lesson removed.');
    header("Location: " . BASE_URL . "/admin/module_editor.php?id=" . $id);
    exit;
}

// Handle Module Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'save_module')) {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $overview = trim($_POST['overview'] ?? '');
    $schedule = trim($_POST['schedule'] ?? '');
    $resources = trim($_POST['resources'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $posterPath = $module['poster_path'] ?? 'assets/images/20 Sample Photography - Kitchen.png';

    if (!empty($_FILES['poster']['name'])) {
        $res = handle_file_upload($_FILES['poster'], 'uploads/photos/', ['jpg', 'jpeg', 'png']);
        if ($res['success']) $posterPath = $res['file_path'];
    }

    if (!$title || !$category || !$overview) {
        $errors[] = 'Title, Category, and Overview are required.';
    }

    if (empty($errors)) {
        if ($module) {
            $stmtUp = $pdo->prepare("UPDATE modules SET title = ?, category = ?, overview = ?, schedule = ?, resources = ?, sort_order = ?, poster_path = ? WHERE id = ?");
            $stmtUp->execute([$title, $category, $overview, $schedule, $resources, $sortOrder, $posterPath, $module['id']]);
            log_audit('UPDATE_MODULE', 'modules', $module['id'], "Updated module {$title}");
            set_flash('success', "Module '{$title}' updated.");
            header("Location: " . BASE_URL . "/admin/module_editor.php?id=" . $module['id']);
            exit;
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO modules (title, category, overview, schedule, resources, sort_order, poster_path, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmtIns->execute([$title, $category, $overview, $schedule, $resources, $sortOrder, $posterPath]);
            $newId = $pdo->lastInsertId();
            log_audit('CREATE_MODULE', 'modules', $newId, "Created module {$title}");
            set_flash('success', "Module '{$title}' created. You can now add syllabus lessons.");
            header("Location: " . BASE_URL . "/admin/module_editor.php?id=" . $newId);
            exit;
        }
    }
}

// Fetch lessons
$lessons = [];
if ($module) {
    $stmtL = $pdo->prepare("SELECT * FROM module_lessons WHERE module_id = ? ORDER BY sort_order ASC, id ASC");
    $stmtL->execute([$module['id']]);
    $lessons = $stmtL->fetchAll();
}
?>

<div style="max-width: 840px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/admin/modules.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to Module Management</a>
  </div>

  <div style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin-bottom: 6px;"><?= $module ? 'Edit Module Curriculum' : 'Create Skill Module' ?></h1>
    <p style="color: var(--color-muted);">Define module details, learning resources, and lesson sequences.</p>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <ul style="margin-left: 20px;">
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Module Details Form -->
  <div class="form-card" style="max-width: 100%; margin-bottom: 40px;">
    <form method="POST" action="" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="save_module">

      <div class="form-group">
        <label class="form-label">Module Title *</label>
        <input type="text" name="title" value="<?= e($module['title'] ?? '') ?>" class="form-input" required placeholder="e.g. Pastry Skills">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Category *</label>
          <input type="text" name="category" value="<?= e($module['category'] ?? 'Practical Skills') ?>" class="form-input" required placeholder="e.g. Practical Skills or Wellbeing">
        </div>

        <div class="form-group">
          <label class="form-label">Display Sort Order</label>
          <input type="number" name="sort_order" value="<?= (int)($module['sort_order'] ?? 1) ?>" class="form-input">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Overview Description *</label>
        <textarea name="overview" class="form-textarea" required placeholder="High level objectives and who this module is designed for..."><?= e($module['overview'] ?? '') ?></textarea>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Schedule</label>
          <input type="text" name="schedule" value="<?= e($module['schedule'] ?? '') ?>" class="form-input" placeholder="e.g. Tuesdays & Thursdays · 2:00 PM - 5:00 PM">
        </div>

        <div class="form-group">
          <label class="form-label">Resources / Materials</label>
          <input type="text" name="resources" value="<?= e($module['resources'] ?? '') ?>" class="form-input" placeholder="e.g. Baking Standard Formulation Manual">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Cover Image</label>
        <input type="file" name="poster" class="form-input" accept=".jpg,.jpeg,.png">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--color-line);">
        <a href="<?= BASE_URL ?>/admin/modules.php" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
          <?= $module ? 'Save Module Info' : 'Create Module' ?>
        </button>
      </div>
    </form>
  </div>

  <?php if ($module): ?>
    <!-- Lessons Manager Section -->
    <div style="border-top: 2px solid var(--color-line); padding-top: 36px; margin-bottom: 40px;">
      <h2 style="margin-bottom: 8px;">Module Syllabus Lessons (<?= count($lessons) ?>)</h2>
      <p style="color: var(--color-muted); font-size: 14px; margin-bottom: 24px;">Manage lesson sequence, duration, and detailed topic breakdown.</p>

      <!-- Existing Lessons List -->
      <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 32px;">
        <?php foreach ($lessons as $idx => $les): ?>
          <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-sm); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px;">
            <div>
              <div style="font-weight: 700; color: var(--color-ink); font-size: 15px;">
                <?= e($les['title']) ?>
              </div>
              <div style="font-size: 13px; color: var(--color-muted); margin-top: 2px;">
                <?= e($les['content']) ?>
              </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; white-space: nowrap;">
              <span class="badge badge-neutral"><?= e($les['duration']) ?></span>
              <form method="POST" action="" onsubmit="return confirm('Remove this lesson?');">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete_lesson">
                <input type="hidden" name="lesson_id" value="<?= $les['id'] ?>">
                <button type="submit" class="btn btn-destructive btn-sm">Delete</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Add New Lesson Form -->
      <div class="form-card" style="max-width: 100%; background: var(--color-sage);">
        <h3 style="font-size: 16px; margin-bottom: 16px;">+ Add New Lesson to Syllabus</h3>
        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="add_lesson">

          <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Lesson Title *</label>
              <input type="text" name="lesson_title" class="form-input" required placeholder="e.g. 7. Artisan Sourdough Fermentation">
            </div>

            <div class="form-group">
              <label class="form-label">Duration</label>
              <input type="text" name="duration" value="60 mins" class="form-input" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Topic Content / Description</label>
            <input type="text" name="lesson_content" class="form-input" placeholder="Brief summary of hands-on topics covered in this lesson">
          </div>

          <div style="display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary">Add Lesson</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
