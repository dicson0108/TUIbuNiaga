<?php
/**
 * Program Registration Form
 * Matches Figma: 02 Member workspace/04 Program Registration Form.png
 */

$pageTitle = "Program Registration Form";
require_once __DIR__ . '/../includes/user_header.php';
require_once __DIR__ . '/../includes/mailer.php';
$pdo = get_db();
$userId = $user['id'];

// Check if user already has an existing draft or application to edit
$appId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$app = null;
if ($appId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND user_id = ?");
    $stmt->execute([$appId, $userId]);
    $app = $stmt->fetch();
} else {
    // Check for draft
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? AND status = 'Draft' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$userId]);
    $app = $stmt->fetch();
}

// Fetch open programs
$programs = $pdo->query("SELECT * FROM programs WHERE status = 'open' ORDER BY id ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $programId = (int)($_POST['program_id'] ?? 0);
    $fullName = trim($_POST['full_name'] ?? '');
    $icNo = trim($_POST['ic_no'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $businessName = trim($_POST['business_name'] ?? '');
    $businessCat = trim($_POST['business_category'] ?? '');
    $businessStage = trim($_POST['business_stage'] ?? '');
    $revenue = trim($_POST['monthly_revenue'] ?? '');
    $intent = trim($_POST['statement_of_intent'] ?? '');
    $submitType = $_POST['submit_type'] ?? 'submit'; // 'draft' or 'submit'
    $status = ($submitType === 'draft') ? 'Draft' : 'Submitted';

    if (!$programId || !$fullName || !$icNo || !$phone) {
        $errors[] = 'Please fill in all mandatory applicant details.';
    }

    if (empty($errors)) {
        if ($app) {
            $stmtUp = $pdo->prepare("UPDATE applications SET 
                program_id = ?, full_name = ?, ic_no = ?, phone = ?, address = ?, 
                business_name = ?, business_category = ?, business_stage = ?, 
                monthly_revenue = ?, statement_of_intent = ?, status = ?, 
                submitted_at = IF(? = 'Submitted', NOW(), submitted_at)
                WHERE id = ? AND user_id = ?");
            $stmtUp->execute([
                $programId, $fullName, $icNo, $phone, $address,
                $businessName, $businessCat, $businessStage,
                $revenue, $intent, $status, $status, $app['id'], $userId
            ]);
            $currentAppId = $app['id'];
        } else {
            $appNo = 'IN-' . str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT);
            $stmtIns = $pdo->prepare("INSERT INTO applications 
                (application_no, user_id, program_id, full_name, ic_no, phone, address, business_name, business_category, business_stage, monthly_revenue, statement_of_intent, status, submitted_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, IF(? = 'Submitted', NOW(), NULL), NOW())");
            $stmtIns->execute([
                $appNo, $userId, $programId, $fullName, $icNo, $phone, $address,
                $businessName, $businessCat, $businessStage, $revenue, $intent, $status, $status
            ]);
            $currentAppId = $pdo->lastInsertId();

            // Record status history
            $stmtHist = $pdo->prepare("INSERT INTO application_status_history (application_id, status, changed_by, note, changed_at) VALUES (?, ?, ?, ?, NOW())");
            $stmtHist->execute([$currentAppId, $status, $userId, ($status === 'Draft' ? 'Saved as draft' : 'Initial application submitted online.')]);
        }

        // Handle Document Uploads (IC & Business Proposal)
        if (!empty($_FILES['doc_ic']['name'])) {
            $res = handle_file_upload($_FILES['doc_ic'], 'uploads/documents/');
            if ($res['success']) {
                $stmtDoc = $pdo->prepare("INSERT INTO application_documents (application_id, doc_type, file_name, file_path, file_size, mime_type) VALUES (?, 'IC Copy', ?, ?, ?, ?)");
                $stmtDoc->execute([$currentAppId, $res['file_name'], $res['file_path'], $res['file_size'], $res['mime_type']]);
            } else {
                $errors[] = 'IC Upload Error: ' . $res['error'];
            }
        }

        if (!empty($_FILES['doc_proposal']['name'])) {
            $res = handle_file_upload($_FILES['doc_proposal'], 'uploads/documents/');
            if ($res['success']) {
                $stmtDoc = $pdo->prepare("INSERT INTO application_documents (application_id, doc_type, file_name, file_path, file_size, mime_type) VALUES (?, 'Business Proposal', ?, ?, ?, ?)");
                $stmtDoc->execute([$currentAppId, $res['file_name'], $res['file_path'], $res['file_size'], $res['mime_type']]);
            } else {
                $errors[] = 'Proposal Upload Error: ' . $res['error'];
            }
        }

        if ($status === 'Submitted') {
            // In-app notification
            create_notification(
                $userId,
                'application',
                'Application Submitted',
                "Your application for Ibu Niaga has been received and queued for review.",
                'member/applications.php'
            );

            // Confirmation email
            send_app_email(
                $user['email'],
                "Application Received · Taylor's Ibu Niaga",
                "<p>Hi {$fullName},</p><p>We have successfully received your application for the Ibu Niaga program. Our review committee at Taylor's University Lakeside Campus will review your submission shortly.</p><p>You can track real-time progress on your status bar in your Member Workspace.</p>"
            );

            log_audit('APPLICATION_SUBMIT', 'applications', $currentAppId, "Submitted application {$currentAppId}");
            set_flash('success', 'Your application has been submitted successfully! You can track its progress below.');
            header('Location: ' . BASE_URL . '/member/applications.php?id=' . $currentAppId);
            exit;
        } else {
            set_flash('info', 'Draft saved! You can resume and submit your application at any time.');
            header('Location: ' . BASE_URL . '/member/program_registration.php?id=' . $currentAppId);
            exit;
        }
    }
}
?>

<div style="max-width: 820px;">
  <div style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin-bottom: 6px;">Program Registration Form</h1>
    <p style="color: var(--color-muted);">
      Apply for Taylor's University Ibu Niaga entrepreneurship cohort and subsidized skill pathways.
    </p>
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

      <div style="border-bottom: 1px solid var(--color-line); padding-bottom: 16px; margin-bottom: 24px;">
        <h3 style="font-size: 18px; color: var(--color-primary);">1. Select Program Track</h3>
      </div>

      <div class="form-group">
        <label class="form-label">Available Programs *</label>
        <select name="program_id" class="form-select" required>
          <?php foreach ($programs as $p): ?>
            <option value="<?= $p['id'] ?>" <?= ($app && $app['program_id'] == $p['id']) ? 'selected' : '' ?>>
              <?= e($p['title']) ?> (<?= e($p['code']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="border-bottom: 1px solid var(--color-line); padding-bottom: 16px; margin: 32px 0 24px;">
        <h3 style="font-size: 18px; color: var(--color-primary);">2. Personal & Contact Information</h3>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Applicant Full Name (as per IC) *</label>
          <input type="text" name="full_name" value="<?= e($app['full_name'] ?? $user['name']) ?>" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">MyKad / IC Number *</label>
          <input type="text" name="ic_no" value="<?= e($app['ic_no'] ?? '') ?>" class="form-input" placeholder="e.g. 881024-10-5542" required>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Contact Phone Number *</label>
          <input type="text" name="phone" value="<?= e($app['phone'] ?? $user['phone'] ?? '') ?>" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">Registered Email</label>
          <input type="email" value="<?= e($user['email']) ?>" class="form-input" disabled style="background-color: var(--color-sage);">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Residential Address in Selangor / Klang Valley *</label>
        <textarea name="address" class="form-textarea" style="min-height: 80px;" required placeholder="Street address, postcode, and area"><?= e($app['address'] ?? '') ?></textarea>
      </div>

      <div style="border-bottom: 1px solid var(--color-line); padding-bottom: 16px; margin: 32px 0 24px;">
        <h3 style="font-size: 18px; color: var(--color-primary);">3. Business / Startup Profile</h3>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Business / Brand Name *</label>
          <input type="text" name="business_name" value="<?= e($app['business_name'] ?? $user['business_name'] ?? '') ?>" class="form-input" required placeholder="e.g. Aina's Kitchen">
        </div>

        <div class="form-group">
          <label class="form-label">Business Category *</label>
          <select name="business_category" class="form-select" required>
            <option value="Food & Beverages" <?= (($app['business_category'] ?? '') === 'Food & Beverages') ? 'selected' : '' ?>>Food & Beverages</option>
            <option value="Home Bakery" <?= (($app['business_category'] ?? '') === 'Home Bakery') ? 'selected' : '' ?>>Home Bakery</option>
            <option value="Services & Catering" <?= (($app['business_category'] ?? '') === 'Services & Catering') ? 'selected' : '' ?>>Services & Catering</option>
            <option value="Handmade Crafts" <?= (($app['business_category'] ?? '') === 'Handmade Crafts') ? 'selected' : '' ?>>Handmade Crafts</option>
          </select>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Current Business Stage *</label>
          <select name="business_stage" class="form-select" required>
            <option value="Idea / Aspiring" <?= (($app['business_stage'] ?? '') === 'Idea / Aspiring') ? 'selected' : '' ?>>Idea / Aspiring</option>
            <option value="Early Revenue (Under 1 year)" <?= (($app['business_stage'] ?? '') === 'Early Revenue (Under 1 year)') ? 'selected' : '' ?>>Early Revenue (Under 1 year)</option>
            <option value="Established (1-3 years)" <?= (($app['business_stage'] ?? '') === 'Established (1-3 years)') ? 'selected' : '' ?>>Established (1-3 years)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Approximate Monthly Revenue</label>
          <input type="text" name="monthly_revenue" value="<?= e($app['monthly_revenue'] ?? '') ?>" class="form-input" placeholder="e.g. RM 1,500 - RM 3,000">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Statement of Intent & Goals</label>
        <textarea name="statement_of_intent" class="form-textarea" placeholder="Why do you wish to join the Ibu Niaga initiative? What skills do you hope to acquire?"><?= e($app['statement_of_intent'] ?? '') ?></textarea>
      </div>

      <div style="border-bottom: 1px solid var(--color-line); padding-bottom: 16px; margin: 32px 0 24px;">
        <h3 style="font-size: 18px; color: var(--color-primary);">4. Document Uploads (PDF, JPG, PNG · Max 5MB)</h3>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label class="form-label">MyKad / IC Copy (PDF / Image)</label>
          <input type="file" name="doc_ic" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
          <div class="form-hint">Required for official identity verification. Stored securely.</div>
        </div>

        <div class="form-group">
          <label class="form-label">Business Proposal / Pitch / SSM (Optional)</label>
          <input type="file" name="doc_proposal" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
          <div class="form-hint">Attach your menu, product photos, or pitch deck.</div>
        </div>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--color-line);">
        <button type="submit" name="submit_type" value="draft" class="btn btn-outline">
          Save as draft
        </button>
        <button type="submit" name="submit_type" value="submit" class="btn btn-primary btn-lg">
          Submit application
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
