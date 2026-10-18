<?php
/**
 * Enquiry / Order Request Form Page
 * Matches Figma: 16 Enquiry - Order Request.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT b.*, u.id as seller_uid, u.name as seller_name, u.email as seller_email, u.business_name FROM bazaar_listings b JOIN users u ON b.user_id = u.id WHERE b.id = ?");
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing) {
    header("Location: " . BASE_URL . "/bazaar.php");
    exit;
}

$user = current_user();
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $senderName = trim($_POST['sender_name'] ?? '');
    $senderEmail = trim($_POST['sender_email'] ?? '');
    $senderPhone = trim($_POST['sender_phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($senderName && $senderEmail && $message) {
        $stmtIns = $pdo->prepare("INSERT INTO enquiries (listing_id, seller_id, sender_name, sender_email, sender_phone, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())");
        $stmtIns->execute([$id, $listing['seller_uid'], $senderName, $senderEmail, $senderPhone, $message]);

        // Send in-app notification to seller
        create_notification(
            $listing['seller_uid'],
            'enquiry',
            'New Listing Enquiry',
            "{$senderName} submitted a new inquiry regarding '{$listing['title']}'.",
            'member/enquiries.php'
        );

        // Send email to seller
        $emailBody = "<h3>New Order / Enquiry from Taylor's Ibu Niaga Bazaar</h3>"
                   . "<p><strong>Item:</strong> {$listing['title']}</p>"
                   . "<p><strong>From:</strong> {$senderName} ({$senderEmail}, {$senderPhone})</p>"
                   . "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>"
                   . "<p>Log in to your Ibu Niaga Member Workspace to reply directly.</p>";
        send_app_email($listing['seller_email'], "New Enquiry: {$listing['title']}", $emailBody);

        set_flash('success', "Your enquiry has been successfully delivered to {$listing['business_name']}! The seller will reach out via email/phone.");
        header("Location: " . BASE_URL . "/listing_detail.php?id=" . $id);
        exit;
    } else {
        set_flash('error', 'Please fill in all required fields.');
    }
}

$pageTitle = "Send Enquiry · " . $listing['title'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 680px;">
    <div style="margin-bottom: 24px;">
      <a href="<?= BASE_URL ?>/listing_detail.php?id=<?= $listing['id'] ?>" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to listing</a>
    </div>

    <div class="card-category" style="margin-bottom: 8px;">ORDER REQUEST & ENQUIRY</div>
    <h1 style="font-size: 32px; margin-bottom: 12px;">Contact <?= e($listing['business_name'] ?: $listing['seller_name']) ?></h1>
    <p style="font-size: 15px; color: var(--color-muted); margin-bottom: 32px;">
      Inquiring regarding: <strong><?= e($listing['title']) ?></strong> (<?= e($listing['price_text']) ?>)
    </p>

    <div class="form-card">
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
          <label class="form-label">Your Name *</label>
          <input type="text" name="sender_name" value="<?= e($user ? $user['name'] : '') ?>" class="form-input" required placeholder="e.g. Dr. Faridah Noor">
        </div>

        <div class="form-group">
          <label class="form-label">Your Email Address *</label>
          <input type="email" name="sender_email" value="<?= e($user ? $user['email'] : '') ?>" class="form-input" required placeholder="name@domain.com">
          <div class="form-hint">The seller will reply directly to this email.</div>
        </div>

        <div class="form-group">
          <label class="form-label">Phone Number (Optional)</label>
          <input type="text" name="sender_phone" value="<?= e($user ? ($user['phone'] ?? '') : '') ?>" class="form-input" placeholder="+60 12-345 6789">
        </div>

        <div class="form-group">
          <label class="form-label">Order Details / Message *</label>
          <textarea name="message" class="form-textarea" required placeholder="Please describe desired quantity, delivery date/pickup time, dietary requirements, or any questions for the seller..."></textarea>
        </div>

        <div style="background-color: var(--color-sage); padding: 14px 18px; border-radius: var(--radius-sm); font-size: 13px; color: var(--color-ink); margin-bottom: 24px;">
          ℹ️ <strong>Direct Transaction Notice:</strong> No payment is processed through this form. Payment & logistics are arranged directly with the seller.
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
          Submit Order Request / Enquiry
        </button>
      </form>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
