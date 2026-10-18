<?php
/**
 * Moderator Dashboard
 * Matches Figma: 03 Moderator workspace/01 Moderator Dashboard.png
 */

$pageTitle = "Moderator Dashboard";
require_once __DIR__ . '/../includes/mod_header.php';
$pdo = get_db();

// 1. Pending applications count
$stmtApps = $pdo->query("SELECT COUNT(*) FROM applications WHERE status IN ('Submitted', 'In Review', 'Needs Documents')");
$pendingAppsCount = (int)$stmtApps->fetchColumn();

// 2. Open tickets
$stmtTickets = $pdo->query("SELECT COUNT(*) AS total, SUM(CASE WHEN `priority` = 'High' THEN 1 ELSE 0 END) AS `high_priority` FROM `tickets` WHERE `status` IN ('Open', 'In Progress')");
$ticketStats = $stmtTickets->fetch();



// 3. Flagged / Hidden content
$stmtFlaggedFeed = $pdo->query("SELECT COUNT(*) FROM event_feedback WHERE status = 'hidden'");
$flaggedFeedCount = (int)$stmtFlaggedFeed->fetchColumn();

$stmtPendingBaz = $pdo->query("SELECT COUNT(*) FROM bazaar_listings WHERE status = 'pending'");
$pendingBazCount = (int)$stmtPendingBaz->fetchColumn();

// 4. Recent activity log
$stmtAudit = $pdo->query("SELECT a.*, COALESCE(u.name, 'System') as user_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 5");
$recentActivities = $stmtAudit->fetchAll();
?>


<!-- Header -->
<div style="margin-bottom: 32px;">
  <h1 class="display-title" style="font-size: 38px; margin-bottom: 8px;">A clear view of what needs you.</h1>
  <p style="font-size: 16px; color: var(--color-muted);">
    Keep applications, content and community support moving.
  </p>
</div>

<!-- 4 Key Stat Metric Cards (Matches Figma 01 Moderator Dashboard) -->
<div class="stats-grid" style="margin-top: 0; margin-bottom: 32px;">
  <div class="stat-card">
    <span class="stat-label">Pending applications</span>
    <span class="stat-value"><?= $pendingAppsCount ?></span>
    <span class="stat-sub">Awaiting decision</span>
  </div>

  <div class="stat-card">
    <span class="stat-label">Open tickets</span>
    <span class="stat-value"><?= (int)($ticketStats['total'] ?? 0) ?></span>
    <span class="stat-sub"><?= (int)($ticketStats['high_priority'] ?? 0) ?> high priority</span>
  </div>

  <div class="stat-card">
    <span class="stat-label">Flagged comments</span>
    <span class="stat-value"><?= $flaggedFeedCount ?></span>
    <span class="stat-sub">Hidden from public</span>
  </div>

  <div class="stat-card">
    <span class="stat-label">Listings to review</span>
    <span class="stat-value"><?= $pendingBazCount ?></span>
    <span class="stat-sub">Bazaar queue</span>
  </div>
</div>

<!-- Middle Section: Visual summary & Your Priorities -->
<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; margin-bottom: 36px;">
  <!-- Application Activity Summary Box -->
  <div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 32px; display: flex; flex-direction: column;">
    <div style="font-size: 18px; font-weight: 700; color: var(--color-ink); margin-bottom: 24px;">
      Application activity
    </div>

    <!-- Monthly Bar Chart Visual -->
    <div style="display: flex; align-items: flex-end; justify-content: space-between; height: 180px; padding-bottom: 12px; border-bottom: 1px solid var(--color-line);">
      <div style="text-align: center; flex: 1;">
        <div style="height: 40px; background: var(--color-sage); width: 44px; margin: 0 auto; border-radius: 4px 4px 0 0;"></div>
        <div class="text-caption" style="margin-top: 8px;">May</div>
      </div>
      <div style="text-align: center; flex: 1;">
        <div style="height: 75px; background: var(--color-sage); width: 44px; margin: 0 auto; border-radius: 4px 4px 0 0;"></div>
        <div class="text-caption" style="margin-top: 8px;">Jun</div>
      </div>
      <div style="text-align: center; flex: 1;">
        <div style="height: 60px; background: var(--color-sage); width: 44px; margin: 0 auto; border-radius: 4px 4px 0 0;"></div>
        <div class="text-caption" style="margin-top: 8px;">Jul</div>
      </div>
      <div style="text-align: center; flex: 1;">
        <div style="height: 110px; background: var(--color-sage); width: 44px; margin: 0 auto; border-radius: 4px 4px 0 0;"></div>
        <div class="text-caption" style="margin-top: 8px;">Aug</div>
      </div>
      <div style="text-align: center; flex: 1;">
        <div style="height: 90px; background: var(--color-sage); width: 44px; margin: 0 auto; border-radius: 4px 4px 0 0;"></div>
        <div class="text-caption" style="margin-top: 8px;">Sep</div>
      </div>
      <div style="text-align: center; flex: 1;">
        <div style="height: 155px; background: var(--color-primary); width: 44px; margin: 0 auto; border-radius: 4px 4px 0 0;"></div>
        <div class="text-caption" style="margin-top: 8px; font-weight: 700; color: var(--color-primary);">Oct</div>
      </div>
    </div>
  </div>

  <!-- Your Priorities Box (Matches Figma 01 Moderator Dashboard) -->
  <div style="background: var(--color-cream); border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 32px; display: flex; flex-direction: column;">
    <div style="font-size: 18px; font-weight: 700; color: var(--color-ink); margin-bottom: 20px;">
      Your priorities
    </div>

    <div style="display: flex; flex-direction: column; gap: 14px; flex-grow: 1; justify-content: center;">
      <a href="<?= BASE_URL ?>/moderator/applications.php" class="btn btn-outline" style="background: #fff; justify-content: center; padding: 14px; font-weight: 700;">
        Review programme applications (<?= $pendingAppsCount ?>)
      </a>
      <a href="<?= BASE_URL ?>/moderator/tickets.php" class="btn btn-outline" style="background: #fff; justify-content: center; padding: 14px; font-weight: 700;">
        Reply to open support tickets (<?= (int)$ticketStats['total'] ?>)
      </a>
      <a href="<?= BASE_URL ?>/moderator/bazaar_moderation.php" class="btn btn-outline" style="background: #fff; justify-content: center; padding: 14px; font-weight: 700;">
        Review bazaar listings (<?= $pendingBazCount ?>)
      </a>
    </div>
  </div>
</div>

<!-- Recent Activity Log Table -->
<div class="table-wrapper">
  <div class="table-header-bar">
    <div style="font-size: 16px; font-weight: 700;">Recent Operational Activity</div>
  </div>

  <table class="data-table">
    <thead>
      <tr>
        <th>Action & Entity</th>
        <th>Performed By</th>
        <th>Time</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($recentActivities)): ?>
        <tr>
          <td colspan="3" style="text-align: center; color: var(--color-muted); padding: 32px;">
            No recent operational events recorded.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($recentActivities as $act): ?>
          <tr>
            <td>
              <div style="font-weight: 600; color: var(--color-ink);"><?= e($act['details'] ?: $act['action']) ?></div>
              <div class="text-caption"><?= e($act['entity']) ?> #<?= e($act['entity_id']) ?> · <?= e($act['action']) ?></div>
            </td>
            <td style="color: var(--color-ink); font-weight: 500;">
              <?= e($act['user_name'] ?: 'System / Moderator') ?>
            </td>
            <td style="color: var(--color-muted); font-size: 13px;">
              <?= date('d M Y, h:i A', strtotime($act['created_at'])) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
