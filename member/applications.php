<?php
/**
 * My Applications & Status Stepper
 * Matches Figma: 02 Member workspace/05 My Applications.png, 06 Application · Needs Documents.png, 07 Application · Submitted.png
 */

$pageTitle = "My Applications";
require_once __DIR__ . '/../includes/user_header.php';
$pdo = get_db();
$userId = $user['id'];

// Handle document re-upload when status is Needs Documents (PRD PR-07)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reupload') {
    verify_csrf();
    $targetAppId = (int)$_POST['app_id'];
    $docType = trim($_POST['doc_type'] ?? 'Additional Document');

    if (!empty($_FILES['reupload_file']['name'])) {
        $res = handle_file_upload($_FILES['reupload_file'], 'uploads/documents/');
        if ($res['success']) {
            $stmtDoc = $pdo->prepare("INSERT INTO application_documents (application_id, doc_type, file_name, file_path, file_size, mime_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtDoc->execute([$targetAppId, $docType, $res['file_name'], $res['file_path'], $res['file_size'], $res['mime_type']]);

            // Change status back to In Review
            $pdo->prepare("UPDATE applications SET status = 'In Review', remarks = 'Documents re-uploaded by applicant. Awaiting moderator review.' WHERE id = ? AND user_id = ?")->execute([$targetAppId, $userId]);

            // Add history
            $pdo->prepare("INSERT INTO application_status_history (application_id, status, changed_by, note, changed_at) VALUES (?, 'In Review', ?, 'Applicant re-uploaded requested documents.', NOW())")->execute([$targetAppId, $userId]);

            log_audit('DOCUMENT_REUPLOAD', 'applications', $targetAppId, "Applicant re-uploaded {$docType}");
            set_flash('success', 'Your documents have been submitted and your application has returned to In Review.');
            header("Location: " . BASE_URL . "/member/applications.php?id=" . $targetAppId);
            exit;
        } else {
            set_flash('error', 'Upload error: ' . $res['error']);
        }
    }
}

// Fetch all applications for user
$stmtApps = $pdo->prepare("SELECT a.*, p.title as program_title, p.code as program_code FROM applications a JOIN programs p ON a.program_id = p.id WHERE a.user_id = ? ORDER BY a.id DESC");
$stmtApps->execute([$userId]);
$applications = $stmtApps->fetchAll();

// Active selected application (default to query ?id= or latest)
$selectedAppId = isset($_GET['id']) ? (int)$_GET['id'] : (!empty($applications) ? $applications[0]['id'] : 0);
$selectedApp = null;
foreach ($applications as $a) {
    if ($a['id'] == $selectedAppId) {
        $selectedApp = $a;
        break;
    }
}
if (!$selectedApp && !empty($applications)) {
    $selectedApp = $applications[0];
}

// Fetch documents and status history for selected application
$documents = [];
$history = [];
if ($selectedApp) {
    $stmtDocs = $pdo->prepare("SELECT * FROM application_documents WHERE application_id = ? ORDER BY uploaded_at ASC");
    $stmtDocs->execute([$selectedApp['id']]);
    $documents = $stmtDocs->fetchAll();

    $stmtHist = $pdo->prepare("SELECT h.*, u.name as changer_name FROM application_status_history h LEFT JOIN users u ON h.changed_by = u.id WHERE h.application_id = ? ORDER BY h.changed_at ASC");
    $stmtHist->execute([$selectedApp['id']]);
    $history = $stmtHist->fetchAll();
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">My Applications</h1>
    <p style="color: var(--color-muted);">Track your program enrollment status, reviewer notes, and upload documents.</p>
  </div>
  <a href="<?= BASE_URL ?>/member/program_registration.php" class="btn btn-primary">
    + Apply for New Program
  </a>
</div>

<?php if (empty($applications)): ?>
  <div style="background: #fff; border: 1px dashed var(--color-line); border-radius: var(--radius-md); padding: 64px 32px; text-align: center;">
    <h3 style="margin-bottom: 8px;">No applications submitted yet</h3>
    <p style="color: var(--color-muted); font-size: 15px; margin-bottom: 24px;">
      Explore our entrepreneurship tracks and apply to start your next chapter.
    </p>
    <a href="<?= BASE_URL ?>/member/program_registration.php" class="btn btn-primary">
      Start Program Application
    </a>
  </div>
<?php else: ?>

  <!-- Application Selector Tabs if multiple -->
  <?php if (count($applications) > 1): ?>
    <div style="display: flex; gap: 12px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px;">
      <?php foreach ($applications as $appItem): ?>
        <a href="?id=<?= $appItem['id'] ?>" class="btn <?= ($selectedApp['id'] == $appItem['id']) ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="white-space: nowrap;">
          <?= e($appItem['application_no']) ?>: <?= e($appItem['program_title']) ?> (<?= e($appItem['status']) ?>)
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Main Status Card for Selected Application -->
  <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 32px; margin-bottom: 32px; box-shadow: var(--shadow-subtle);">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
      <div>
        <div class="card-category" style="margin-bottom: 6px;">APPLICATION NO: <?= e($selectedApp['application_no']) ?></div>
        <h2 style="font-size: 26px; margin-bottom: 4px;"><?= e($selectedApp['program_title']) ?></h2>
        <div style="font-size: 14px; color: var(--color-muted);">
          Submitted on <?= $selectedApp['submitted_at'] ? date('d M Y, h:i A', strtotime($selectedApp['submitted_at'])) : 'Draft' ?>
        </div>
      </div>
      <div>
        <?= status_badge($selectedApp['status']) ?>
      </div>
    </div>

    <!-- 5-Stage Status Stepper (PRD PR-04: Submitted -> In Review -> Needs Documents -> Approved / Rejected) -->
    <?php
      $statusOrder = ['Submitted', 'In Review', 'Needs Documents', 'Approved'];
      $currentIdx = array_search($selectedApp['status'], $statusOrder);
      if ($selectedApp['status'] === 'Rejected') {
          $currentIdx = 3;
      } elseif ($currentIdx === false) {
          $currentIdx = 0; // Draft or Submitted
      }
    ?>
    <div class="status-tracker" style="margin: 40px 0 24px;">
      <div class="tracker-line">
        <div class="tracker-line-fill" style="width: <?= min(100, $currentIdx * 33.3) ?>%;"></div>
      </div>

      <div class="tracker-step <?= ($currentIdx >= 0) ? 'completed' : '' ?> <?= ($selectedApp['status'] === 'Submitted') ? 'active' : '' ?>">
        <div class="step-circle">1</div>
        <div class="step-label">Submitted</div>
      </div>

      <div class="tracker-step <?= ($currentIdx >= 1) ? 'completed' : '' ?> <?= ($selectedApp['status'] === 'In Review') ? 'active' : '' ?>">
        <div class="step-circle">2</div>
        <div class="step-label">In Review</div>
      </div>

      <div class="tracker-step <?= ($currentIdx >= 2) ? 'completed' : '' ?> <?= ($selectedApp['status'] === 'Needs Documents') ? 'active' : '' ?>">
        <div class="step-circle">3</div>
        <div class="step-label">Needs Documents</div>
      </div>

      <div class="tracker-step <?= ($currentIdx >= 3) ? 'completed' : '' ?> <?= in_array($selectedApp['status'], ['Approved', 'Rejected']) ? 'active' : '' ?>">
        <div class="step-circle"><?= ($selectedApp['status'] === 'Rejected') ? '✕' : '4' ?></div>
        <div class="step-label"><?= ($selectedApp['status'] === 'Rejected') ? 'Rejected' : 'Approved' ?></div>
      </div>
    </div>

    <!-- Reviewer Remarks Box (PRD PR-05) -->
    <?php if ($selectedApp['remarks']): ?>
      <div style="background-color: var(--color-cream); border-left: 4px solid var(--color-primary); padding: 18px 24px; border-radius: var(--radius-sm); margin: 28px 0;">
        <div style="font-weight: 700; color: var(--color-ink); margin-bottom: 4px;">Review Committee Remarks:</div>
        <div style="font-size: 15px; color: var(--color-ink); line-height: 1.5;"><?= nl2br(e($selectedApp['remarks'])) ?></div>
      </div>
    <?php endif; ?>

    <!-- Document Re-upload Action (PRD PR-07 & Figma 06 Application · Needs Documents.png) -->
    <?php if ($selectedApp['status'] === 'Needs Documents'): ?>
      <div style="background: #fff3e0; border: 1px solid #ffe0b2; border-radius: var(--radius-md); padding: 24px; margin: 28px 0;">
        <h3 style="color: #e65100; margin-bottom: 8px;">Action Required: Re-upload Documents</h3>
        <p style="font-size: 14px; color: var(--color-ink); margin-bottom: 16px;">
          The review committee requested additional or updated documents for this application. Please upload the requested file below to resume review.
        </p>

        <form method="POST" action="" enctype="multipart/form-data" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="reupload">
          <input type="hidden" name="app_id" value="<?= $selectedApp['id'] ?>">

          <div style="flex: 1; min-width: 200px;">
            <label class="form-label" style="font-size: 13px;">Document Type</label>
            <select name="doc_type" class="form-select">
              <option value="IC Copy">MyKad / IC Copy</option>
              <option value="Business Proposal">Business Proposal</option>
              <option value="SSM Certificate">SSM Certificate</option>
              <option value="Other Document">Other Document</option>
            </select>
          </div>

          <div style="flex: 1.5; min-width: 240px;">
            <label class="form-label" style="font-size: 13px;">File (PDF, JPG, PNG)</label>
            <input type="file" name="reupload_file" class="form-input" required accept=".pdf,.jpg,.jpeg,.png">
          </div>

          <button type="submit" class="btn btn-primary">
            Submit Document
          </button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <!-- Documents & History Side-by-Side Grid -->
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Documents Attached -->
    <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 24px;">
      <h3 style="font-size: 18px; margin-bottom: 16px;">Uploaded Documents</h3>
      <?php if (empty($documents)): ?>
        <p style="font-size: 14px; color: var(--color-muted);">No documents uploaded yet.</p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
          <?php foreach ($documents as $doc): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid var(--color-line); border-radius: var(--radius-sm);">
              <div>
                <div style="font-weight: 600; font-size: 14px;"><?= e($doc['doc_type']) ?></div>
                <div style="font-size: 12px; color: var(--color-muted);"><?= e($doc['file_name']) ?> (<?= round($doc['file_size'] / 1024) ?> KB)</div>
              </div>
              <a href="<?= BASE_URL ?>/<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-outline btn-sm">
                View File ↗
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Status History Timeline -->
    <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 24px;">
      <h3 style="font-size: 18px; margin-bottom: 16px;">Status History</h3>
      <?php if (empty($history)): ?>
        <p style="font-size: 14px; color: var(--color-muted);">No status updates recorded yet.</p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 14px; position: relative;">
          <?php foreach ($history as $h): ?>
            <div style="border-left: 2px solid var(--color-primary); padding-left: 14px;">
              <div style="font-weight: 700; font-size: 14px; color: var(--color-ink);">
                <?= e($h['status']) ?>
              </div>
              <div style="font-size: 13px; color: var(--color-muted); margin: 2px 0;">
                <?= e($h['note']) ?>
              </div>
              <div class="text-caption">
                <?= date('d M Y, h:i A', strtotime($h['changed_at'])) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
