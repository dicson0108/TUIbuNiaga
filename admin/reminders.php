<?php
/**
 * Admin Reminder & Notification Settings
 * Matches Figma: 04 Admin workspace/11 Reminder & Notification Settings.png
 */

$pageTitle = "Reminder & Notification Settings";
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/mailer.php';
$pdo = get_db();

// Handle broadcast announcement submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'broadcast') {
    verify_csrf();
    $title = trim($_POST['broadcast_title'] ?? '');
    $message = trim($_POST['broadcast_message'] ?? '');
    $targetRole = $_POST['target_role'] ?? 'all';

    if ($title && $message) {
        $sqlUsers = "SELECT id, email, name FROM users WHERE status = 'active'";
        if ($targetRole === 'user') $sqlUsers .= " AND role = 'user'";
        if ($targetRole === 'moderator') $sqlUsers .= " AND role = 'moderator'";
        
        $recipients = $pdo->query($sqlUsers)->fetchAll();
        $count = 0;
        foreach ($recipients as $r) {
            create_notification($r['id'], 'broadcast', $title, $message, 'index.php');
            $count++;
        }

        log_audit('BROADCAST_ANNOUNCEMENT', 'notifications', null, "Dispatched broadcast '{$title}' to {$count} recipients");
        set_flash('success', "Broadcast announcement delivered to {$count} users!");
        header('Location: ' . BASE_URL . '/admin/reminders.php');
        exit;
    }
}

// Handle reminder toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_reminder') {
    verify_csrf();
    $settingKey = $_POST['setting_key'] ?? '';
    $val = $_POST['val'] ?? '0';

    $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?")->execute([$val, $settingKey]);
    set_flash('success', 'Automated reminder schedule updated.');
    header('Location: ' . BASE_URL . '/admin/reminders.php');
    exit;
}
?>

<div style="max-width: 900px;">
  <div style="margin-bottom: 28px;">
    <h1 style="font-size: 32px; margin-bottom: 4px;">Reminder & Notification Settings</h1>
    <p style="color: var(--color-muted);">Configure automated event countdown reminders, document upload deadlines, and broadcast university-wide notices.</p>
  </div>

  <!-- Automated Reminders Policy Grid (PRD SN-03) -->
  <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 28px; margin-bottom: 36px; box-shadow: var(--shadow-subtle);">
    <h2 style="font-size: 20px; margin-bottom: 16px;">Automated Reminder Triggers</h2>

    <div style="display: flex; flex-direction: column; gap: 16px;">
      <!-- Reminder Item 1 -->
      <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border: 1px solid var(--color-line); border-radius: var(--radius-sm);">
        <div>
          <div style="font-weight: 700; color: var(--color-ink);">Event Starting Reminder</div>
          <div style="font-size: 13px; color: var(--color-muted);">Sends email and notification 24 hours prior to registered campus workshop.</div>
        </div>
        <div>
          <span class="badge badge-positive">Active Schedule (24h prior)</span>
        </div>
      </div>

      <!-- Reminder Item 2 -->
      <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border: 1px solid var(--color-line); border-radius: var(--radius-sm);">
        <div>
          <div style="font-weight: 700; color: var(--color-ink);">Needs Documents Deadline Reminder</div>
          <div style="font-size: 13px; color: var(--color-muted);">Notifies applicants if requested re-upload documents remain pending after 48 hours.</div>
        </div>
        <div>
          <span class="badge badge-positive">Active Schedule (48h pending)</span>
        </div>
      </div>

      <!-- Reminder Item 3 -->
      <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border: 1px solid var(--color-line); border-radius: var(--radius-sm);">
        <div>
          <div style="font-weight: 700; color: var(--color-ink);">Unanswered Support Ticket SLA Alert</div>
          <div style="font-size: 13px; color: var(--color-muted);">Escalates tickets to lead administrator if unassigned or unanswered past 1 business day.</div>
        </div>
        <div>
          <span class="badge badge-positive">Active Schedule (&gt; 24h SLA)</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Broadcast Announcement Card (PRD SN-04) -->
  <div class="form-card" style="max-width: 100%;">
    <div class="card-category" style="margin-bottom: 4px;">COMMUNITY BROADCAST</div>
    <h2 style="font-size: 20px; margin-bottom: 10px;">Send Broadcast Announcement</h2>
    <p style="font-size: 14px; color: var(--color-muted); margin-bottom: 24px;">
      Deliver instant in-app alerts and notifications to all registered members or moderators.
    </p>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="broadcast">

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Announcement Title *</label>
          <input type="text" name="broadcast_title" class="form-input" required placeholder="e.g. Taylor's Lakeside Weekend Bazaar Setup Schedule">
        </div>

        <div class="form-group">
          <label class="form-label">Target Audience</label>
          <select name="target_role" class="form-select">
            <option value="all">All Community (Everyone)</option>
            <option value="user">Members / Participants Only</option>
            <option value="moderator">Moderators & Staff Only</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Announcement Message *</label>
        <textarea name="broadcast_message" class="form-textarea" required placeholder="Type the notification text delivered to member inboxes..."></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
        <button type="submit" class="btn btn-primary btn-lg">
          Dispatch Announcement
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
