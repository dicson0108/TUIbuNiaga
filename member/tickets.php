<?php
/**
 * Member Support Tickets
 * Matches Figma: 02 Member workspace/18 My Support Tickets.png
 */

$pageTitle = "My Support Tickets";
require_once __DIR__ . '/../includes/user_header.php';
$pdo = get_db();
$userId = $user['id'];

$stmt = $pdo->prepare("SELECT t.*, 
    (SELECT COUNT(*) FROM ticket_replies r WHERE r.ticket_id = t.id) as reply_count
    FROM tickets t 
    WHERE t.user_id = ? 
    ORDER BY t.updated_at DESC");
$stmt->execute([$userId]);
$tickets = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">My Support Tickets</h1>
    <p style="color: var(--color-muted);">Direct line to Taylor's University community coordinators and technical support staff.</p>
  </div>
  <a href="<?= BASE_URL ?>/member/ticket_create.php" class="btn btn-primary">
    + Create Support Ticket
  </a>
</div>

<?php if (empty($tickets)): ?>
  <div style="background: #fff; border: 1px dashed var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center;">
    <h3 style="margin-bottom: 8px;">No support tickets raised</h3>
    <p style="color: var(--color-muted); font-size: 15px; margin-bottom: 24px;">
      Need help with documents, program application, or bazaar listings? Create a ticket and we'll assist you.
    </p>
    <a href="<?= BASE_URL ?>/member/ticket_create.php" class="btn btn-primary">
      Create Ticket
    </a>
  </div>
<?php else: ?>
  <div class="table-wrapper">
    <div class="table-header-bar">
      <div style="font-size: 16px; font-weight: 700;">Tickets (<?= count($tickets) ?>)</div>
      <input type="text" placeholder="Search my tickets..." class="table-search-input">
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Ticket No</th>
          <th>Subject</th>
          <th>Category</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Updated</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td style="font-family: monospace; font-weight: 700;">#<?= e($t['ticket_no']) ?></td>
            <td>
              <a href="<?= BASE_URL ?>/member/ticket_thread.php?id=<?= $t['id'] ?>" style="font-weight: 700; color: var(--color-ink);" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='var(--color-ink)'">
                <?= e($t['subject']) ?>
              </a>
              <div class="text-caption"><?= (int)$t['reply_count'] ?> messages in thread</div>
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
            <td style="color: var(--color-muted); font-size: 13px;">
              <?= date('d M Y, h:i A', strtotime($t['updated_at'])) ?>
            </td>
            <td style="text-align: right;">
              <a href="<?= BASE_URL ?>/member/ticket_thread.php?id=<?= $t['id'] ?>" class="btn btn-secondary btn-sm">
                Open Thread →
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
