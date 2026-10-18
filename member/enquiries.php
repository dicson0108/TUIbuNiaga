<?php
/**
 * Member Bazaar Enquiries & Orders Inbox
 * Matches Figma: 02 Member workspace/15 Enquiries.png & 16 Enquiries · Sent.png
 */

$pageTitle = "Bazaar Enquiries";
require_once __DIR__ . '/../includes/user_header.php';
require_once __DIR__ . '/../includes/mailer.php';
$pdo = get_db();
$userId = $user['id'];

// Handle Reply Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    verify_csrf();
    $enqId = (int)$_POST['enquiry_id'];
    $replyText = trim($_POST['reply_text'] ?? '');

    $stmtEnq = $pdo->prepare("SELECT e.*, b.title as listing_title FROM enquiries e JOIN bazaar_listings b ON e.listing_id = b.id WHERE e.id = ? AND e.seller_id = ?");
    $stmtEnq->execute([$enqId, $userId]);
    $enquiry = $stmtEnq->fetch();

    if ($enquiry && $replyText) {
        $pdo->prepare("UPDATE enquiries SET reply_text = ?, status = 'replied', replied_at = NOW() WHERE id = ?")->execute([$replyText, $enqId]);

        // Email the buyer the seller's reply
        $buyerEmail = $enquiry['sender_email'];
        $emailBody = "<h3>Response from {$user['name']} ({$user['business_name']})</h3>"
                   . "<p>Regarding your enquiry for <strong>{$enquiry['listing_title']}</strong>:</p>"
                   . "<div style='background:#f9f9f9;padding:16px;border-left:4px solid #d71920;margin:16px 0;'>"
                   . nl2br(htmlspecialchars($replyText))
                   . "</div>"
                   . "<p>You may reply directly to this email or contact the seller at {$user['phone']}.</p>";

        send_app_email($buyerEmail, "Reply regarding {$enquiry['listing_title']} · Taylor's Ibu Niaga", $emailBody);

        log_audit('REPLY_ENQUIRY', 'enquiries', $enqId, "Seller replied to enquiry #{$enqId}");
        set_flash('success', "Reply sent to {$enquiry['sender_name']} ({$buyerEmail})!");
        header('Location: ' . BASE_URL . '/member/enquiries.php');
        exit;
    }
}

// Mark Handled / Closed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'close') {
    verify_csrf();
    $enqId = (int)$_POST['enquiry_id'];
    $pdo->prepare("UPDATE enquiries SET status = 'closed' WHERE id = ? AND seller_id = ?")->execute([$enqId, $userId]);
    set_flash('info', 'Enquiry marked as handled.');
    header('Location: ' . BASE_URL . '/member/enquiries.php');
    exit;
}

// Fetch enquiries
$stmt = $pdo->prepare("SELECT e.*, b.title as listing_title FROM enquiries e JOIN bazaar_listings b ON e.listing_id = b.id WHERE e.seller_id = ? ORDER BY e.created_at DESC");
$stmt->execute([$userId]);
$enquiries = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Enquiries & Order Requests</h1>
    <p style="color: var(--color-muted);">Customer requests, campus catering orders, and pickup arrangements submitted via your Open Bazaar listings.</p>
  </div>
</div>

<?php if (empty($enquiries)): ?>
  <div style="background: #fff; border: 1px dashed var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center;">
    <h3 style="margin-bottom: 8px;">No customer enquiries yet</h3>
    <p style="color: var(--color-muted); font-size: 15px;">
      When visitors view your Open Bazaar items and submit an order request, you will receive them here.
    </p>
  </div>
<?php else: ?>
  <div style="display: flex; flex-direction: column; gap: 20px;">
    <?php foreach ($enquiries as $enq): ?>
      <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-subtle);">
        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 12px; margin-bottom: 14px;">
          <div>
            <div class="card-category" style="margin-bottom: 4px;">LISTING: <?= e($enq['listing_title']) ?></div>
            <div style="font-size: 18px; font-weight: 700; color: var(--color-ink);">
              From: <?= e($enq['sender_name']) ?>
            </div>
            <div style="font-size: 13px; color: var(--color-muted); margin-top: 2px;">
              ✉ <?= e($enq['sender_email']) ?> &nbsp;·&nbsp; 📞 <?= e($enq['sender_phone'] ?: 'No phone provided') ?>
            </div>
          </div>

          <div style="display: flex; align-items: center; gap: 10px;">
            <?php if ($enq['status'] === 'new'): ?>
              <span class="badge badge-peach">Needs Reply</span>
            <?php elseif ($enq['status'] === 'replied'): ?>
              <span class="badge badge-positive">Replied</span>
            <?php else: ?>
              <span class="badge badge-neutral">Handled / Closed</span>
            <?php endif; ?>
            <span class="text-caption"><?= date('d M Y, h:i A', strtotime($enq['created_at'])) ?></span>
          </div>
        </div>

        <!-- Customer Message -->
        <div style="background: var(--color-sage); padding: 16px 20px; border-radius: var(--radius-sm); font-size: 15px; color: var(--color-ink); line-height: 1.5; margin-bottom: 16px;">
          “<?= nl2br(e($enq['message'])) ?>”
        </div>

        <!-- Prior Reply if exists -->
        <?php if ($enq['reply_text']): ?>
          <div style="background: #e8f5e9; border-left: 4px solid #2e7d32; padding: 14px 18px; border-radius: var(--radius-sm); font-size: 14px; margin-bottom: 16px;">
            <div style="font-weight: 700; color: #1b5e20; margin-bottom: 4px;">Your Reply (sent <?= date('d M, h:i A', strtotime($enq['replied_at'])) ?>):</div>
            <div style="color: #1b5e20;"><?= nl2br(e($enq['reply_text'])) ?></div>
          </div>
        <?php endif; ?>

        <!-- Reply / Action Controls -->
        <div style="display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
          <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('reply-box-<?= $enq['id'] ?>').style.display = 'block'; this.style.display='none';">
            <?= $enq['reply_text'] ? 'Send Follow-up Reply' : 'Reply to Buyer' ?>
          </button>

          <?php if ($enq['status'] !== 'closed'): ?>
            <form method="POST" action="" style="display: inline;">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="close">
              <input type="hidden" name="enquiry_id" value="<?= $enq['id'] ?>">
              <button type="submit" class="btn btn-outline btn-sm">
                Mark as Handled
              </button>
            </form>
          <?php endif; ?>
        </div>

        <!-- Collapsible Reply Box -->
        <div id="reply-box-<?= $enq['id'] ?>" style="display: none; margin-top: 20px; border-top: 1px solid var(--color-line); padding-top: 16px;">
          <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="reply">
            <input type="hidden" name="enquiry_id" value="<?= $enq['id'] ?>">

            <div class="form-group">
              <label class="form-label">Write your reply to <?= e($enq['sender_name']) ?></label>
              <textarea name="reply_text" class="form-textarea" required placeholder="Hi <?= e($enq['sender_name']) ?>, thank you for your order inquiry! We would be delighted to prepare your order..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
              <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('reply-box-<?= $enq['id'] ?>').style.display='none';">Cancel</button>
              <button type="submit" class="btn btn-primary btn-sm">Send Email Reply</button>
            </div>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
