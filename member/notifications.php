<?php
/**
 * Member In-App Notifications Feed
 * Matches Figma: 02 Member workspace/17 Notifications.png
 */

$pageTitle = "Notifications";
require_once __DIR__ . '/../includes/user_header.php';
$pdo = get_db();
$userId = $user['id'];

// Handle Mark All Read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    verify_csrf();
    $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL")->execute([$userId]);
    set_flash('success', 'All notifications marked as read.');
    header('Location: ' . BASE_URL . '/member/notifications.php');
    exit;
}

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Notifications</h1>
    <p style="color: var(--color-muted);">Stay informed with real-time updates on applications, event waitlists, and buyer inquiries.</p>
  </div>

  <?php if ($unreadNotifCount > 0): ?>
    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="mark_all_read">
      <button type="submit" class="btn btn-outline btn-sm">
        ✓ Mark all as read
      </button>
    </form>
  <?php endif; ?>
</div>

<div class="table-wrapper">
  <table class="data-table">
    <thead>
      <tr>
        <th>Notification</th>
        <th>Category</th>
        <th>Time</th>
        <th style="text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($notifications)): ?>
        <tr>
          <td colspan="4" style="text-align: center; color: var(--color-muted); padding: 48px;">
            No notifications in your inbox.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($notifications as $n): ?>
          <tr style="<?= empty($n['read_at']) ? 'background-color: #fff9f9;' : '' ?>">
            <td>
              <div style="display: flex; align-items: center; gap: 10px;">
                <?php if (empty($n['read_at'])): ?>
                  <span style="width: 8px; height: 8px; border-radius: 50%; background-color: var(--color-primary); display: inline-block;"></span>
                <?php else: ?>
                  <span style="width: 8px; height: 8px; display: inline-block;"></span>
                <?php endif; ?>
                <div>
                  <div style="font-weight: 700; color: var(--color-ink);"><?= e($n['title']) ?></div>
                  <div style="font-size: 13px; color: var(--color-muted); margin-top: 2px;"><?= e($n['message']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <span class="badge badge-neutral" style="text-transform: capitalize;"><?= e($n['type']) ?></span>
            </td>
            <td style="color: var(--color-muted); font-size: 13px; white-space: nowrap;">
              <?= date('d M Y · h:i A', strtotime($n['created_at'])) ?>
            </td>
            <td style="text-align: right;">
              <?php if (!empty($n['link'])): ?>
                <a href="<?= BASE_URL ?>/<?= e($n['link']) ?>" class="btn btn-secondary btn-sm">
                  View →
                </a>
              <?php else: ?>
                <span class="text-caption" style="color: var(--color-muted);">System</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
