<?php
/**
 * Moderator Participant Editor
 * Matches Figma: 03 Moderator workspace/05 Participant Editor.png
 */

require_once __DIR__ . '/../includes/mod_header.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$participant = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
    $stmt->execute([$id]);
    $participant = $stmt->fetch();
}

$pageTitle = $participant ? "Edit Participant · " . $participant['name'] : "Add Participant";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $businessName = trim($_POST['business_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['active', 'suspended']) ? $_POST['status'] : 'active';

    if (!$name || !$email) {
        $errors[] = 'Name and Email are required.';
    }

    if (empty($errors)) {
        if ($participant) {
            $stmtUp = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, business_name = ?, bio = ?, status = ? WHERE id = ?");
            $stmtUp->execute([$name, $email, $phone, $businessName, $bio, $status, $participant['id']]);

            log_audit('UPDATE_PARTICIPANT', 'users', $participant['id'], "Updated participant record {$name}");
            set_flash('success', "Participant record for {$name} updated successfully.");
        } else {
            $tempPass = password_hash('User@123', PASSWORD_DEFAULT);
            $stmtIns = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, phone, business_name, bio, status, email_verified_at, created_at) VALUES (?, ?, ?, 'user', ?, ?, ?, ?, NOW(), NOW())");
            $stmtIns->execute([$name, $email, $tempPass, $phone, $businessName, $bio, $status]);
            $newId = $pdo->lastInsertId();

            log_audit('CREATE_PARTICIPANT', 'users', $newId, "Created participant record {$name}");
            set_flash('success', "New participant record created. Default temporary password: User@123");
        }
        header('Location: ' . BASE_URL . '/moderator/participants.php');
        exit;
    }
}
?>

<div style="max-width: 680px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/moderator/participants.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to Participant Records</a>
  </div>

  <div style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin-bottom: 6px;"><?= $participant ? 'Edit Participant Record' : 'Add New Participant' ?></h1>
    <p style="color: var(--color-muted);">Manage member account details, contact information, and community status.</p>
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
    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input type="text" name="name" value="<?= e($participant['name'] ?? '') ?>" class="form-input" required>
      </div>

      <div class="form-group">
        <label class="form-label">Email Address *</label>
        <input type="email" name="email" value="<?= e($participant['email'] ?? '') ?>" class="form-input" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" value="<?= e($participant['phone'] ?? '') ?>" class="form-input" placeholder="+60 12-345 6789">
        </div>

        <div class="form-group">
          <label class="form-label">Account Status</label>
          <select name="status" class="form-select">
            <option value="active" <?= (($participant['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
            <option value="suspended" <?= (($participant['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Business / Brand Name</label>
        <input type="text" name="business_name" value="<?= e($participant['business_name'] ?? '') ?>" class="form-input">
      </div>

      <div class="form-group">
        <label class="form-label">Bio / Profile Notes</label>
        <textarea name="bio" class="form-textarea" placeholder="Internal notes or public profile summary..."><?= e($participant['bio'] ?? '') ?></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--color-line);">
        <a href="<?= BASE_URL ?>/moderator/participants.php" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
          <?= $participant ? 'Save Participant Record' : 'Create Record' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
