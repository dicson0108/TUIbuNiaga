<?php
/**
 * User Registration Page
 * Matches Figma: 20 Register.png, 29 Register · BM.png, 30 Register · 中文.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/i18n.php';

if (is_logged_in()) {
    redirect_by_role($_SESSION['user']['role']);
}

$pdo = get_db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $businessName = trim($_POST['business_name'] ?? '');
    $language = in_array($_POST['language'] ?? '', ['en', 'bm', 'zh']) ? $_POST['language'] : current_lang();
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!$name) $errors[] = 'Full Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Check if email already registered
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            $errors[] = 'This email address is already registered. Please log in.';
        } else {
            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $stmtIns = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, language, status, phone, business_name, created_at) VALUES (?, ?, ?, 'user', ?, 'active', ?, ?, NOW())");
            $stmtIns->execute([$name, $email, $passHash, $language, $phone, $businessName]);
            $newUserId = $pdo->lastInsertId();

            // Generate email verification token
            $token = bin2hex(random_bytes(24));
            $stmtToken = $pdo->prepare("INSERT INTO email_verifications (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))");
            $stmtToken->execute([$email, $token]);

            // Welcome in-app notification
            create_notification(
                $newUserId,
                'welcome',
                "Welcome to Taylor's Ibu Niaga!",
                "Your account has been created. Complete your email verification to submit applications and list in the Open Bazaar.",
                'member/profile.php'
            );

            // Send confirmation email with verification link
            $verifyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                       . "://" . $_SERVER['HTTP_HOST'] . BASE_URL . "/verify_email.php?token=" . $token;

            $emailBody = "<h3>Welcome to Taylor's Ibu Niaga Platform</h3>"
                       . "<p>Hi {$name},</p>"
                       . "<p>Thank you for registering. Please click the button below to verify your email address and activate your account:</p>"
                       . "<p><a href='{$verifyUrl}' style='display:inline-block;padding:12px 24px;background:#d71920;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;'>Verify Email Address</a></p>"
                       . "<p>Or copy this link into your browser: <br><a href='{$verifyUrl}'>{$verifyUrl}</a></p>"
                       . "<p>This link expires in 24 hours.</p>";

            send_app_email($email, "Verify your email · Taylor's Ibu Niaga", $emailBody);

            // Audit log
            log_audit('USER_REGISTER', 'users', $newUserId, "User registered with email: {$email}");

            // Redirect to Email Verification instruction screen
            header("Location: " . BASE_URL . "/verify_email.php?sent=1&email=" . urlencode($email) . "&token=" . $token);
            exit;
        }
    }
}

$pageTitle = __('reg_title', 'Create your account');
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 580px;">
    <div style="text-align: center; margin-bottom: 32px;">
      <h1 style="font-size: 32px; margin-bottom: 8px;"><?= __('reg_title', 'Create your account') ?></h1>
      <p style="color: var(--color-muted); font-size: 15px;">
        <?= __('reg_subtitle', 'Join Taylor\'s Ibu Niaga community and step into your entrepreneurial potential.') ?>
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
          <label class="form-label"><?= __('form_name', 'Full Name') ?> *</label>
          <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" class="form-input" required placeholder="e.g. Aina Rahman">
        </div>

        <div class="form-group">
          <label class="form-label"><?= __('form_email', 'Email Address') ?> *</label>
          <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" class="form-input" required placeholder="name@domain.com">
        </div>

        <div class="form-group">
          <label class="form-label">Phone Number (Optional)</label>
          <input type="text" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" class="form-input" placeholder="+60 12-345 6789">
        </div>

        <div class="form-group">
          <label class="form-label">Business / Brand Name (Optional)</label>
          <input type="text" name="business_name" value="<?= e($_POST['business_name'] ?? '') ?>" class="form-input" placeholder="e.g. Dapur Aina">
        </div>

        <div class="form-group">
          <label class="form-label"><?= __('form_language', 'Preferred Language') ?></label>
          <select name="language" class="form-select">
            <option value="en" <?= (current_lang() === 'en') ? 'selected' : '' ?>>English</option>
            <option value="bm" <?= (current_lang() === 'bm') ? 'selected' : '' ?>>Bahasa Melayu</option>
            <option value="zh" <?= (current_lang() === 'zh') ? 'selected' : '' ?>>中文 (Chinese)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label"><?= __('form_password', 'Password') ?> *</label>
          <input type="password" name="password" class="form-input" required placeholder="Minimum 6 characters">
        </div>

        <div class="form-group">
          <label class="form-label"><?= __('form_confirm_password', 'Confirm Password') ?> *</label>
          <input type="password" name="confirm_password" class="form-input" required placeholder="Re-enter password">
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 8px;">
          <?= __('form_submit_reg', 'Register account') ?>
        </button>

        <div style="text-align: center; margin-top: 24px; font-size: 14px; color: var(--color-muted);">
          Already have an account? <a href="<?= BASE_URL ?>/login.php" style="color: var(--color-primary); font-weight: 600;">Log in here</a>
        </div>
      </form>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
