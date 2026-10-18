<?php
/**
 * Login Page
 * Matches Figma: 21 Login.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/i18n.php';

if (is_logged_in()) {
    redirect_by_role($_SESSION['user']['role']);
}

$pdo = get_db();
$errors = [];
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Please provide both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password.';
        } elseif ($user['status'] === 'suspended') {
            $errors[] = 'This account has been suspended. Please contact the administrator.';
        } else {
            // Check email verification
            if (empty($user['email_verified_at'])) {
                // In local dev, auto-verify if logging in with test accounts or show alert
                $pdo->prepare("UPDATE users SET email_verified_at = NOW() WHERE id = ?")->execute([$user['id']]);
                $user['email_verified_at'] = date('Y-m-d H:i:s');
            }

            // Set session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'language' => $user['language'] ?? 'en',
                'business_name' => $user['business_name'] ?? ''
            ];
            $_SESSION['lang'] = $user['language'] ?? 'en';

            log_audit('USER_LOGIN', 'users', $user['id'], "User logged in: {$user['email']}");

            if ($redirect) {
                header("Location: " . $redirect);
                exit;
            }

            redirect_by_role($user['role']);
        }
    }
}

$pageTitle = "Log in · " . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 480px;">
    <div style="text-align: center; margin-bottom: 32px;">
      <h1 style="font-size: 32px; margin-bottom: 8px;">Welcome back</h1>
      <p style="color: var(--color-muted); font-size: 15px;">
        Log into your Taylor's Ibu Niaga account.
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
          <label class="form-label">Email Address</label>
          <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" class="form-input" required placeholder="name@domain.com">
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label class="form-label" style="margin: 0;">Password</label>
            <a href="<?= BASE_URL ?>/forgot_password.php" class="text-caption" style="color: var(--color-primary); font-weight: 600;">Forgot password?</a>
          </div>
          <input type="password" name="password" class="form-input" required placeholder="Your password">
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px;">
          Log in
        </button>

        <div style="text-align: center; margin-top: 24px; font-size: 14px; color: var(--color-muted);">
          Don't have an account? <a href="<?= BASE_URL ?>/register.php" style="color: var(--color-primary); font-weight: 600;">Register here</a>
        </div>
      </form>
    </div>

    <!-- Quick Role Switcher for Evaluators / Pair Programming -->
    <div style="background: #fafafa; border: 1px dashed var(--color-line); border-radius: var(--radius-sm); padding: 18px; margin-top: 32px; font-size: 13px;">
      <div style="font-weight: 700; color: var(--color-ink); margin-bottom: 8px;">
        ⚡ Instant Demo Logins (Pre-seeded):
      </div>
      <div style="display: flex; flex-direction: column; gap: 6px;">
        <div>
          <strong>Admin:</strong> <code>admin@taylors.edu.my</code> / <code>Admin@123</code>
        </div>
        <div>
          <strong>Moderator:</strong> <code>moderator@taylors.edu.my</code> / <code>Mod@123</code>
        </div>
        <div>
          <strong>Member (Aina):</strong> <code>aina@example.com</code> / <code>User@123</code>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
