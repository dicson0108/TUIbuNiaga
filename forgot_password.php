<?php
/**
 * Forgot Password Page
 * Matches Figma: 22 Forgot Password.png & 26 Reset Link Sent.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = get_db();

$sent = false;
$email = '';
$token = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $token = bin2hex(random_bytes(24));
            $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
            $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))")->execute([$email, $token]);

            $resetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                      . "://" . $_SERVER['HTTP_HOST'] . BASE_URL . "/reset_password.php?token=" . $token;

            $body = "<h3>Password Reset Request</h3>"
                  . "<p>You recently requested to reset your password for Taylor's Ibu Niaga Platform. Click below to set a new password:</p>"
                  . "<p><a href='{$resetUrl}' style='display:inline-block;padding:12px 24px;background:#d71920;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>Reset Password</a></p>"
                  . "<p>This link is valid for 1 hour.</p>";

            send_app_email($email, "Password Reset Link · Taylor's Ibu Niaga", $body);
        }
        $sent = true;
    } else {
        set_flash('error', 'Please provide a valid email address.');
    }
}

$pageTitle = $sent ? "Reset Link Sent · Ibu Niaga" : "Forgot Password · Ibu Niaga";
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 480px;">
    <?php if ($sent): ?>
      <!-- Matches Figma 26 Reset Link Sent.png -->
      <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--color-peach); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px;">
          ✉
        </div>
        <h1 style="font-size: 26px; margin-bottom: 12px;">Reset link sent</h1>
        <p style="font-size: 15px; color: var(--color-muted); line-height: 1.6; margin-bottom: 24px;">
          If an account exists for <strong><?= e($email) ?></strong>, we have dispatched a password reset link to your email.
        </p>

        <?php if ($token): ?>
          <div style="background: #f0f7ff; border: 1px dashed #0284c7; padding: 14px; border-radius: var(--radius-sm); margin-bottom: 24px; text-align: left; font-size: 13px;">
            <div style="font-weight: 700; color: #0369a1; margin-bottom: 4px;">⚡ Local Dev Quick Reset</div>
            <a href="<?= BASE_URL ?>/reset_password.php?token=<?= e($token) ?>" class="btn btn-primary btn-sm">
              Open Reset Password Form →
            </a>
          </div>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/login.php" class="btn btn-secondary" style="width: 100%;">
          Return to login
        </a>
      </div>
    <?php else: ?>
      <!-- Matches Figma 22 Forgot Password.png -->
      <div style="text-align: center; margin-bottom: 32px;">
        <h1 style="font-size: 32px; margin-bottom: 8px;">Reset your password</h1>
        <p style="color: var(--color-muted); font-size: 15px;">
          Enter your registered email address and we'll send a password recovery link.
        </p>
      </div>

      <div class="form-card" style="max-width: 100%;">
        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-input" required placeholder="name@domain.com">
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 8px;">
            Send reset instructions
          </button>

          <div style="text-align: center; margin-top: 24px; font-size: 14px;">
            <a href="<?= BASE_URL ?>/login.php" style="color: var(--color-muted);">← Back to login</a>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
