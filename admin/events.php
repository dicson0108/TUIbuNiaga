<?php
/**
 * Admin Event Management
 * Matches Figma: 04 Admin workspace/05 Event Management.png & 07 Delete Event.png
 */

$pageTitle = "Event Management";
require_once __DIR__ . '/../includes/admin_header.php';
$pdo = get_db();

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    verify_csrf();
    $evId = (int)$_POST['event_id'];
    $stmtDel = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmtDel->execute([$evId]);
    log_audit('DELETE_EVENT', 'events', $evId, "Admin deleted event #{$evId}");
    set_flash('info', 'Event deleted permanently.');
    header('Location: ' . BASE_URL . '/admin/events.php');
    exit;
}

$stmt = $pdo->query("SELECT e.*, 
    (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id AND r.status = 'joined') as joined_count,
    (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id AND r.status = 'waitlisted') as waitlist_count,
    (SELECT AVG(rating) FROM event_feedback fb WHERE fb.event_id = e.id AND fb.status = 'visible') as avg_rating
    FROM events e 
    ORDER BY e.start_at DESC");
$events = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Event Management</h1>
    <p style="color: var(--color-muted);">Create campus workshops, webinars, manage venues, and track participant attendance and waitlists.</p>
  </div>

  <a href="<?= BASE_URL ?>/admin/event_editor.php" class="btn btn-primary">
    + Create New Event
  </a>
</div>

<div class="table-wrapper">
  <div class="table-header-bar">
    <div style="font-size: 16px; font-weight: 700;">Scheduled Events (<?= count($events) ?>)</div>
    <input type="text" placeholder="Search events..." class="table-search-input">
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <th>Event Title</th>
        <th>Category / Type</th>
        <th>Date & Time</th>
        <th>Venue</th>
        <th>Capacity / Attendance</th>
        <th>Rating</th>
        <th>Status</th>
        <th style="text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($events)): ?>
        <tr>
          <td colspan="8" style="text-align: center; color: var(--color-muted); padding: 48px;">
            No events scheduled yet.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($events as $ev): ?>
          <tr>
            <td>
              <div style="font-weight: 700; color: var(--color-ink);"><?= e($ev['title']) ?></div>
              <div class="text-caption">by <?= e($ev['speaker'] ?: "Taylor's Culinary Mentors") ?></div>
            </td>
            <td>
              <div><?= e($ev['category']) ?></div>
              <span class="badge badge-neutral" style="font-size: 11px; padding: 2px 6px;"><?= e($ev['type']) ?></span>
            </td>
            <td style="color: var(--color-ink); font-size: 13px; white-space: nowrap;">
              <?= date('d M Y', strtotime($ev['start_at'])) ?><br>
              <span class="text-caption"><?= date('h:i A', strtotime($ev['start_at'])) ?></span>
            </td>
            <td style="max-width: 180px; font-size: 13px; color: var(--color-muted);"><?= e($ev['venue']) ?></td>
            <td style="font-size: 13px; white-space: nowrap;">
              <strong><?= (int)$ev['joined_count'] ?></strong> / <?= (int)$ev['capacity'] ?>
              <?php if ($ev['waitlist_count'] > 0): ?>
                <br><span style="color: #e65100; font-size: 11px;">(<?= (int)$ev['waitlist_count'] ?> on waitlist)</span>
              <?php endif; ?>
            </td>
            <td style="color: #f59e0b; font-weight: 700; font-size: 13px;">
              <?= $ev['avg_rating'] ? number_format($ev['avg_rating'], 1) . ' ★' : '-' ?>
            </td>
            <td>
              <?php if ($ev['status'] === 'Upcoming'): ?>
                <span class="badge badge-positive">Upcoming</span>
              <?php elseif ($ev['status'] === 'Completed'): ?>
                <span class="badge badge-neutral">Completed</span>
              <?php else: ?>
                <span class="badge badge-destructive">Cancelled</span>
              <?php endif; ?>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <div style="display: inline-flex; gap: 6px;">
                <a href="<?= BASE_URL ?>/admin/event_attendees.php?id=<?= $ev['id'] ?>" class="btn btn-outline btn-sm" title="View Registered Attendees">
                  Attendees (<?= (int)$ev['joined_count'] ?>)
                </a>
                <a href="<?= BASE_URL ?>/admin/event_editor.php?id=<?= $ev['id'] ?>" class="btn btn-secondary btn-sm">
                  Edit
                </a>
                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this event?');">
                  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="event_id" value="<?= $ev['id'] ?>">
                  <button type="submit" class="btn btn-destructive btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
