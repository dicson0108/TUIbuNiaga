<?php
/**
 * Reset Password Form
 * Matches Figma: 23 Reset Password.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = get_db();

$token = trim($_GET['token'] ?? '');
$errors = [];
$isValid = false;
$email = '';

if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if ($reset) {
        $isValid = true;
        $email = $reset['email'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValid) {
    verify_csrf();
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?")->execute([$hash, $email]);
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        log_audit('PASSWORD_RESET', 'users', null, "Password reset completed for: {$email}");
        set_flash('success', 'Your password has been reset successfully. Please log in with your new password.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

$pageTitle = "Reset Password · Ibu Niaga";
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 480px;">
    <?php if (!$isValid): ?>
      <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center;">
        <h1 style="font-size: 24px; margin-bottom: 12px; color: var(--color-primary);">Invalid or Expired Link</h1>
        <p style="font-size: 15px; color: var(--color-muted); line-height: 1.6; margin-bottom: 24px;">
          This password reset link is invalid or has expired. Please request a new link.
        </p>
        <a href="<?= BASE_URL ?>/forgot_password.php" class="btn btn-primary">
          Request new reset link
        </a>
      </div>
    <?php else: ?>
      <div style="text-align: center; margin-bottom: 32px;">
        <h1 style="font-size: 32px; margin-bottom: 8px;">Create new password</h1>
        <p style="color: var(--color-muted); font-size: 15px;">
          Set a new password for <strong><?= e($email) ?></strong>.
        </p>
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
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-input" required placeholder="Minimum 6 characters">
          </div>

          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-input" required placeholder="Re-enter new password">
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px;">
            Update password
          </button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
