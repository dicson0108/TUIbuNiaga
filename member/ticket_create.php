<?php
/**
 * Create Support Ticket
 * Matches Figma: 02 Member workspace/19 Create Support Ticket.png
 */

$pageTitle = "Create Support Ticket";
require_once __DIR__ . '/../includes/user_header.php';
require_once __DIR__ . '/../includes/mailer.php';
$pdo = get_db();
$userId = $user['id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $category = trim($_POST['category'] ?? '');
    $priority = in_array($_POST['priority'] ?? '', ['Low', 'Medium', 'High']) ? $_POST['priority'] : 'Medium';
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$category || !$subject || !$message) {
        $errors[] = 'Please fill in category, subject, and message.';
    }

    if (empty($errors)) {
        $attachmentPath = null;
        if (!empty($_FILES['attachment']['name'])) {
            $res = handle_file_upload($_FILES['attachment'], 'uploads/documents/');
            if ($res['success']) {
                $attachmentPath = $res['file_path'];
            }
        }

        $ticketNo = 'SUP-' . str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT);
        $stmtIns = $pdo->prepare("INSERT INTO tickets (ticket_no, user_id, category, priority, subject, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'Open', NOW(), NOW())");
        $stmtIns->execute([$ticketNo, $userId, $category, $priority, $subject]);
        $ticketId = $pdo->lastInsertId();

        // Create initial reply record
        $stmtReply = $pdo->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message, attachment_path, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmtReply->execute([$ticketId, $userId, $message, $attachmentPath]);

        // Audit log
        log_audit('CREATE_TICKET', 'tickets', $ticketId, "Created support ticket #{$ticketNo}");

        // In-app notification to user
        create_notification($userId, 'ticket', "Ticket #{$ticketNo} Created", "Your ticket '{$subject}' has been submitted. A moderator will review it shortly.", "member/ticket_thread.php?id={$ticketId}");

        set_flash('success', "Support ticket #{$ticketNo} has been submitted successfully.");
        header('Location: ' . BASE_URL . '/member/ticket_thread.php?id=' . $ticketId);
        exit;
    }
}
?>

<div style="max-width: 720px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/member/tickets.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to my tickets</a>
  </div>

  <div style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin-bottom: 6px;">Create Support Ticket</h1>
    <p style="color: var(--color-muted);">Our operations team will assist you with any questions or technical issues.</p>
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
    <form method="POST" action="" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Category *</label>
          <select name="category" class="form-select" required>
            <option value="Program Application">Program Application & Documents</option>
            <option value="Events & Workshops">Events & Workshops</option>
            <option value="Open Bazaar">Open Bazaar Listing</option>
            <option value="Account & Profile">Account & Profile Settings</option>
            <option value="General Inquiry">General Inquiry</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Priority</label>
          <select name="priority" class="form-select">
            <option value="Low">Low</option>
            <option value="Medium" selected>Medium</option>
            <option value="High">High (Urgent)</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Subject *</label>
        <input type="text" name="subject" class="form-input" required placeholder="Brief summary of your question or issue">
      </div>

      <div class="form-group">
        <label class="form-label">Detailed Description / Question *</label>
        <textarea name="message" class="form-textarea" required style="min-height: 140px;" placeholder="Provide details, error messages, or context to help our staff resolve this quickly..."></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Optional Attachment (PDF, Image · Max 5MB)</label>
        <input type="file" name="attachment" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--color-line);">
        <a href="<?= BASE_URL ?>/member/tickets.php" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Submit Ticket</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
