<?php
/**
 * Member Events (Joined & Waitlist)
 * Matches Figma: 02 Member workspace/08 My Events.png, 09 My Events · Waitlist.png, 10 Cancel Event.png, 11 Event Feedback.png
 */

$pageTitle = "My Events";
require_once __DIR__ . '/../includes/user_header.php';
$pdo = get_db();
$userId = $user['id'];

// Handle cancel event action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    verify_csrf();
    $regId = (int)$_POST['reg_id'];

    $stmtReg = $pdo->prepare("SELECT r.*, e.title, e.id as event_id FROM event_registrations r JOIN events e ON r.event_id = e.id WHERE r.id = ? AND r.user_id = ?");
    $stmtReg->execute([$regId, $userId]);
    $reg = $stmtReg->fetch();

    if ($reg) {
        $pdo->prepare("UPDATE event_registrations SET status = 'cancelled' WHERE id = ?")->execute([$regId]);

        // Promote waitlist if slot opened
        if ($reg['status'] === 'joined') {
            $stmtWait = $pdo->prepare("SELECT * FROM event_registrations WHERE event_id = ? AND status = 'waitlisted' ORDER BY joined_at ASC LIMIT 1");
            $stmtWait->execute([$reg['event_id']]);
            $next = $stmtWait->fetch();
            if ($next) {
                $pdo->prepare("UPDATE event_registrations SET status = 'joined' WHERE id = ?")->execute([$next['id']]);
                create_notification($next['user_id'], 'event', 'Promoted from Waitlist!', "A spot opened up for {$reg['title']} and you have been promoted to confirmed attendance!", 'member/events.php');
            }
        }

        log_audit('CANCEL_EVENT_REG', 'event_registrations', $regId, "Cancelled registration for {$reg['title']}");
        set_flash('info', "Your registration for '{$reg['title']}' has been cancelled.");
        header('Location: ' . BASE_URL . '/member/events.php');
        exit;
    }
}

// Fetch joined events
$stmtJoined = $pdo->prepare("SELECT r.id as reg_id, r.status as reg_status, r.joined_at, e.*,
    (SELECT rating FROM event_feedback fb WHERE fb.event_id = e.id AND fb.user_id = ?) as user_rating
    FROM event_registrations r JOIN events e ON r.event_id = e.id 
    WHERE r.user_id = ? AND r.status = 'joined' 
    ORDER BY e.start_at ASC");
$stmtJoined->execute([$userId, $userId]);
$joinedEvents = $stmtJoined->fetchAll();

// Fetch waitlisted events
$stmtWait = $pdo->prepare("SELECT r.id as reg_id, r.status as reg_status, r.joined_at, e.* 
    FROM event_registrations r JOIN events e ON r.event_id = e.id 
    WHERE r.user_id = ? AND r.status = 'waitlisted' 
    ORDER BY r.joined_at ASC");
$stmtWait->execute([$userId]);
$waitlistEvents = $stmtWait->fetchAll();

$tab = $_GET['tab'] ?? 'joined';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">My Events</h1>
    <p style="color: var(--color-muted);">Manage your registered campus workshops, webinars, and waitlist bookings.</p>
  </div>
  <a href="<?= BASE_URL ?>/events.php" class="btn btn-primary">
    + Browse More Events
  </a>
</div>

<!-- Tabs (Joined vs Waitlist) -->
<div style="display: flex; gap: 12px; margin-bottom: 32px; border-bottom: 1px solid var(--color-line); padding-bottom: 12px;">
  <a href="?tab=joined" class="btn <?= ($tab === 'joined') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Joined Sessions (<?= count($joinedEvents) ?>)
  </a>
  <a href="?tab=waitlist" class="btn <?= ($tab === 'waitlist') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Waitlist (<?= count($waitlistEvents) ?>)
  </a>
</div>

<?php if ($tab === 'joined'): ?>
  <!-- Joined Events List -->
  <?php if (empty($joinedEvents)): ?>
    <div style="background: #fff; border: 1px dashed var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center;">
      <h3 style="margin-bottom: 8px;">No joined events yet</h3>
      <p style="color: var(--color-muted); font-size: 15px; margin-bottom: 20px;">
        Browse upcoming sessions hosted at Taylor's Lakeside Campus.
      </p>
      <a href="<?= BASE_URL ?>/events.php" class="btn btn-primary">Browse Events</a>
    </div>
  <?php else: ?>
    <div class="card-grid">
      <?php foreach ($joinedEvents as $ev): ?>
        <?php $isPast = (strtotime($ev['start_at']) < time()); ?>
        <div class="content-card">
          <img src="<?= BASE_URL ?>/<?= e($ev['poster_path'] ?: 'assets/images/20 Sample Photography - Kitchen.png') ?>" alt="<?= e($ev['title']) ?>" class="card-cover">
          <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div class="card-category"><?= e($ev['category']) ?></div>
              <?php if ($isPast): ?>
                <span class="badge badge-neutral">Completed</span>
              <?php else: ?>
                <span class="badge badge-positive">Confirmed</span>
              <?php endif; ?>
            </div>

            <div class="card-title"><?= e($ev['title']) ?></div>
            
            <div style="font-size: 14px; color: var(--color-ink); font-weight: 600;">
              📅 <?= date('D, d M Y · h:i A', strtotime($ev['start_at'])) ?>
            </div>

            <div style="font-size: 13px; color: var(--color-muted);">
              📍 <?= e($ev['venue']) ?>
            </div>

            <?php if ($isPast && $ev['user_rating']): ?>
              <div style="font-size: 13px; color: #f59e0b; margin-top: 4px;">
                Your rating: <?= str_repeat('★', (int)$ev['user_rating']) ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="card-footer" style="gap: 10px;">
            <a href="<?= BASE_URL ?>/event_detail.php?id=<?= $ev['id'] ?>" class="btn btn-secondary btn-sm" style="flex: 1;">
              <?= $isPast ? 'Rate / Review' : 'View details' ?>
            </a>

            <?php if (!$isPast): ?>
              <form method="POST" action="" onsubmit="return confirm('Are you sure you want to cancel your attendance? Your slot will be released to waitlisted members.');">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="reg_id" value="<?= $ev['reg_id'] ?>">
                <button type="submit" class="btn btn-destructive btn-sm">
                  Cancel
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php else: ?>
  <!-- Waitlisted Events List (Matches Figma 09 My Events · Waitlist.png) -->
  <?php if (empty($waitlistEvents)): ?>
    <div style="background: #fff; border: 1px dashed var(--color-line); border-radius: var(--radius-md); padding: 48px; text-align: center;">
      <h3 style="margin-bottom: 8px;">No waitlisted events</h3>
      <p style="color: var(--color-muted); font-size: 15px;">
        When an event reaches full capacity, your registration will appear here while waiting for openings.
      </p>
    </div>
  <?php else: ?>
    <div class="card-grid">
      <?php foreach ($waitlistEvents as $ev): ?>
        <div class="content-card">
          <img src="<?= BASE_URL ?>/<?= e($ev['poster_path'] ?: 'assets/images/20 Sample Photography - Kitchen.png') ?>" alt="<?= e($ev['title']) ?>" class="card-cover">
          <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div class="card-category"><?= e($ev['category']) ?></div>
              <span class="badge badge-attention">Waitlisted</span>
            </div>

            <div class="card-title"><?= e($ev['title']) ?></div>
            
            <div style="font-size: 14px; color: var(--color-ink); font-weight: 600;">
              📅 <?= date('D, d M Y · h:i A', strtotime($ev['start_at'])) ?>
            </div>

            <div style="font-size: 13px; color: var(--color-muted);">
              📍 <?= e($ev['venue']) ?>
            </div>

            <div style="background: #fff3e0; color: #e65100; font-size: 13px; padding: 10px; border-radius: var(--radius-sm); margin-top: auto;">
              ⏳ Position reserved. You will be automatically notified if an attendee cancels.
            </div>
          </div>

          <div class="card-footer">
            <form method="POST" action="" style="width: 100%;" onsubmit="return confirm('Leave waitlist for this event?');">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="cancel">
              <input type="hidden" name="reg_id" value="<?= $ev['reg_id'] ?>">
              <button type="submit" class="btn btn-outline btn-sm" style="width: 100%;">
                Leave waitlist
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
