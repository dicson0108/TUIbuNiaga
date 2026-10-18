<?php
/**
 * Moderator Ticket Management Detail & Reply
 * Matches Figma: 03 Moderator workspace/09 Ticket Management Detail.png
 */

require_once __DIR__ . '/../includes/mod_header.php';
require_once __DIR__ . '/../includes/mailer.php';
$pdo = get_db();
$modId = $user['id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT t.*, u.name as opener_name, u.email as opener_email, m.name as assignee_name 
    FROM tickets t 
    JOIN users u ON t.user_id = u.id 
    LEFT JOIN users m ON t.assigned_to = m.id 
    WHERE t.id = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    header("Location: " . BASE_URL . "/moderator/tickets.php");
    exit;
}

// Fetch moderators list for assignment dropdown
$moderators = $pdo->query("SELECT id, name FROM users WHERE role IN ('moderator', 'admin') ORDER BY name ASC")->fetchAll();

// Handle Status & Assignment Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_meta') {
    verify_csrf();
    $status = $_POST['status'] ?? $ticket['status'];
    $priority = $_POST['priority'] ?? $ticket['priority'];
    $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

    $stmtUp = $pdo->prepare("UPDATE tickets SET status = ?, priority = ?, assigned_to = ?, updated_at = NOW() WHERE id = ?");
    $stmtUp->execute([$status, $priority, $assignedTo, $id]);

    log_audit('UPDATE_TICKET_META', 'tickets', $id, "Updated ticket #{$ticket['ticket_no']}: status={$status}, priority={$priority}");
    set_flash('success', "Ticket parameters updated successfully.");
    header("Location: " . BASE_URL . "/moderator/ticket_detail.php?id=" . $id);
    exit;
}

// Handle Moderator Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    verify_csrf();
    $message = trim($_POST['message'] ?? '');
    $newStatus = $_POST['reply_status'] ?? 'In Progress';

    if ($message) {
        $attachmentPath = null;
        if (!empty($_FILES['attachment']['name'])) {
            $res = handle_file_upload($_FILES['attachment'], 'uploads/documents/');
            if ($res['success']) {
                $attachmentPath = $res['file_path'];
            }
        }

        $pdo->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message, attachment_path, created_at) VALUES (?, ?, ?, ?, NOW())")
            ->execute([$id, $modId, $message, $attachmentPath]);

        // Auto assign to replying moderator if unassigned
        $assigned = $ticket['assigned_to'] ?: $modId;
        $pdo->prepare("UPDATE tickets SET status = ?, assigned_to = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$newStatus, $assigned, $id]);

        // In-app notification to member
        create_notification(
            $ticket['user_id'],
            'ticket',
            "Reply on Ticket #{$ticket['ticket_no']}",
            "Moderator {$user['name']} replied: " . substr($message, 0, 80) . "...",
            "member/ticket_thread.php?id={$id}"
        );

        // Email to member
        $emailBody = "<h3>Update on Support Ticket #{$ticket['ticket_no']}</h3>"
                   . "<p>Hi {$ticket['opener_name']},</p>"
                   . "<p>Our community operations team has replied to your inquiry: <strong>{$ticket['subject']}</strong></p>"
                   . "<div style='background:#f9f9f9;padding:16px;border-left:4px solid #d71920;margin:16px 0;'>"
                   . nl2br(htmlspecialchars($message))
                   . "</div>"
                   . "<p>Log into your Member Workspace to view the full discussion or reply.</p>";

        send_app_email($ticket['opener_email'], "Ticket #{$ticket['ticket_no']} Update · Taylor's Ibu Niaga", $emailBody);

        log_audit('MODERATOR_REPLY_TICKET', 'tickets', $id, "Moderator replied to ticket #{$ticket['ticket_no']}");
        set_flash('success', "Your reply has been sent and ticket status updated to '{$newStatus}'.");
        header("Location: " . BASE_URL . "/moderator/ticket_detail.php?id=" . $id);
        exit;
    }
}

// Fetch thread replies
$stmtReplies = $pdo->prepare("SELECT r.*, u.name as author_name, u.role as author_role FROM ticket_replies r JOIN users u ON r.user_id = u.id WHERE r.ticket_id = ? ORDER BY r.created_at ASC");
$stmtReplies->execute([$id]);
$replies = $stmtReplies->fetchAll();

$pageTitle = "Ticket #" . $ticket['ticket_no'] . " · " . $ticket['subject'];
?>

<div style="max-width: 960px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/moderator/tickets.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to Ticket Queue</a>
  </div>

  <div style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 28px; align-items: start;">
    <!-- Left: Conversation Thread & Reply Form -->
    <div>
      <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px;">
        <div class="card-category" style="margin-bottom: 4px;">#<?= e($ticket['ticket_no']) ?> · <?= e($ticket['category']) ?></div>
        <h1 style="font-size: 24px; margin-bottom: 6px;"><?= e($ticket['subject']) ?></h1>
        <div style="font-size: 13px; color: var(--color-muted);">
          Opened by <strong><?= e($ticket['opener_name']) ?></strong> (<?= e($ticket['opener_email']) ?>) on <?= date('d M Y, h:i A', strtotime($ticket['created_at'])) ?>
        </div>
      </div>

      <!-- Thread messages -->
      <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 32px;">
        <?php foreach ($replies as $rep): ?>
          <?php $isStaff = ($rep['author_role'] === 'moderator' || $rep['author_role'] === 'admin'); ?>
          <div style="background: <?= $isStaff ? '#fdf8f8' : '#fff' ?>; border: 1px solid <?= $isStaff ? 'var(--color-peach-border)' : 'var(--color-line)' ?>; border-radius: var(--radius-md); padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <div style="display: flex; align-items: center; gap: 8px;">
                <div style="font-weight: 700; color: var(--color-ink);"><?= e($rep['author_name']) ?></div>
                <?php if ($isStaff): ?>
                  <span class="badge badge-peach">Moderator Team</span>
                <?php else: ?>
                  <span class="badge badge-neutral">Applicant</span>
                <?php endif; ?>
              </div>
              <div class="text-caption"><?= date('d M, h:i A', strtotime($rep['created_at'])) ?></div>
            </div>

            <div style="font-size: 14px; line-height: 1.6; color: var(--color-ink);">
              <?= nl2br(e($rep['message'])) ?>
            </div>

            <?php if (!empty($rep['attachment_path'])): ?>
              <div style="margin-top: 10px;">
                <a href="<?= BASE_URL ?>/<?= e($rep['attachment_path']) ?>" target="_blank" class="btn btn-outline btn-sm">
                  📎 View Attachment ↗
                </a>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Reply Box -->
      <div class="form-card" style="max-width: 100%;">
        <h3 style="margin-bottom: 16px;">Post Official Moderator Reply</h3>
        <form method="POST" action="" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="reply">

          <div class="form-group">
            <textarea name="message" class="form-textarea" required placeholder="Type guidance or instructions to the user..."></textarea>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group" style="margin: 0;">
              <label class="form-label" style="font-size: 13px;">Status after reply</label>
              <select name="reply_status" class="form-select">
                <option value="In Progress" <?= ($ticket['status'] === 'In Progress') ? 'selected' : '' ?>>In Progress (Awaiting follow-up)</option>
                <option value="Resolved">Resolved (Issue addressed)</option>
                <option value="Closed">Closed</option>
              </select>
            </div>

            <div class="form-group" style="margin: 0;">
              <label class="form-label" style="font-size: 13px;">Optional Attachment</label>
              <input type="file" name="attachment" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
            </div>
          </div>

          <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button type="submit" class="btn btn-primary">
              Send Reply & Update Ticket
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Right: Ticket Properties & Assignment Sidebar -->
    <div style="background: var(--color-cream); border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 24px;">
      <h3 style="font-size: 16px; margin-bottom: 16px;">Ticket Properties</h3>

      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="update_meta">

        <div class="form-group">
          <label class="form-label" style="font-size: 13px;">Status</label>
          <select name="status" class="form-select">
            <option value="Open" <?= ($ticket['status'] === 'Open') ? 'selected' : '' ?>>Open</option>
            <option value="In Progress" <?= ($ticket['status'] === 'In Progress') ? 'selected' : '' ?>>In Progress</option>
            <option value="Resolved" <?= ($ticket['status'] === 'Resolved') ? 'selected' : '' ?>>Resolved</option>
            <option value="Closed" <?= ($ticket['status'] === 'Closed') ? 'selected' : '' ?>>Closed</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" style="font-size: 13px;">Priority</label>
          <select name="priority" class="form-select">
            <option value="Low" <?= ($ticket['priority'] === 'Low') ? 'selected' : '' ?>>Low</option>
            <option value="Medium" <?= ($ticket['priority'] === 'Medium') ? 'selected' : '' ?>>Medium</option>
            <option value="High" <?= ($ticket['priority'] === 'High') ? 'selected' : '' ?>>High</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" style="font-size: 13px;">Assignee</label>
          <select name="assigned_to" class="form-select">
            <option value="">Unassigned</option>
            <?php foreach ($moderators as $m): ?>
              <option value="<?= $m['id'] ?>" <?= ($ticket['assigned_to'] == $m['id']) ? 'selected' : '' ?>>
                <?= e($m['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; background: #fff; margin-top: 10px;">
          Update Ticket Settings
        </button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
