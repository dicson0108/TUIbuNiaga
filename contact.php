<?php
/**
 * Contact Page & Confirmation
 * Matches Figma: 19 Contact.png & 28 Contact · Sent.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = get_db();

$isSent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $message) {
        $body = "<h3>New Public Website Contact Message</h3>"
              . "<p><strong>From:</strong> " . htmlspecialchars($name) . " (" . htmlspecialchars($email) . ")</p>"
              . "<p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>"
              . "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";

        send_app_email('ibuniaga@taylors.edu.my', "Website Contact: " . ($subject ?: 'General Inquiry'), $body);
        $isSent = true;
    } else {
        set_flash('error', 'Please complete all required fields.');
    }
}

$pageTitle = $isSent ? "Message Sent · Contact" : "Contact Taylor's Ibu Niaga";
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 900px;">
    <?php if ($isSent): ?>
      <!-- Matches Figma 28 Contact · Sent.png -->
      <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center; max-width: 600px; margin: 0 auto;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #e8f5e9; color: #2e7d32; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px;">
          ✓
        </div>
        <h1 style="font-size: 28px; margin-bottom: 12px;">Thank you for getting in touch!</h1>
        <p style="font-size: 16px; color: var(--color-muted); line-height: 1.6; margin-bottom: 28px;">
          Your message has been received by our community coordination team at Taylor's University Lakeside Campus. We will respond within one working day.
        </p>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">
          Return to home page
        </a>
      </div>
    <?php else: ?>
      <!-- Matches Figma 19 Contact.png -->
      <div style="max-width: 760px; margin-bottom: 40px;">
        <div class="card-category" style="margin-bottom: 12px;">COMMUNITY ENGAGEMENT & INQUIRIES</div>
        <h1 class="display-title" style="margin-bottom: 16px;">Contact Us</h1>
        <p style="font-size: 18px; color: var(--color-muted); line-height: 1.6;">
          Have questions regarding program eligibility, catering collaborations, or campus workshops? Connect with our team directly.
        </p>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 48px; align-items: start;">
        <div>
          <div style="background: var(--color-cream); border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 28px; margin-bottom: 24px;">
            <h3 style="margin-bottom: 16px;">Taylor's Lakeside Campus</h3>
            <p style="font-size: 14px; line-height: 1.6; color: var(--color-ink); margin-bottom: 16px;">
              School of Food Studies & Gastronomy<br>
              Taylor's University Lakeside Campus<br>
              No. 1, Jalan Taylor's, 47500 Subang Jaya<br>
              Selangor Darul Ehsan, Malaysia
            </p>
            <div style="font-size: 14px; color: var(--color-muted); line-height: 1.6;">
              <strong>Email:</strong> ibuniaga@taylors.edu.my<br>
              <strong>Phone:</strong> +60 3-5629 5000<br>
              <strong>Operating Hours:</strong> Mon – Fri, 9:00 AM – 5:30 PM
            </div>
          </div>
        </div>

        <div class="form-card" style="max-width: 100%;">
          <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input type="text" name="name" class="form-input" required placeholder="Your name">
            </div>

            <div class="form-group">
              <label class="form-label">Email Address *</label>
              <input type="email" name="email" class="form-input" required placeholder="name@domain.com">
            </div>

            <div class="form-group">
              <label class="form-label">Subject</label>
              <input type="text" name="subject" class="form-input" placeholder="e.g. Program Intake or Partnership Inquiry">
            </div>

            <div class="form-group">
              <label class="form-label">Message *</label>
              <textarea name="message" class="form-textarea" required placeholder="How can our community team assist you?"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
              Send Message
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
