<?php
/**
 * Moderator Application Review Detail
 * Matches Figma: 03 Moderator workspace/03 Application Review Detail.png
 */

require_once __DIR__ . '/../includes/mod_header.php';
require_once __DIR__ . '/../includes/mailer.php';
$pdo = get_db();
$modId = $user['id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT a.*, p.title as program_title, p.code as program_code, u.email as user_email, u.phone as user_phone, u.name as account_name
    FROM applications a 
    JOIN programs p ON a.program_id = p.id 
    JOIN users u ON a.user_id = u.id 
    WHERE a.id = ?");
$stmt->execute([$id]);
$application = $stmt->fetch();

if (!$application) {
    header("Location: " . BASE_URL . "/moderator/applications.php");
    exit;
}

// Handle Status Decision
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'decision') {
    verify_csrf();
    $newStatus = $_POST['status'] ?? '';
    $remarks = trim($_POST['remarks'] ?? '');

    $allowedStatuses = ['In Review', 'Needs Documents', 'Approved', 'Rejected'];
    if (in_array($newStatus, $allowedStatuses)) {
        // Update application
        $stmtUp = $pdo->prepare("UPDATE applications SET status = ?, remarks = ?, reviewer_id = ?, updated_at = NOW() WHERE id = ?");
        $stmtUp->execute([$newStatus, $remarks, $modId, $id]);

        // Insert into history
        $stmtHist = $pdo->prepare("INSERT INTO application_status_history (application_id, status, changed_by, note, changed_at) VALUES (?, ?, ?, ?, NOW())");
        $stmtHist->execute([$id, $newStatus, $modId, $remarks ?: "Status updated to {$newStatus} by moderator."]);

        // If approved, create verified members directory profile if not exists
        if ($newStatus === 'Approved') {
            $stmtCheckMem = $pdo->prepare("SELECT id FROM members WHERE user_id = ?");
            $stmtCheckMem->execute([$application['user_id']]);
            if (!$stmtCheckMem->fetch()) {
                $pdo->prepare("INSERT INTO members (user_id, name, business_name, category, bio, photo_path, location, featured, created_at) VALUES (?, ?, ?, ?, ?, 'assets/images/22 Sample Photography - Community.png', 'Subang Jaya', 1, NOW())")
                    ->execute([$application['user_id'], $application['full_name'], $application['business_name'], $application['business_category'], $application['statement_of_intent'] ?: 'Proud member of Ibu Niaga entrepreneurship community.']);
            }
        }

        // PRD PR-06: Dispatch In-app notification to applicant
        create_notification(
            $application['user_id'],
            'application',
            "Application {$newStatus}",
            "Your application for {$application['program_title']} (#{$application['application_no']}) has been marked as {$newStatus}.",
            "member/applications.php?id={$id}"
        );

        // PRD PR-06: Dispatch Email to applicant
        $emailBody = "<h3>Application Status Update: {$newStatus}</h3>"
                   . "<p>Hi {$application['full_name']},</p>"
                   . "<p>Your application for <strong>{$application['program_title']}</strong> (#{$application['application_no']}) has been updated to <strong>{$newStatus}</strong>.</p>"
                   . ($remarks ? "<div style='background:#f9f9f9;padding:14px;border-left:4px solid #d71920;'><strong>Reviewer Remarks:</strong><br>" . nl2br(htmlspecialchars($remarks)) . "</div>" : "")
                   . "<p>Please log in to your Member Workspace to view next steps or upload requested documents.</p>";

        send_app_email($application['user_email'], "Application Status: {$newStatus} · Taylor's Ibu Niaga", $emailBody);

        log_audit('REVIEW_DECISION', 'applications', $id, "Moderator set status to {$newStatus}. Remarks: {$remarks}");
        set_flash('success', "Application #{$application['application_no']} updated to '{$newStatus}'. Notification and email sent to applicant.");
        header("Location: " . BASE_URL . "/moderator/application_detail.php?id=" . $id);
        exit;
    }
}

// Fetch documents & history
$stmtDocs = $pdo->prepare("SELECT * FROM application_documents WHERE application_id = ? ORDER BY uploaded_at ASC");
$stmtDocs->execute([$id]);
$documents = $stmtDocs->fetchAll();

$stmtHist = $pdo->prepare("SELECT h.*, u.name as changer_name FROM application_status_history h LEFT JOIN users u ON h.changed_by = u.id WHERE h.application_id = ? ORDER BY h.changed_at ASC");
$stmtHist->execute([$id]);
$history = $stmtHist->fetchAll();

$pageTitle = "Review Application #" . $application['application_no'];
?>

<div style="max-width: 960px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/moderator/applications.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to Application Queue</a>
  </div>

  <!-- Header Card -->
  <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 28px; margin-bottom: 32px; box-shadow: var(--shadow-subtle);">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 16px;">
      <div>
        <div class="card-category" style="margin-bottom: 4px;">APPLICATION #<?= e($application['application_no']) ?></div>
        <h1 style="font-size: 28px; margin-bottom: 4px;"><?= e($application['full_name']) ?></h1>
        <div style="font-size: 16px; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
          <?= e($application['business_name']) ?> · <?= e($application['program_title']) ?>
        </div>
        <div style="font-size: 13px; color: var(--color-muted);">
          Submitted: <?= $application['submitted_at'] ? date('d M Y, h:i A', strtotime($application['submitted_at'])) : 'Draft' ?> &nbsp;|&nbsp; 
          Email: <?= e($application['user_email']) ?> &nbsp;|&nbsp; 
          Phone: <?= e($application['phone']) ?>
        </div>
      </div>

      <div>
        <?= status_badge($application['status']) ?>
      </div>
    </div>
  </div>

  <!-- 2 Column Details: Applicant Details & Decision Form -->
  <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 28px; margin-bottom: 36px;">
    <!-- Left: Application Submission Fields -->
    <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 28px;">
      <h3 style="font-size: 18px; margin-bottom: 20px;">Applicant Information</h3>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div>
          <span class="text-caption">MyKad / IC No</span>
          <div style="font-weight: 600; font-size: 14px;"><?= e($application['ic_no']) ?></div>
        </div>
        <div>
          <span class="text-caption">Business Category</span>
          <div style="font-weight: 600; font-size: 14px;"><?= e($application['business_category']) ?></div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div>
          <span class="text-caption">Current Stage</span>
          <div style="font-weight: 600; font-size: 14px;"><?= e($application['business_stage']) ?></div>
        </div>
        <div>
          <span class="text-caption">Monthly Revenue</span>
          <div style="font-weight: 600; font-size: 14px;"><?= e($application['monthly_revenue'] ?: 'Not stated') ?></div>
        </div>
      </div>

      <div style="margin-bottom: 20px;">
        <span class="text-caption">Residential / Kitchen Address</span>
        <div style="font-size: 14px; color: var(--color-ink); margin-top: 2px;"><?= nl2br(e($application['address'])) ?></div>
      </div>

      <div style="margin-bottom: 24px;">
        <span class="text-caption">Statement of Intent & Goals</span>
        <div style="background: var(--color-sage); padding: 14px; border-radius: var(--radius-sm); font-size: 14px; color: var(--color-ink); margin-top: 4px; line-height: 1.5;">
          <?= nl2br(e($application['statement_of_intent'] ?: 'No statement provided.')) ?>
        </div>
      </div>

      <!-- Uploaded Documents -->
      <h4 style="font-size: 16px; margin-bottom: 12px;">Submitted Documents (<?= count($documents) ?>)</h4>
      <?php if (empty($documents)): ?>
        <p style="font-size: 13px; color: var(--color-muted);">No documents attached to this application.</p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <?php foreach ($documents as $doc): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border: 1px solid var(--color-line); border-radius: var(--radius-sm);">
              <div>
                <div style="font-weight: 600; font-size: 13px;"><?= e($doc['doc_type']) ?></div>
                <div class="text-caption"><?= e($doc['file_name']) ?> (<?= round($doc['file_size'] / 1024) ?> KB)</div>
              </div>
              <a href="<?= BASE_URL ?>/<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-outline btn-sm">
                View Document ↗
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Right: Review Decision Form -->
    <div style="background: var(--color-cream); border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 28px; display: flex; flex-direction: column;">
      <h3 style="font-size: 18px; margin-bottom: 8px;">Record Review Decision</h3>
      <p style="font-size: 13px; color: var(--color-muted); margin-bottom: 20px;">
        The applicant will immediately receive an in-app notice and an automated email update.
      </p>

      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="decision">

        <div class="form-group">
          <label class="form-label">Review Status Decision *</label>
          <select name="status" class="form-select" required>
            <option value="In Review" <?= ($application['status'] === 'In Review') ? 'selected' : '' ?>>In Review (Under assessment)</option>
            <option value="Needs Documents" <?= ($application['status'] === 'Needs Documents') ? 'selected' : '' ?>>Needs Documents (Request re-upload)</option>
            <option value="Approved" <?= ($application['status'] === 'Approved') ? 'selected' : '' ?>>Approved (Accept into cohort)</option>
            <option value="Rejected" <?= ($application['status'] === 'Rejected') ? 'selected' : '' ?>>Rejected (Declined)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Review Remarks / Instructions</label>
          <textarea name="remarks" class="form-textarea" style="min-height: 120px;" placeholder="Provide feedback or specific instructions (e.g. please upload valid SSM certificate, or congratulations welcome to the program)..."><?= e($application['remarks']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
          Submit Review Decision
        </button>
      </form>

      <!-- History log -->
      <div style="margin-top: 32px; border-top: 1px solid var(--color-line); padding-top: 20px;">
        <h4 style="font-size: 14px; margin-bottom: 12px;">Status Change Timeline</h4>
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <?php foreach ($history as $h): ?>
            <div style="font-size: 13px; border-left: 2px solid var(--color-primary); padding-left: 10px;">
              <strong><?= e($h['status']) ?></strong>: <?= e($h['note']) ?>
              <div class="text-caption"><?= date('d M, h:i A', strtotime($h['changed_at'])) ?> by <?= e($h['changer_name'] ?: 'System') ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
