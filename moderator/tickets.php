<?php
/**
 * Moderator Ticket Management
 * Matches Figma: 03 Moderator workspace/08 Ticket Management.png
 */

$pageTitle = "Ticket Management";
require_once __DIR__ . '/../includes/mod_header.php';
$pdo = get_db();

$statusFilter = $_GET['status'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';

$sql = "SELECT t.*, u.name as opener_name, u.email as opener_email, m.name as assignee_name,
        (SELECT COUNT(*) FROM ticket_replies r WHERE r.ticket_id = t.id) as reply_count
        FROM tickets t 
        JOIN users u ON t.user_id = u.id 
        LEFT JOIN users m ON t.assigned_to = m.id 
        WHERE 1=1";
$params = [];

if ($statusFilter !== '') {
    $sql .= " AND t.status = ?";
    $params[] = $statusFilter;
}

if ($priorityFilter !== '') {
    $sql .= " AND t.priority = ?";
    $params[] = $priorityFilter;
}

$sql .= " ORDER BY (t.status = 'Open' OR t.status = 'In Progress') DESC, (t.priority = 'High') DESC, t.updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Ticket Management</h1>
    <p style="color: var(--color-muted);">Assign, reply to, prioritize, and resolve support requests from community applicants and members.</p>
  </div>
</div>

<div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 18px 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
  <div style="display: flex; gap: 8px; flex-wrap: wrap;">
    <a href="?status=" class="btn <?= ($statusFilter === '') ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
    <a href="?status=Open" class="btn <?= ($statusFilter === 'Open') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Open</a>
    <a href="?status=In+Progress" class="btn <?= ($statusFilter === 'In Progress') ? 'btn-primary' : 'btn-outline' ?> btn-sm">In Progress</a>
    <a href="?status=Resolved" class="btn <?= ($statusFilter === 'Resolved') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Resolved</a>
    <a href="?status=Closed" class="btn <?= ($statusFilter === 'Closed') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Closed</a>
  </div>

  <div style="display: flex; gap: 12px; align-items: center;">
    <form method="GET" action="" style="display: flex; gap: 8px;">
      <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
      <select name="priority" class="form-select" onchange="this.form.submit()" style="font-size: 13px;">
        <option value="">All Priorities</option>
        <option value="High" <?= ($priorityFilter === 'High') ? 'selected' : '' ?>>High Priority</option>
        <option value="Medium" <?= ($priorityFilter === 'Medium') ? 'selected' : '' ?>>Medium Priority</option>
        <option value="Low" <?= ($priorityFilter === 'Low') ? 'selected' : '' ?>>Low Priority</option>
      </select>
    </form>
  </div>
</div>

<div class="table-wrapper">
  <table class="data-table">
    <thead>
      <tr>
        <th>Ticket No</th>
        <th>Subject & Opener</th>
        <th>Category</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Assigned To</th>
        <th>Updated</th>
        <th style="text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($tickets)): ?>
        <tr>
          <td colspan="8" style="text-align: center; color: var(--color-muted); padding: 48px;">
            No tickets found matching current filters.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td style="font-family: monospace; font-weight: 700;">#<?= e($t['ticket_no']) ?></td>
            <td>
              <div style="font-weight: 700; color: var(--color-ink);"><?= e($t['subject']) ?></div>
              <div class="text-caption">by <?= e($t['opener_name']) ?> (<?= e($t['opener_email']) ?>) · <?= (int)$t['reply_count'] ?> messages</div>
            </td>
            <td><?= e($t['category']) ?></td>
            <td>
              <?php if ($t['priority'] === 'High'): ?>
                <span class="badge badge-destructive">High</span>
              <?php elseif ($t['priority'] === 'Medium'): ?>
                <span class="badge badge-attention">Medium</span>
              <?php else: ?>
                <span class="badge badge-neutral">Low</span>
              <?php endif; ?>
            </td>
            <td><?= status_badge($t['status']) ?></td>
            <td style="color: var(--color-ink); font-weight: 500;">
              <?= e($t['assignee_name'] ?: 'Unassigned') ?>
            </td>
            <td style="color: var(--color-muted); font-size: 13px;">
              <?= date('d M, h:i A', strtotime($t['updated_at'])) ?>
            </td>
            <td style="text-align: right;">
              <a href="<?= BASE_URL ?>/moderator/ticket_detail.php?id=<?= $t['id'] ?>" class="btn btn-primary btn-sm">
                Manage Ticket →
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
