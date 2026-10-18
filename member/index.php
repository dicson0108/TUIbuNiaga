<?php
/**
 * Member Dashboard
 * Matches Figma: 02 Member workspace/01 Dashboard.png
 */

$pageTitle = "Member Dashboard";
require_once __DIR__ . '/../includes/user_header.php';
$pdo = get_db();
$userId = $user['id'];

// 1. My Applications count
$stmtApps = $pdo->prepare("SELECT 
    COUNT(*) as total,
    SUM(status = 'In Review') as in_review,
    SUM(status = 'Approved') as approved,
    SUM(status = 'Needs Documents') as needs_docs
    FROM applications WHERE user_id = ?");
$stmtApps->execute([$userId]);
$appStats = $stmtApps->fetch();

// Active reviewing application for banner
$stmtActiveApp = $pdo->prepare("SELECT a.*, p.title as program_title FROM applications a JOIN programs p ON a.program_id = p.id WHERE a.user_id = ? AND a.status IN ('In Review', 'Submitted', 'Needs Documents') ORDER BY a.updated_at DESC LIMIT 1");
$stmtActiveApp->execute([$userId]);
$activeApp = $stmtActiveApp->fetch();

// 2. Upcoming events count & next event
$stmtEvents = $pdo->prepare("SELECT COUNT(*) FROM event_registrations r JOIN events e ON r.event_id = e.id WHERE r.user_id = ? AND r.status = 'joined' AND e.start_at >= NOW()");
$stmtEvents->execute([$userId]);
$upcomingEventsCount = (int)$stmtEvents->fetchColumn();

$stmtNextEvent = $pdo->prepare("SELECT e.* FROM event_registrations r JOIN events e ON r.event_id = e.id WHERE r.user_id = ? AND r.status = 'joined' AND e.start_at >= NOW() ORDER BY e.start_at ASC LIMIT 1");
$stmtNextEvent->execute([$userId]);
$nextEvent = $stmtNextEvent->fetch();

// 3. Enquiries count
$stmtEnq = $pdo->prepare("SELECT 
    COUNT(*) as total,
    SUM(status = 'new') as need_reply
    FROM enquiries WHERE seller_id = ?");
$stmtEnq->execute([$userId]);
$enqStats = $stmtEnq->fetch();

// 4. Latest updates / activity feed
$stmtUpdates = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmtUpdates->execute([$userId]);
$latestUpdates = $stmtUpdates->fetchAll();
?>

<!-- Greeting Header -->
<div style="margin-bottom: 28px;">
  <h1 class="display-title" style="font-size: 38px; margin-bottom: 8px;">
    Good morning, <?= e(explode(' ', $user['name'])[0]) ?>.
  </h1>
  <p style="font-size: 16px; color: var(--color-muted);">
    Every small step counts. Here's what's happening in your community.
  </p>
</div>

<!-- Active Review Banner if present -->
<?php if ($activeApp): ?>
  <div style="background: var(--color-sage); border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 24px 28px; margin-bottom: 32px;">
    <div style="font-size: 16px; font-weight: 700; color: var(--color-ink); margin-bottom: 6px;">
      Your <?= e($activeApp['program_title']) ?> application is <?= strtolower(e($activeApp['status'])) ?>.
    </div>
    <div style="font-size: 14px; color: var(--color-muted);">
      <?= e($activeApp['remarks'] ?: "We'll let you know when there's an update. You can check progress at any time.") ?>
    </div>
  </div>
<?php endif; ?>

<!-- 4 Key Metric Cards (Matches Figma 01 Dashboard) -->
<div class="stats-grid" style="margin-top: 0; margin-bottom: 32px;">
  <div class="stat-card">
    <span class="stat-label">My applications</span>
    <span class="stat-value"><?= (int)($appStats['total'] ?? 0) ?></span>
    <span class="stat-sub">
      <?= (int)($appStats['in_review'] ?? 0) ?> in review · <?= (int)($appStats['approved'] ?? 0) ?> approved
    </span>
  </div>

  <div class="stat-card">
    <span class="stat-label">Upcoming events</span>
    <span class="stat-value"><?= $upcomingEventsCount ?></span>
    <span class="stat-sub">
      Next: <?= $nextEvent ? date('d F', strtotime($nextEvent['start_at'])) : 'Browse events' ?>
    </span>
  </div>

  <div class="stat-card">
    <span class="stat-label">New enquiries</span>
    <span class="stat-value"><?= (int)($enqStats['total'] ?? 0) ?></span>
    <span class="stat-sub">
      <?= (int)($enqStats['need_reply'] ?? 0) ?> need a reply
    </span>
  </div>

  <div class="stat-card">
    <span class="stat-label">Notifications</span>
    <span class="stat-value"><?= $unreadNotifCount ?></span>
    <span class="stat-sub">
      <?= $unreadNotifCount > 0 ? 'Unread updates' : 'All caught up' ?>
    </span>
  </div>
</div>

<!-- Middle Row: Coming up next & Shortcuts -->
<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; margin-bottom: 36px;">
  <!-- Coming Up Next Card -->
  <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 32px; display: flex; flex-direction: column; justify-content: space-between;">
    <div>
      <div class="text-caption" style="font-weight: 700; text-transform: uppercase; color: var(--color-muted); margin-bottom: 12px;">
        Coming up next
      </div>
      <?php if ($nextEvent): ?>
        <div style="font-size: 20px; font-weight: 800; color: var(--color-ink); margin-bottom: 4px;">
          <?= strtoupper(date('d M · h:i A', strtotime($nextEvent['start_at']))) ?>
        </div>
        <h2 style="font-size: 24px; margin-bottom: 8px;"><?= e($nextEvent['title']) ?></h2>
        <div style="font-size: 14px; color: var(--color-muted); margin-bottom: 24px;">
          <?= e($nextEvent['venue']) ?>
        </div>
      <?php else: ?>
        <h3 style="margin-bottom: 8px;">No upcoming registered events</h3>
        <p style="font-size: 14px; color: var(--color-muted); margin-bottom: 24px;">
          Join upcoming workshops at Taylor's Lakeside Campus to learn new skills.
        </p>
      <?php endif; ?>
    </div>

    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
      <a href="<?= BASE_URL ?>/member/events.php" class="btn btn-primary">
        View my events
      </a>
      <a href="<?= BASE_URL ?>/events.php" class="btn btn-secondary">
        Browse events
      </a>
    </div>
  </div>

  <!-- Your Shortcuts Card -->
  <div style="background: var(--color-cream); border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 32px; display: flex; flex-direction: column;">
    <div style="font-size: 18px; font-weight: 700; color: var(--color-ink); margin-bottom: 20px;">
      Your shortcuts
    </div>

    <div style="display: flex; flex-direction: column; gap: 12px; flex-grow: 1; justify-content: center;">
      <a href="<?= BASE_URL ?>/member/program_registration.php" class="btn btn-outline" style="background: #fff; justify-content: center; padding: 14px;">
        Apply for a programme
      </a>
      <a href="<?= BASE_URL ?>/member/listing_editor.php" class="btn btn-outline" style="background: #fff; justify-content: center; padding: 14px;">
        Add a bazaar listing
      </a>
      <a href="<?= BASE_URL ?>/member/ticket_create.php" class="btn btn-outline" style="background: #fff; justify-content: center; padding: 14px;">
        Get support
      </a>
    </div>
  </div>
</div>

<!-- Latest Updates Table (Matches Figma 01 Dashboard) -->
<div class="table-wrapper">
  <div class="table-header-bar">
    <div style="font-size: 16px; font-weight: 700;">Latest Updates</div>
    <a href="<?= BASE_URL ?>/member/notifications.php" style="font-size: 13px; font-weight: 600; color: var(--color-primary);">
      View all notifications →
    </a>
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <th>Latest update</th>
        <th>Received</th>
        <th style="text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($latestUpdates)): ?>
        <tr>
          <td colspan="3" style="text-align: center; color: var(--color-muted); padding: 32px;">
            No recent activity updates.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($latestUpdates as $up): ?>
          <tr>
            <td>
              <div style="font-weight: 600; color: var(--color-ink);"><?= e($up['title']) ?></div>
              <div style="font-size: 13px; color: var(--color-muted);"><?= e($up['message']) ?></div>
            </td>
            <td style="color: var(--color-muted); font-size: 13px;">
              <?= date('d M · h:i A', strtotime($up['created_at'])) ?>
            </td>
            <td style="text-align: right;">
              <a href="<?= BASE_URL ?>/<?= e($up['link'] ?: 'member/notifications.php') ?>" class="btn btn-secondary btn-sm">
                View →
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
