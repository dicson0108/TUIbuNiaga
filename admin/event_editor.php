<?php
/**
 * Admin Event Editor (Create / Edit)
 * Matches Figma: 04 Admin workspace/06 Event Editor.png
 */

require_once __DIR__ . '/../includes/admin_header.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$event = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch();
}

$pageTitle = $event ? "Edit Event · " . $event['title'] : "Create Event";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'Workshop');
    $type = in_array($_POST['type'] ?? '', ['Physical', 'Online', 'Hybrid']) ? $_POST['type'] : 'Physical';
    $venue = trim($_POST['venue'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 30);
    $startAt = $_POST['start_at'] ?? '';
    $endAt = $_POST['end_at'] ?? '';
    $speaker = trim($_POST['speaker'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['Upcoming', 'Completed', 'Cancelled']) ? $_POST['status'] : 'Upcoming';
    $posterPath = $event['poster_path'] ?? 'assets/images/20 Sample Photography - Kitchen.png';

    if (!$title || !$venue || !$startAt || !$endAt || !$description) {
        $errors[] = 'Please fill in all mandatory fields (Title, Venue, Start/End Time, Description).';
    }

    if (!empty($_FILES['poster']['name'])) {
        $res = handle_file_upload($_FILES['poster'], 'uploads/photos/', ['jpg', 'jpeg', 'png']);
        if ($res['success']) {
            $posterPath = $res['file_path'];
        } else {
            $errors[] = 'Poster upload error: ' . $res['error'];
        }
    }

    if (empty($errors)) {
        if ($event) {
            $stmtUp = $pdo->prepare("UPDATE events SET title = ?, category = ?, type = ?, venue = ?, capacity = ?, start_at = ?, end_at = ?, speaker = ?, description = ?, poster_path = ?, status = ? WHERE id = ?");
            $stmtUp->execute([$title, $category, $type, $venue, $capacity, $startAt, $endAt, $speaker, $description, $posterPath, $status, $event['id']]);
            log_audit('UPDATE_EVENT', 'events', $event['id'], "Admin updated event {$title}");
            set_flash('success', "Event '{$title}' updated successfully.");
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO events (title, category, type, venue, capacity, start_at, end_at, speaker, description, poster_path, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmtIns->execute([$title, $category, $type, $venue, $capacity, $startAt, $endAt, $speaker, $description, $posterPath, $status, $user['id']]);
            $newId = $pdo->lastInsertId();
            log_audit('CREATE_EVENT', 'events', $newId, "Admin created event {$title}");
            set_flash('success', "Event '{$title}' scheduled successfully.");
        }
        header('Location: ' . BASE_URL . '/admin/events.php');
        exit;
    }
}
?>

<div style="max-width: 760px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/admin/events.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to Event Management</a>
  </div>

  <div style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin-bottom: 6px;"><?= $event ? 'Edit Event' : 'Create New Event' ?></h1>
    <p style="color: var(--color-muted);">Configure workshop date, time, venue, facilitator, and seating capacity.</p>
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

  <div class="form-card" style="max-width: 100%;">
    <form method="POST" action="" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div class="form-group">
        <label class="form-label">Event Title *</label>
        <input type="text" name="title" value="<?= e($event['title'] ?? '') ?>" class="form-input" required placeholder="e.g. Bake your next chapter">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
        <div class="form-group">
          <label class="form-label">Category *</label>
          <select name="category" class="form-select" required>
            <option value="Workshop" <?= (($event['category'] ?? '') === 'Workshop') ? 'selected' : '' ?>>Workshop</option>
            <option value="Financial Literacy" <?= (($event['category'] ?? '') === 'Financial Literacy') ? 'selected' : '' ?>>Financial Literacy</option>
            <option value="Community" <?= (($event['category'] ?? '') === 'Community') ? 'selected' : '' ?>>Community</option>
            <option value="Masterclass" <?= (($event['category'] ?? '') === 'Masterclass') ? 'selected' : '' ?>>Masterclass</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Format Type *</label>
          <select name="type" class="form-select" required>
            <option value="Physical" <?= (($event['type'] ?? '') === 'Physical') ? 'selected' : '' ?>>Physical</option>
            <option value="Online" <?= (($event['type'] ?? '') === 'Online') ? 'selected' : '' ?>>Online</option>
            <option value="Hybrid" <?= (($event['type'] ?? '') === 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Capacity Limit *</label>
          <input type="number" name="capacity" value="<?= (int)($event['capacity'] ?? 30) ?>" class="form-input" required min="1">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Start Date & Time *</label>
          <input type="datetime-local" name="start_at" value="<?= $event ? date('Y-m-d\TH:i', strtotime($event['start_at'])) : '' ?>" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">End Date & Time *</label>
          <input type="datetime-local" name="end_at" value="<?= $event ? date('Y-m-d\TH:i', strtotime($event['end_at'])) : '' ?>" class="form-input" required>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Venue / Room / Link *</label>
          <input type="text" name="venue" value="<?= e($event['venue'] ?? "Taylor's Lakeside Campus · Kitchen Studio") ?>" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">Speaker / Facilitator Mentors</label>
          <input type="text" name="speaker" value="<?= e($event['speaker'] ?? "Taylor's Culinary Faculty Mentors") ?>" class="form-input">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Lifecycle Status</label>
        <select name="status" class="form-select">
          <option value="Upcoming" <?= (($event['status'] ?? '') === 'Upcoming') ? 'selected' : '' ?>>Upcoming (Active registration)</option>
          <option value="Completed" <?= (($event['status'] ?? '') === 'Completed') ? 'selected' : '' ?>>Completed</option>
          <option value="Cancelled" <?= (($event['status'] ?? '') === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Detailed Description *</label>
        <textarea name="description" class="form-textarea" required style="min-height: 120px;" placeholder="Session syllabus, tools provided, learning outcomes..."><?= e($event['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Event Poster Image (Optional)</label>
        <input type="file" name="poster" class="form-input" accept=".jpg,.jpeg,.png">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--color-line);">
        <a href="<?= BASE_URL ?>/admin/events.php" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
          <?= $event ? 'Save Event Changes' : 'Schedule Event' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
