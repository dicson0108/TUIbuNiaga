<?php
/**
 * Email Verification & Activation Page
 * Matches Figma: 24 Email Verification.png, 25 Email Verified.png, 27 Email Verification · Expired.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = get_db();

$token = trim($_GET['token'] ?? '');
$email = trim($_GET['email'] ?? '');
$sent = isset($_GET['sent']);

$status = 'pending'; // 'pending', 'verified', 'expired', 'invalid'

if ($token !== '') {
    $stmt = $pdo->prepare("SELECT * FROM email_verifications WHERE token = ?");
    $stmt->execute([$token]);
    $rec = $stmt->fetch();

    if ($rec) {
        if (strtotime($rec['expires_at']) < time()) {
            $status = 'expired';
            $email = $rec['email'];
        } else {
            // Mark user email verified
            $stmtUp = $pdo->prepare("UPDATE users SET email_verified_at = NOW() WHERE email = ?");
            $stmtUp->execute([$rec['email']]);

            // Clean up verification tokens
            $pdo->prepare("DELETE FROM email_verifications WHERE email = ?")->execute([$rec['email']]);
            $status = 'verified';
            $email = $rec['email'];

            log_audit('EMAIL_VERIFIED', 'users', null, "Verified email: {$email}");
        }
    } else {
        $status = 'invalid';
    }
}

// Resend verification link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_email'])) {
    verify_csrf();
    $resendEmail = trim($_POST['resend_email']);
    if ($resendEmail) {
        $newToken = bin2hex(random_bytes(24));
        $pdo->prepare("INSERT INTO email_verifications (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))")->execute([$resendEmail, $newToken]);
        
        $verifyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                   . "://" . $_SERVER['HTTP_HOST'] . BASE_URL . "/verify_email.php?token=" . $newToken;

        send_app_email($resendEmail, "New Verification Link · Taylor's Ibu Niaga", "<p>Please click here to verify: <a href='{$verifyUrl}'>{$verifyUrl}</a></p>");
        set_flash('success', "A fresh verification link has been sent to {$resendEmail}!");
        header("Location: " . BASE_URL . "/verify_email.php?sent=1&email=" . urlencode($resendEmail) . "&token=" . $newToken);
        exit;
    }
}

$pageTitle = "Email Verification · Ibu Niaga";
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 600px;">
    
    <?php if ($status === 'verified'): ?>
      <!-- Matches Figma 25 Email Verified.png -->
      <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #e8f5e9; color: #2e7d32; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px;">
          ✓
        </div>
        <h1 style="font-size: 28px; margin-bottom: 12px;">Email verified successfully!</h1>
        <p style="font-size: 16px; color: var(--color-muted); line-height: 1.6; margin-bottom: 28px;">
          Your email (<strong><?= e($email) ?></strong>) has been verified. You can now log into your Member Workspace, apply for incubation programs, and submit Open Bazaar listings.
        </p>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-lg" style="width: 100%;">
          Log into your workspace
        </a>
      </div>

    <?php elseif ($status === 'expired'): ?>
      <!-- Matches Figma 27 Email Verification · Expired.png -->
      <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #fff3e0; color: #e65100; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px;">
          ⏳
        </div>
        <h1 style="font-size: 28px; margin-bottom: 12px;">Verification link expired</h1>
        <p style="font-size: 16px; color: var(--color-muted); line-height: 1.6; margin-bottom: 28px;">
          Security links expire after 24 hours. Request a new verification link below to activate your account.
        </p>

        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="resend_email" value="<?= e($email) ?>">
          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
            Send new verification link
          </button>
        </form>
      </div>

    <?php else: ?>
      <!-- Matches Figma 24 Email Verification.png -->
      <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--color-peach); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px;">
          ✉
        </div>
        <h1 style="font-size: 28px; margin-bottom: 12px;">Verify your email address</h1>
        <p style="font-size: 16px; color: var(--color-muted); line-height: 1.6; margin-bottom: 24px;">
          We have sent a verification email to <strong><?= e($email ?: 'your registered email') ?></strong>. Please open the link in your inbox to complete activation.
        </p>

        <?php if ($token): ?>
          <!-- Local Development Quick Activation Banner -->
          <div style="background: #f0f7ff; border: 1px dashed #0284c7; padding: 18px; border-radius: var(--radius-sm); margin-bottom: 24px; text-align: left; font-size: 14px;">
            <div style="font-weight: 700; color: #0369a1; margin-bottom: 6px;">
              ⚡ Local Development Quick-Activate
            </div>
            <div style="color: #075985; margin-bottom: 12px;">
              In local testing mode, you can immediately activate your account with 1-click below:
            </div>
            <a href="<?= BASE_URL ?>/verify_email.php?token=<?= e($token) ?>" class="btn btn-primary btn-sm">
              Activate Account Now →
            </a>
          </div>
        <?php endif; ?>

        <form method="POST" action="" style="margin-top: 16px;">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="resend_email" value="<?= e($email) ?>">
          <button type="submit" class="btn btn-outline" style="width: 100%;">
            Resend email
          </button>
        </form>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
