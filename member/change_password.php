<?php
/**
 * Member Change Password Page
 * Matches Figma: 02 Member workspace/03 Change Password.png
 */

$pageTitle = "Change Password";
require_once __DIR__ . '/../includes/user_header.php';
$pdo = get_db();
$userId = $user['id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        $errors[] = 'Current password does not match.';
    } elseif (strlen($new) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $errors[] = 'New passwords do not match.';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $userId]);
        log_audit('CHANGE_PASSWORD', 'users', $userId, "User changed password");
        set_flash('success', 'Your password has been changed successfully.');
        header('Location: ' . BASE_URL . '/member/profile.php');
        exit;
    }
}
?>

<div style="max-width: 540px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/member/profile.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to profile</a>
  </div>

  <div style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin-bottom: 6px;">Change Password</h1>
    <p style="color: var(--color-muted);">Ensure your account uses a secure password.</p>
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
        <label class="form-label">Current Password</label>
        <input type="password" name="current_password" class="form-input" required>
      </div>

      <div class="form-group">
        <label class="form-label">New Password</label>
        <input type="password" name="new_password" class="form-input" required placeholder="Minimum 6 characters">
      </div>

      <div class="form-group">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-input" required placeholder="Re-enter new password">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px;">
        <a href="<?= BASE_URL ?>/member/profile.php" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">Update password</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
