<?php
/**
 * Event Detail & Participation Page
 * Matches Figma: 13 Event Detail.png & 14 Event Detail · Full.png
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = get_db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT e.*, 
        (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id AND r.status = 'joined') as joined_count,
        (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id AND r.status = 'waitlisted') as waitlist_count
        FROM events e WHERE e.id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    header("Location: " . BASE_URL . "/events.php");
    exit;
}

$user = current_user();
$userReg = null;
if ($user) {
    $stmtReg = $pdo->prepare("SELECT * FROM event_registrations WHERE event_id = ? AND user_id = ?");
    $stmtReg->execute([$id, $user['id']]);
    $userReg = $stmtReg->fetch();
}

// Handle Join / Waitlist / Cancel POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();
    require_login();
    $action = $_POST['action'];

    if ($action === 'join') {
        $isFull = ($event['joined_count'] >= $event['capacity']);
        $newStatus = $isFull ? 'waitlisted' : 'joined';

        if ($userReg) {
            $stmtUp = $pdo->prepare("UPDATE event_registrations SET status = ?, joined_at = NOW() WHERE id = ?");
            $stmtUp->execute([$newStatus, $userReg['id']]);
        } else {
            $stmtIns = $pdo->prepare("INSERT INTO event_registrations (event_id, user_id, status, joined_at) VALUES (?, ?, ?, NOW())");
            $stmtIns->execute([$id, $user['id'], $newStatus]);
        }

        if ($newStatus === 'waitlisted') {
            set_flash('info', 'Event is currently at maximum capacity. You have been added to the waitlist. You will be automatically notified if a place opens up.');
            create_notification($user['id'], 'event', 'Added to Waitlist', "You joined the waitlist for {$event['title']}.", 'member/events.php');
        } else {
            set_flash('success', "You have successfully registered for {$event['title']}! See you at Taylor's Lakeside Campus.");
            create_notification($user['id'], 'event', 'Registration Confirmed', "Your place for {$event['title']} is confirmed.", 'member/events.php');
        }
        header("Location: " . BASE_URL . "/event_detail.php?id=" . $id);
        exit;
    }

    if ($action === 'cancel') {
        if ($userReg) {
            $stmtDel = $pdo->prepare("UPDATE event_registrations SET status = 'cancelled' WHERE id = ?");
            $stmtDel->execute([$userReg['id']]);

            // Promote first person on waitlist if spots opened
            $stmtWait = $pdo->prepare("SELECT * FROM event_registrations WHERE event_id = ? AND status = 'waitlisted' ORDER BY joined_at ASC LIMIT 1");
            $stmtWait->execute([$id]);
            $nextWait = $stmtWait->fetch();
            if ($nextWait) {
                $stmtPromote = $pdo->prepare("UPDATE event_registrations SET status = 'joined' WHERE id = ?");
                $stmtPromote->execute([$nextWait['id']]);
                create_notification($nextWait['user_id'], 'event', 'Promoted from Waitlist!', "A spot opened up for {$event['title']} and you have been promoted to confirmed attendance!", 'member/events.php');
            }

            set_flash('info', 'Your event registration has been cancelled.');
        }
        header("Location: " . BASE_URL . "/event_detail.php?id=" . $id);
        exit;
    }

    if ($action === 'feedback') {
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');
        if ($comment !== '') {
            $stmtFb = $pdo->prepare("INSERT INTO event_feedback (event_id, user_id, rating, comment, status) VALUES (?, ?, ?, ?, 'visible')");
            $stmtFb->execute([$id, $user['id'], $rating, $comment]);
            set_flash('success', 'Thank you for your feedback! Your review helps mentors improve future sessions.');
        }
        header("Location: " . BASE_URL . "/event_detail.php?id=" . $id);
        exit;
    }
}

// Fetch feedback
$stmtFeed = $pdo->prepare("SELECT f.*, u.name as user_name FROM event_feedback f JOIN users u ON f.user_id = u.id WHERE f.event_id = ? AND f.status = 'visible' ORDER BY f.created_at DESC");
$stmtFeed->execute([$id]);
$feedbacks = $stmtFeed->fetchAll();

$isFull = ($event['joined_count'] >= $event['capacity']);
$spotsLeft = max(0, $event['capacity'] - $event['joined_count']);
$pageTitle = $event['title'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width: 920px;">
    <div style="margin-bottom: 24px;">
      <a href="<?= BASE_URL ?>/events.php" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to all events</a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
      <div class="card-category">
        <?= e($event['category']) ?> · <?= e($event['type']) ?>
      </div>
      <div>
        <?php if ($isFull): ?>
          <span class="badge badge-attention">Full · Waitlist Available</span>
        <?php else: ?>
          <span class="badge badge-positive"><?= $spotsLeft ?> spots left (<?= $event['joined_count'] ?>/<?= $event['capacity'] ?> registered)</span>
        <?php endif; ?>
      </div>
    </div>

    <h1 class="display-title" style="margin-bottom: 24px; font-size: 38px;"><?= e($event['title']) ?></h1>

    <img src="<?= BASE_URL ?>/<?= e($event['poster_path'] ?: 'assets/images/20 Sample Photography - Kitchen.png') ?>" alt="<?= e($event['title']) ?>" style="width: 100%; height: 420px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 32px; border: 1px solid var(--color-line);">

    <!-- Event Key Details Bar -->
    <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 28px; margin-bottom: 36px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px;">
      <div>
        <span class="text-caption">Date & Time</span>
        <div style="font-size: 16px; font-weight: 700; color: var(--color-ink); margin-top: 4px;">
          <?= date('l, d F Y', strtotime($event['start_at'])) ?>
        </div>
        <div style="font-size: 14px; color: var(--color-muted);">
          <?= date('h:i A', strtotime($event['start_at'])) ?> – <?= date('h:i A', strtotime($event['end_at'])) ?>
        </div>
      </div>

      <div>
        <span class="text-caption">Location / Venue</span>
        <div style="font-size: 16px; font-weight: 700; color: var(--color-ink); margin-top: 4px;">
          <?= e($event['venue']) ?>
        </div>
        <div style="font-size: 13px; color: var(--color-muted);">
          Taylor's Lakeside Campus
        </div>
      </div>

      <div>
        <span class="text-caption">Facilitator / Speaker</span>
        <div style="font-size: 16px; font-weight: 700; color: var(--color-ink); margin-top: 4px;">
          <?= e($event['speaker'] ?: "Taylor's Culinary Faculty Mentors") ?>
        </div>
        <div style="font-size: 13px; color: var(--color-muted);">
          Verified Mentor
        </div>
      </div>
    </div>

    <!-- Registration CTA Block -->
    <div style="background-color: var(--color-cream); border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 32px; margin-bottom: 40px;">
      <h3 style="margin-bottom: 8px;">Event Registration</h3>
      <p style="font-size: 15px; color: var(--color-muted); margin-bottom: 24px;">
        Free for all verified Ibu Niaga community members and Taylor's community participants.
      </p>

      <?php if (!is_logged_in()): ?>
        <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
          <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary btn-lg">
            Log in to Join Event
          </a>
          <a href="<?= BASE_URL ?>/register.php" class="btn btn-secondary btn-lg">
            Register new account
          </a>
        </div>
      <?php else: ?>
        <?php if ($userReg && $userReg['status'] === 'joined'): ?>
          <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; background: #e8f5e9; padding: 16px 20px; border-radius: var(--radius-sm); border: 1px solid #c8e6c9;">
            <div style="color: #2e7d32; font-weight: 600;">
              ✓ You are registered for this event!
            </div>
            <form method="POST" action="">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="cancel">
              <button type="submit" class="btn btn-destructive btn-sm" data-confirm="Are you sure you want to cancel your registration? Your place will be offered to the waitlist.">
                Cancel participation
              </button>
            </form>
          </div>
        <?php elseif ($userReg && $userReg['status'] === 'waitlisted'): ?>
          <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; background: #fff3e0; padding: 16px 20px; border-radius: var(--radius-sm); border: 1px solid #ffe0b2;">
            <div style="color: #e65100; font-weight: 600;">
              ⏳ You are on the waitlist (Position #<?= $event['waitlist_count'] ?>). We will notify you if a slot opens!
            </div>
            <form method="POST" action="">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="cancel">
              <button type="submit" class="btn btn-outline btn-sm">
                Leave waitlist
              </button>
            </form>
          </div>
        <?php else: ?>
          <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="join">
            <?php if ($isFull): ?>
              <button type="submit" class="btn btn-primary btn-lg" style="background: #e65100; border-color: #e65100;">
                Join Waitlist
              </button>
            <?php else: ?>
              <button type="submit" class="btn btn-primary btn-lg">
                Join Event (1-Click)
              </button>
            <?php endif; ?>
          </form>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Description -->
    <div style="margin-bottom: 48px;">
      <h2 style="margin-bottom: 16px;">About this Event</h2>
      <div style="font-size: 16px; line-height: 1.7; color: var(--color-ink);">
        <?= nl2br(e($event['description'])) ?>
      </div>
    </div>

    <!-- Reviews & Feedback Section (PRD EV-06) -->
    <div style="border-top: 1px solid var(--color-line); padding-top: 40px; margin-bottom: 40px;">
      <h2 style="margin-bottom: 24px;">Community Feedback & Ratings</h2>

      <?php if (is_logged_in() && $userReg && $userReg['status'] === 'joined'): ?>
        <!-- Feedback form for registered attendees -->
        <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 24px; margin-bottom: 32px;">
          <h4 style="margin-bottom: 12px;">Share your experience</h4>
          <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="feedback">
            
            <div class="form-group">
              <label class="form-label">Rating</label>
              <select name="rating" class="form-select" style="max-width: 200px;">
                <option value="5">★★★★★ (5/5 Excellent)</option>
                <option value="4">★★★★☆ (4/5 Very Good)</option>
                <option value="3">★★★☆☆ (3/5 Good)</option>
                <option value="2">★★☆☆☆ (2/5 Fair)</option>
                <option value="1">★☆☆☆☆ (1/5 Poor)</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Your comments & learnings</label>
              <textarea name="comment" class="form-textarea" placeholder="How was the session? What skills did you learn?" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit review</button>
          </form>
        </div>
      <?php endif; ?>

      <!-- Feedback List -->
      <?php if (empty($feedbacks)): ?>
        <p style="color: var(--color-muted);">No community reviews posted yet. Be the first to join and review!</p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 16px;">
          <?php foreach ($feedbacks as $fb): ?>
            <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-sm); padding: 20px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div style="font-weight: 700; color: var(--color-ink);">
                  <?= e($fb['user_name']) ?>
                </div>
                <div style="color: #f59e0b; font-size: 14px;">
                  <?= str_repeat('★', (int)$fb['rating']) . str_repeat('☆', 5 - (int)$fb['rating']) ?>
                </div>
              </div>
              <p style="font-size: 14px; color: var(--color-muted); line-height: 1.5; margin: 0;">
                <?= nl2br(e($fb['comment'])) ?>
              </p>
              <div class="text-caption" style="margin-top: 8px;">
                Posted <?= date('d M Y', strtotime($fb['created_at'])) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
