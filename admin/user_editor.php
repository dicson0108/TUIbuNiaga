<?php
/**
 * Admin User Editor (Create / Edit)
 * Matches Figma: 04 Admin workspace/03 User Editor.png
 */

require_once __DIR__ . '/../includes/admin_header.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$targetUser = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $targetUser = $stmt->fetch();
}

$pageTitle = $targetUser ? "Edit User · " . $targetUser['name'] : "Create User";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['user', 'moderator', 'admin']) ? $_POST['role'] : 'user';
    $status = in_array($_POST['status'] ?? '', ['active', 'suspended']) ? $_POST['status'] : 'active';
    $language = in_array($_POST['language'] ?? '', ['en', 'bm', 'zh']) ? $_POST['language'] : 'en';
    $phone = trim($_POST['phone'] ?? '');
    $businessName = trim($_POST['business_name'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid Name and Email address are required.';
    }

    if (empty($errors)) {
        if ($targetUser) {
            // Update
            if (!empty($newPassword)) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmtUp = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, status = ?, language = ?, phone = ?, business_name = ?, password_hash = ? WHERE id = ?");
                $stmtUp->execute([$name, $email, $role, $status, $language, $phone, $businessName, $hash, $targetUser['id']]);
            } else {
                $stmtUp = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, status = ?, language = ?, phone = ?, business_name = ? WHERE id = ?");
                $stmtUp->execute([$name, $email, $role, $status, $language, $phone, $businessName, $targetUser['id']]);
            }
            log_audit('UPDATE_USER', 'users', $targetUser['id'], "Admin updated user #{$targetUser['id']} ({$email}, role: {$role})");
            set_flash('success', "User {$name} updated successfully.");
        } else {
            // Create
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = 'An account with that email already exists.';
            } else {
                $passToUse = !empty($newPassword) ? $newPassword : 'User@123';
                $hash = password_hash($passToUse, PASSWORD_DEFAULT);
                $stmtIns = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status, language, phone, business_name, email_verified_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$name, $email, $hash, $role, $status, $language, $phone, $businessName]);
                $newId = $pdo->lastInsertId();

                log_audit('CREATE_USER', 'users', $newId, "Admin created user #{$newId} ({$email}, role: {$role})");
                set_flash('success', "New user account created with temporary password: {$passToUse}");
                header('Location: ' . BASE_URL . '/admin/users.php');
                exit;
            }
        }

        if (empty($errors)) {
            header('Location: ' . BASE_URL . '/admin/users.php');
            exit;
        }
    }
}
?>

<div style="max-width: 680px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/admin/users.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to User Management</a>
  </div>

  <div style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin-bottom: 6px;"><?= $targetUser ? 'Edit User & Role' : 'Create New User' ?></h1>
    <p style="color: var(--color-muted);">Assign system permissions, specify operational roles, or update credentials.</p>
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
        <input type="text" name="name" value="<?= e($targetUser['name'] ?? '') ?>" class="form-input" required>
      </div>

      <div class="form-group">
        <label class="form-label">Email Address *</label>
        <input type="email" name="email" value="<?= e($targetUser['email'] ?? '') ?>" class="form-input" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">System Role *</label>
          <select name="role" class="form-select" required>
            <option value="user" <?= (($targetUser['role'] ?? '') === 'user') ? 'selected' : '' ?>>Member / User</option>
            <option value="moderator" <?= (($targetUser['role'] ?? '') === 'moderator') ? 'selected' : '' ?>>Moderator (Operations)</option>
            <option value="admin" <?= (($targetUser['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrator (System Owner)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Account Status</label>
          <select name="status" class="form-select">
            <option value="active" <?= (($targetUser['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
            <option value="suspended" <?= (($targetUser['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
          </select>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" value="<?= e($targetUser['phone'] ?? '') ?>" class="form-input" placeholder="+60 12-345 6789">
        </div>

        <div class="form-group">
          <label class="form-label">Language Preference</label>
          <select name="language" class="form-select">
            <option value="en" <?= (($targetUser['language'] ?? '') === 'en') ? 'selected' : '' ?>>English</option>
            <option value="bm" <?= (($targetUser['language'] ?? '') === 'bm') ? 'selected' : '' ?>>Bahasa Melayu</option>
            <option value="zh" <?= (($targetUser['language'] ?? '') === 'zh') ? 'selected' : '' ?>>中文 (Chinese)</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Business / Brand Name (Optional)</label>
        <input type="text" name="business_name" value="<?= e($targetUser['business_name'] ?? '') ?>" class="form-input">
      </div>

      <div class="form-group">
        <label class="form-label"><?= $targetUser ? 'Reset Password (Leave blank to keep current)' : 'Password (Default: User@123)' ?></label>
        <input type="password" name="new_password" class="form-input" placeholder="Set password">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--color-line);">
        <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
          <?= $targetUser ? 'Save User Record' : 'Create User' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
