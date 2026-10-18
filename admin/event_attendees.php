<?php
/**
 * Admin Event Attendees & Waitlist Management
 * Matches Figma: 04 Admin workspace/08 Event Attendees.png
 */

require_once __DIR__ . '/../includes/admin_header.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: ' . BASE_URL . '/admin/events.php');
    exit;
}

// Handle Attendee actions (promote, remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();
    $regId = (int)$_POST['reg_id'];
    $act = $_POST['action'];

    if ($act === 'promote') {
        $pdo->prepare("UPDATE event_registrations SET status = 'joined' WHERE id = ?")->execute([$regId]);
        
        $stmtUser = $pdo->prepare("SELECT user_id FROM event_registrations WHERE id = ?");
        $stmtUser->execute([$regId]);
        $uid = $stmtUser->fetchColumn();
        if ($uid) {
            create_notification($uid, 'event', 'Promoted from Waitlist!', "An administrator confirmed your seat for '{$event['title']}'!", 'member/events.php');
        }
        set_flash('success', 'Participant promoted to confirmed attendance.');
    } elseif ($act === 'remove') {
        $pdo->prepare("DELETE FROM event_registrations WHERE id = ?")->execute([$regId]);
        set_flash('info', 'Participant registration removed.');
    }
    header("Location: " . BASE_URL . "/admin/event_attendees.php?id=" . $id);
    exit;
}

// CSV Export of attendees
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=attendees_' . preg_replace('/[^a-zA-Z0-9]+/', '_', $event['title']) . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Email', 'Phone', 'Status', 'Registered At']);

    $stmtExp = $pdo->prepare("SELECT u.name, u.email, u.phone, r.status, r.joined_at FROM event_registrations r JOIN users u ON r.user_id = u.id WHERE r.event_id = ? ORDER BY r.status ASC, r.joined_at ASC");
    $stmtExp->execute([$id]);
    while ($row = $stmtExp->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

// Fetch registrations
$stmtRegs = $pdo->prepare("SELECT r.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
    FROM event_registrations r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.event_id = ? 
    ORDER BY (r.status = 'joined') DESC, r.joined_at ASC");
$stmtRegs->execute([$id]);
$registrations = $stmtRegs->fetchAll();

$joinedCount = 0;
$waitlistCount = 0;
foreach ($registrations as $r) {
    if ($r['status'] === 'joined') $joinedCount++;
    if ($r['status'] === 'waitlisted') $waitlistCount++;
}

$pageTitle = "Attendees · " . $event['title'];
?>

<div style="max-width: 960px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/admin/events.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to Event Management</a>
  </div>

  <!-- Event Summary Header -->
  <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 28px; margin-bottom: 28px; display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 16px;">
    <div>
      <div class="card-category" style="margin-bottom: 4px;"><?= e($event['category']) ?> · <?= date('d M Y', strtotime($event['start_at'])) ?></div>
      <h1 style="font-size: 26px; margin-bottom: 6px;"><?= e($event['title']) ?></h1>
      <div style="font-size: 14px; color: var(--color-muted);">
        📍 <?= e($event['venue']) ?> · Capacity Limit: <strong><?= (int)$event['capacity'] ?></strong>
      </div>
    </div>

    <div style="display: flex; gap: 10px; align-items: center;">
      <a href="?id=<?= $id ?>&export=csv" class="btn btn-outline btn-sm">
        📥 Export Attendees CSV
      </a>
      <a href="<?= BASE_URL ?>/admin/event_editor.php?id=<?= $id ?>" class="btn btn-secondary btn-sm">
        Edit Event
      </a>
    </div>
  </div>

  <!-- Stat Pills -->
  <div style="display: flex; gap: 16px; margin-bottom: 24px;">
    <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-sm); padding: 14px 20px; flex: 1;">
      <span class="text-caption">Confirmed Attendance</span>
      <div style="font-size: 24px; font-weight: 800; color: var(--color-ink); margin-top: 2px;">
        <?= $joinedCount ?> / <?= (int)$event['capacity'] ?>
      </div>
    </div>

    <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-sm); padding: 14px 20px; flex: 1;">
      <span class="text-caption">Waitlist Queue</span>
      <div style="font-size: 24px; font-weight: 800; color: #e65100; margin-top: 2px;">
        <?= $waitlistCount ?>
      </div>
    </div>
  </div>

  <div class="table-wrapper">
    <div class="table-header-bar">
      <div style="font-size: 16px; font-weight: 700;">Registered Participants (<?= count($registrations) ?>)</div>
      <input type="text" placeholder="Search participant..." class="table-search-input">
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Participant Name</th>
          <th>Email Address</th>
          <th>Phone</th>
          <th>Status</th>
          <th>Registration Time</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($registrations)): ?>
          <tr>
            <td colspan="6" style="text-align: center; color: var(--color-muted); padding: 48px;">
              No registrations recorded for this event yet.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($registrations as $r): ?>
            <tr>
              <td style="font-weight: 700; color: var(--color-ink);"><?= e($r['user_name']) ?></td>
              <td><?= e($r['user_email']) ?></td>
              <td><?= e($r['user_phone'] ?: '-') ?></td>
              <td>
                <?php if ($r['status'] === 'joined'): ?>
                  <span class="badge badge-positive">Confirmed</span>
                <?php else: ?>
                  <span class="badge badge-attention">Waitlisted</span>
                <?php endif; ?>
              </td>
              <td style="color: var(--color-muted); font-size: 13px;">
                <?= date('d M Y, h:i A', strtotime($r['joined_at'])) ?>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <?php if ($r['status'] === 'waitlisted'): ?>
                  <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="promote">
                    <input type="hidden" name="reg_id" value="<?= $r['id'] ?>">
                    <button type="submit" class="btn btn-primary btn-sm" style="background:#2e7d32; border-color:#2e7d32;">
                      Promote to Confirmed
                    </button>
                  </form>
                <?php endif; ?>

                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Remove registration for this user?');">
                  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="reg_id" value="<?= $r['id'] ?>">
                  <button type="submit" class="btn btn-destructive btn-sm">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
