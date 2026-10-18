<?php
/**
 * Member Profile & Settings
 * Matches Figma: 02 Member workspace/02 My Profile.png
 */

$pageTitle = "My Profile";
require_once __DIR__ . '/../includes/user_header.php';
$pdo = get_db();
$userId = $user['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $businessName = trim($_POST['business_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $language = in_array($_POST['language'] ?? '', ['en', 'bm', 'zh']) ? $_POST['language'] : 'en';

    if ($name) {
        $stmtUp = $pdo->prepare("UPDATE users SET name = ?, phone = ?, business_name = ?, bio = ?, language = ? WHERE id = ?");
        $stmtUp->execute([$name, $phone, $businessName, $bio, $language, $userId]);

        // Also update members directory record if exists
        $stmtMem = $pdo->prepare("UPDATE members SET name = ?, business_name = ?, bio = ? WHERE user_id = ?");
        $stmtMem->execute([$name, $businessName, $bio, $userId]);

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['business_name'] = $businessName;
        $_SESSION['user']['language'] = $language;
        $_SESSION['lang'] = $language;

        log_audit('UPDATE_PROFILE', 'users', $userId, "Updated profile details");
        set_flash('success', 'Your profile details have been saved successfully.');
        header('Location: ' . BASE_URL . '/member/profile.php');
        exit;
    } else {
        set_flash('error', 'Name cannot be blank.');
    }
}
?>

<div style="max-width: 720px;">
  <div style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin-bottom: 6px;">My Profile</h1>
    <p style="color: var(--color-muted);">Manage your personal details, business presence, and language preferences.</p>
  </div>

  <div class="form-card" style="max-width: 100%;">
    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" value="<?= e($userData['name']) ?>" class="form-input" required>
      </div>

      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" value="<?= e($userData['email']) ?>" class="form-input" disabled style="background-color: var(--color-sage);">
        <div class="form-hint">Email address cannot be modified directly. Contact administrator for identity changes.</div>
      </div>

      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input type="text" name="phone" value="<?= e($userData['phone'] ?? '') ?>" class="form-input" placeholder="+60 12-345 6789">
      </div>

      <div class="form-group">
        <label class="form-label">Business / Brand Name</label>
        <input type="text" name="business_name" value="<?= e($userData['business_name'] ?? '') ?>" class="form-input" placeholder="e.g. Aina's Kitchen">
      </div>

      <div class="form-group">
        <label class="form-label">Bio / Business Overview</label>
        <textarea name="bio" class="form-textarea" placeholder="Tell the community about your craft, products, or entrepreneurial journey..."><?= e($userData['bio'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Preferred Platform Language</label>
        <select name="language" class="form-select">
          <option value="en" <?= ($userData['language'] === 'en') ? 'selected' : '' ?>>English</option>
          <option value="bm" <?= ($userData['language'] === 'bm') ? 'selected' : '' ?>>Bahasa Melayu</option>
          <option value="zh" <?= ($userData['language'] === 'zh') ? 'selected' : '' ?>>中文 (Chinese)</option>
        </select>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 32px;">
        <a href="<?= BASE_URL ?>/member/change_password.php" class="btn btn-outline">
          Change password
        </a>
        <button type="submit" class="btn btn-primary">
          Save changes
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
