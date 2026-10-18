<?php
/**
 * Admin System Settings
 * Matches Figma: 04 Admin workspace/13 System Settings.png
 */

$pageTitle = "System Settings";
require_once __DIR__ . '/../includes/admin_header.php';
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $settings = [
        'bazaar_approval_required' => $_POST['bazaar_approval_required'] ?? '0',
        'max_upload_size_mb'       => (int)($_POST['max_upload_size_mb'] ?? 5),
        'enable_email_notifications' => $_POST['enable_email_notifications'] ?? '0',
        'smtp_host'                => trim($_POST['smtp_host'] ?? 'localhost'),
        'smtp_port'                => trim($_POST['smtp_port'] ?? '587'),
        'smtp_user'                => trim($_POST['smtp_user'] ?? ''),
        'smtp_pass'                => trim($_POST['smtp_pass'] ?? ''),
        'smtp_from_email'          => trim($_POST['smtp_from_email'] ?? 'no-reply@taylors.edu.my'),
        'smtp_from_name'           => trim($_POST['smtp_from_name'] ?? "Taylor's Ibu Niaga Platform")
    ];

    $stmtUp = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($settings as $k => $v) {
        $stmtUp->execute([$k, (string)$v]);
    }

    log_audit('UPDATE_SETTINGS', 'system_settings', null, "Admin updated global system settings");
    set_flash('success', 'System settings saved successfully.');
    header('Location: ' . BASE_URL . '/admin/settings.php');
    exit;
}

// Fetch all current settings
$stmtAll = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
$current = [];
while ($row = $stmtAll->fetch()) {
    $current[$row['setting_key']] = $row['setting_value'];
}
?>

<div style="max-width: 760px;">
  <div style="margin-bottom: 28px;">
    <h1 style="font-size: 32px; margin-bottom: 4px;">System Settings</h1>
    <p style="color: var(--color-muted);">Global platform policies, bazaar moderation rules, upload size thresholds, and SMTP mailer configurations.</p>
  </div>

  <div class="form-card" style="max-width: 100%;">
    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div style="border-bottom: 1px solid var(--color-line); padding-bottom: 12px; margin-bottom: 20px;">
        <h3 style="font-size: 18px; color: var(--color-primary);">Open Bazaar Policies</h3>
      </div>

      <div class="form-group">
        <label class="form-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
          <input type="checkbox" name="bazaar_approval_required" value="1" <?= (($current['bazaar_approval_required'] ?? '1') == '1') ? 'checked' : '' ?> style="width: 18px; height: 18px;">
          <span>Require Moderator Approval for Open Bazaar Listings</span>
        </label>
        <div class="form-hint" style="margin-left: 28px;">
          When enabled, new products or services submitted by members remain in "Pending" status until explicitly approved by a moderator.
        </div>
      </div>

      <div style="border-bottom: 1px solid var(--color-line); padding-bottom: 12px; margin: 32px 0 20px;">
        <h3 style="font-size: 18px; color: var(--color-primary);">File Upload Configuration</h3>
      </div>

      <div class="form-group">
        <label class="form-label">Maximum Upload Size (MB)</label>
        <input type="number" name="max_upload_size_mb" value="<?= (int)($current['max_upload_size_mb'] ?? 5) ?>" class="form-input" min="1" max="50" style="max-width: 200px;">
        <div class="form-hint">Applies to identity MyKad copies, business proposal PDFs, and bazaar item photos.</div>
      </div>

      <div style="border-bottom: 1px solid var(--color-line); padding-bottom: 12px; margin: 32px 0 20px;">
        <h3 style="font-size: 18px; color: var(--color-primary);">Email & SMTP Notifications (Hybrid Mailer)</h3>
      </div>

      <div class="form-group">
        <label class="form-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
          <input type="checkbox" name="enable_email_notifications" value="1" <?= (($current['enable_email_notifications'] ?? '1') == '1') ? 'checked' : '' ?> style="width: 18px; height: 18px;">
          <span>Enable Outgoing Email Notifications (Auto-logged to DB Outbox)</span>
        </label>
      </div>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">SMTP Host</label>
          <input type="text" name="smtp_host" value="<?= e($current['smtp_host'] ?? 'localhost') ?>" class="form-input" placeholder="smtp.gmail.com or mail.taylors.edu.my">
        </div>

        <div class="form-group">
          <label class="form-label">SMTP Port</label>
          <input type="text" name="smtp_port" value="<?= e($current['smtp_port'] ?? '587') ?>" class="form-input" placeholder="587 or 465">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">SMTP Username</label>
          <input type="text" name="smtp_user" value="<?= e($current['smtp_user'] ?? '') ?>" class="form-input" placeholder="user@taylors.edu.my">
        </div>

        <div class="form-group">
          <label class="form-label">SMTP Password</label>
          <input type="password" name="smtp_pass" value="<?= e($current['smtp_pass'] ?? '') ?>" class="form-input" placeholder="••••••••">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Sender Email</label>
          <input type="email" name="smtp_from_email" value="<?= e($current['smtp_from_email'] ?? 'no-reply@taylors.edu.my') ?>" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label">Sender Display Name</label>
          <input type="text" name="smtp_from_name" value="<?= e($current['smtp_from_name'] ?? "Taylor's Ibu Niaga Platform") ?>" class="form-input">
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--color-line);">
        <button type="submit" class="btn btn-primary btn-lg">
          Save Settings
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
