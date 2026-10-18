<?php
/**
 * Moderator Application Review Queue
 * Matches Figma: 03 Moderator workspace/02 Application Review.png
 */

$pageTitle = "Application Review";
require_once __DIR__ . '/../includes/mod_header.php';
$pdo = get_db();

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT a.*, p.title as program_title, u.email as user_email 
        FROM applications a 
        JOIN programs p ON a.program_id = p.id 
        JOIN users u ON a.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($statusFilter !== '') {
    $sql .= " AND a.status = ?";
    $params[] = $statusFilter;
}

if ($search !== '') {
    $sql .= " AND (a.application_no LIKE ? OR a.full_name LIKE ? OR a.business_name LIKE ? OR u.email LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

$sql .= " ORDER BY (a.status = 'In Review' OR a.status = 'Submitted') DESC, a.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Application Review</h1>
    <p style="color: var(--color-muted);">Evaluate applicant eligibility, inspect submitted identity and proposal files, and record status decisions.</p>
  </div>
</div>

<!-- Filters & Search Bar -->
<div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 18px 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
  <div style="display: flex; gap: 8px; flex-wrap: wrap;">
    <a href="?status=" class="btn <?= ($statusFilter === '') ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
    <a href="?status=Submitted" class="btn <?= ($statusFilter === 'Submitted') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Submitted</a>
    <a href="?status=In+Review" class="btn <?= ($statusFilter === 'In Review') ? 'btn-primary' : 'btn-outline' ?> btn-sm">In Review</a>
    <a href="?status=Needs+Documents" class="btn <?= ($statusFilter === 'Needs Documents') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Needs Documents</a>
    <a href="?status=Approved" class="btn <?= ($statusFilter === 'Approved') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Approved</a>
    <a href="?status=Rejected" class="btn <?= ($statusFilter === 'Rejected') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Rejected</a>
  </div>

  <form method="GET" action="" style="display: flex; gap: 8px;">
    <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search application no, name..." class="table-search-input">
    <button type="submit" class="btn btn-secondary btn-sm">Search</button>
  </form>
</div>

<div class="table-wrapper">
  <table class="data-table">
    <thead>
      <tr>
        <th>Application No</th>
        <th>Applicant Name</th>
        <th>Business Name</th>
        <th>Program Track</th>
        <th>Status</th>
        <th>Submitted</th>
        <th style="text-align: right;">Review Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($applications)): ?>
        <tr>
          <td colspan="7" style="text-align: center; color: var(--color-muted); padding: 48px;">
            No applications found matching your criteria.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($applications as $app): ?>
          <tr>
            <td style="font-family: monospace; font-weight: 700;">#<?= e($app['application_no']) ?></td>
            <td>
              <div style="font-weight: 700; color: var(--color-ink);"><?= e($app['full_name']) ?></div>
              <div class="text-caption"><?= e($app['user_email']) ?></div>
            </td>
            <td style="font-weight: 600; color: var(--color-primary);"><?= e($app['business_name']) ?></td>
            <td><?= e($app['program_title']) ?></td>
            <td><?= status_badge($app['status']) ?></td>
            <td style="color: var(--color-muted); font-size: 13px;">
              <?= $app['submitted_at'] ? date('d M Y, h:i A', strtotime($app['submitted_at'])) : 'Draft' ?>
            </td>
            <td style="text-align: right;">
              <a href="<?= BASE_URL ?>/moderator/application_detail.php?id=<?= $app['id'] ?>" class="btn btn-primary btn-sm">
                Review Application →
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
