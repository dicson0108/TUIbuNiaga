<?php
/**
 * Support Ticket Conversation Thread
 * Matches Figma: 02 Member workspace/20 Support Ticket Thread.png
 */

require_once __DIR__ . '/../includes/user_header.php';
$pdo = get_db();
$userId = $user['id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT t.*, u.name as opener_name, m.name as assignee_name 
    FROM tickets t 
    JOIN users u ON t.user_id = u.id 
    LEFT JOIN users m ON t.assigned_to = m.id 
    WHERE t.id = ? AND t.user_id = ?");
$stmt->execute([$id, $userId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    header("Location: " . BASE_URL . "/member/tickets.php");
    exit;
}

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $message = trim($_POST['message'] ?? '');
    if ($message) {
        $attachmentPath = null;
        if (!empty($_FILES['attachment']['name'])) {
            $res = handle_file_upload($_FILES['attachment'], 'uploads/documents/');
            if ($res['success']) {
                $attachmentPath = $res['file_path'];
            }
        }

        $pdo->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message, attachment_path, created_at) VALUES (?, ?, ?, ?, NOW())")->execute([$id, $userId, $message, $attachmentPath]);
        
        // Reopen ticket if resolved/closed
        $newStatus = ($ticket['status'] === 'Resolved' || $ticket['status'] === 'Closed') ? 'In Progress' : $ticket['status'];
        $pdo->prepare("UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$newStatus, $id]);

        log_audit('REPLY_TICKET', 'tickets', $id, "Applicant replied to ticket #{$ticket['ticket_no']}");
        set_flash('success', 'Your reply has been posted to the ticket thread.');
        header("Location: " . BASE_URL . "/member/ticket_thread.php?id=" . $id);
        exit;
    }
}

// Fetch replies
$stmtReplies = $pdo->prepare("SELECT r.*, u.name as author_name, u.role as author_role FROM ticket_replies r JOIN users u ON r.user_id = u.id WHERE r.ticket_id = ? ORDER BY r.created_at ASC");
$stmtReplies->execute([$id]);
$replies = $stmtReplies->fetchAll();

$pageTitle = "Ticket #" . $ticket['ticket_no'];
?>

<div style="max-width: 820px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/member/tickets.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to my tickets</a>
  </div>

  <!-- Ticket Header Card -->
  <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 28px; margin-bottom: 32px; box-shadow: var(--shadow-subtle);">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
      <div>
        <div class="card-category" style="margin-bottom: 4px;">TICKET #<?= e($ticket['ticket_no']) ?> · <?= e($ticket['category']) ?></div>
        <h1 style="font-size: 26px; margin-bottom: 6px;"><?= e($ticket['subject']) ?></h1>
        <div style="font-size: 13px; color: var(--color-muted);">
          Opened on <?= date('d M Y, h:i A', strtotime($ticket['created_at'])) ?> · Assigned to: <strong><?= e($ticket['assignee_name'] ?: 'Community Operations Pool') ?></strong>
        </div>
      </div>
      <div style="display: flex; align-items: center; gap: 8px;">
        <span class="badge badge-neutral">Priority: <?= e($ticket['priority']) ?></span>
        <?= status_badge($ticket['status']) ?>
      </div>
    </div>
  </div>

  <!-- Conversation Thread -->
  <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 36px;">
    <?php foreach ($replies as $rep): ?>
      <?php $isStaff = ($rep['author_role'] === 'moderator' || $rep['author_role'] === 'admin'); ?>
      <div style="background: <?= $isStaff ? '#fdf8f8' : '#fff' ?>; border: 1px solid <?= $isStaff ? 'var(--color-peach-border)' : 'var(--color-line)' ?>; border-radius: var(--radius-md); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="font-weight: 700; color: var(--color-ink);">
              <?= e($rep['author_name']) ?>
            </div>
            <?php if ($isStaff): ?>
              <span class="badge badge-peach">Community Moderator</span>
            <?php else: ?>
              <span class="badge badge-neutral">You</span>
            <?php endif; ?>
          </div>
          <div class="text-caption">
            <?= date('d M Y, h:i A', strtotime($rep['created_at'])) ?>
          </div>
        </div>

        <div style="font-size: 15px; line-height: 1.6; color: var(--color-ink); margin-bottom: 12px;">
          <?= nl2br(e($rep['message'])) ?>
        </div>

        <?php if (!empty($rep['attachment_path'])): ?>
          <div style="margin-top: 10px;">
            <a href="<?= BASE_URL ?>/<?= e($rep['attachment_path']) ?>" target="_blank" class="btn btn-outline btn-sm">
              📎 View Attachment
            </a>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Reply Form -->
  <div class="form-card" style="max-width: 100%;">
    <h3 style="margin-bottom: 16px;">Post a Reply</h3>
    <form method="POST" action="" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div class="form-group">
        <textarea name="message" class="form-textarea" required placeholder="Type your reply here..."></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" style="font-size: 13px;">Optional Attachment</label>
        <input type="file" name="attachment" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
      </div>

      <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
        <button type="submit" class="btn btn-primary">Send Reply</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
