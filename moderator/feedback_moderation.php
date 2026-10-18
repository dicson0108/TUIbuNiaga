<?php
/**
 * Moderator Feedback & Reviews Moderation
 * Matches Figma: 03 Moderator workspace/07 Feedback Moderation.png
 */

$pageTitle = "Feedback Moderation";
require_once __DIR__ . '/../includes/mod_header.php';
$pdo = get_db();

// Handle Hide / Unhide / Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();
    $feedId = (int)$_POST['feedback_id'];
    $act = $_POST['action'];

    if ($act === 'hide') {
        $pdo->prepare("UPDATE event_feedback SET status = 'hidden' WHERE id = ?")->execute([$feedId]);
        log_audit('HIDE_FEEDBACK', 'event_feedback', $feedId, "Hidden feedback #{$feedId}");
        set_flash('info', 'Comment hidden from public event view.');
    } elseif ($act === 'unhide') {
        $pdo->prepare("UPDATE event_feedback SET status = 'visible' WHERE id = ?")->execute([$feedId]);
        log_audit('UNHIDE_FEEDBACK', 'event_feedback', $feedId, "Made feedback #{$feedId} visible");
        set_flash('success', 'Comment restored to visible status.');
    } elseif ($act === 'delete') {
        $pdo->prepare("DELETE FROM event_feedback WHERE id = ?")->execute([$feedId]);
        log_audit('DELETE_FEEDBACK', 'event_feedback', $feedId, "Permanently deleted feedback #{$feedId}");
        set_flash('info', 'Comment permanently deleted.');
    }
    header('Location: ' . BASE_URL . '/moderator/feedback_moderation.php');
    exit;
}

$stmt = $pdo->query("SELECT fb.*, e.title as event_title, u.name as user_name, u.email as user_email 
    FROM event_feedback fb 
    JOIN events e ON fb.event_id = e.id 
    JOIN users u ON fb.user_id = u.id 
    ORDER BY fb.created_at DESC");
$feedbacks = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Feedback Moderation</h1>
    <p style="color: var(--color-muted);">Review attendee ratings and comments from campus workshops, and hide or remove inappropriate feedback.</p>
  </div>
</div>

<div class="table-wrapper">
  <table class="data-table">
    <thead>
      <tr>
        <th>Attendee</th>
        <th>Event</th>
        <th>Rating</th>
        <th>Comment</th>
        <th>Status</th>
        <th>Date</th>
        <th style="text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($feedbacks)): ?>
        <tr>
          <td colspan="7" style="text-align: center; color: var(--color-muted); padding: 48px;">
            No event feedback records found.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($feedbacks as $fb): ?>
          <tr>
            <td>
              <div style="font-weight: 700; color: var(--color-ink);"><?= e($fb['user_name']) ?></div>
              <div class="text-caption"><?= e($fb['user_email']) ?></div>
            </td>
            <td style="font-weight: 600;"><?= e($fb['event_title']) ?></td>
            <td style="color: #f59e0b; font-size: 14px; white-space: nowrap;">
              <?= str_repeat('★', (int)$fb['rating']) . str_repeat('☆', 5 - (int)$fb['rating']) ?>
            </td>
            <td style="max-width: 320px; line-height: 1.5; color: var(--color-ink);">
              <?= nl2br(e($fb['comment'])) ?>
            </td>
            <td>
              <?php if ($fb['status'] === 'visible'): ?>
                <span class="badge badge-positive">Visible</span>
              <?php else: ?>
                <span class="badge badge-destructive">Hidden</span>
              <?php endif; ?>
            </td>
            <td style="color: var(--color-muted); font-size: 13px; white-space: nowrap;">
              <?= date('d M Y', strtotime($fb['created_at'])) ?>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="feedback_id" value="<?= $fb['id'] ?>">

                <?php if ($fb['status'] === 'visible'): ?>
                  <button type="submit" name="action" value="hide" class="btn btn-outline btn-sm">
                    Hide Comment
                  </button>
                <?php else: ?>
                  <button type="submit" name="action" value="unhide" class="btn btn-secondary btn-sm">
                    Unhide
                  </button>
                <?php endif; ?>

                <button type="submit" name="action" value="delete" class="btn btn-destructive btn-sm" onclick="return confirm('Permanently delete this review?');">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
