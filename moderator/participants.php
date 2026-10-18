<?php
/**
 * Moderator Participant Records
 * Matches Figma: 03 Moderator workspace/04 Participant Records.png
 */

$pageTitle = "Participant Records";
require_once __DIR__ . '/../includes/mod_header.php';
$pdo = get_db();

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

// CSV Export trigger
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=ibu_niaga_participants_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Email', 'Phone', 'Business Name', 'Status', 'Registered Date']);

    $stmtExport = $pdo->query("SELECT id, name, email, phone, business_name, status, created_at FROM users WHERE role = 'user' ORDER BY id ASC");
    while ($row = $stmtExport->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM applications a WHERE a.user_id = u.id) as app_count,
        (SELECT COUNT(*) FROM event_registrations r WHERE r.user_id = u.id AND r.status = 'joined') as event_count,
        (SELECT COUNT(*) FROM bazaar_listings b WHERE b.user_id = u.id) as listing_count
        FROM users u 
        WHERE u.role = 'user'";
$params = [];

if ($search !== '') {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.business_name LIKE ? OR u.phone LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($status !== '') {
    $sql .= " AND u.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY u.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$participants = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Participant Records</h1>
    <p style="color: var(--color-muted);">Browse, filter, edit, and export active member databases and entrepreneur profiles.</p>
  </div>

  <div style="display: flex; gap: 10px;">
    <a href="?export=csv" class="btn btn-outline">
      📥 Export CSV
    </a>
    <a href="<?= BASE_URL ?>/moderator/participant_editor.php" class="btn btn-primary">
      + Add Participant
    </a>
  </div>
</div>

<div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 18px 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
  <form method="GET" action="" style="display: flex; gap: 12px; flex: 1; max-width: 600px;">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name, email, business name..." class="table-search-input" style="flex: 1;">
    
    <select name="status" class="form-select" style="width: auto;">
      <option value="">All Statuses</option>
      <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
      <option value="suspended" <?= ($status === 'suspended') ? 'selected' : '' ?>>Suspended</option>
    </select>

    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    <?php if ($search || $status): ?>
      <a href="<?= BASE_URL ?>/moderator/participants.php" class="btn btn-outline btn-sm">Reset</a>
    <?php endif; ?>
  </form>

  <div class="text-caption">
    Showing <strong><?= count($participants) ?></strong> records
  </div>
</div>

<div class="table-wrapper">
  <table class="data-table" id="participantsTable">
    <thead>
      <tr>
        <th>Participant</th>
        <th>Business Name</th>
        <th>Phone</th>
        <th>Status</th>
        <th>Activity Counts</th>
        <th>Registered</th>
        <th style="text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($participants)): ?>
        <tr>
          <td colspan="7" style="text-align: center; color: var(--color-muted); padding: 48px;">
            No participant records found.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($participants as $p): ?>
          <tr>
            <td>
              <div style="font-weight: 700; color: var(--color-ink);"><?= e($p['name']) ?></div>
              <div class="text-caption"><?= e($p['email']) ?></div>
            </td>
            <td style="font-weight: 600; color: var(--color-primary);"><?= e($p['business_name'] ?: 'None registered') ?></td>
            <td><?= e($p['phone'] ?: '-') ?></td>
            <td><?= status_badge($p['status']) ?></td>
            <td style="font-size: 13px; color: var(--color-muted);">
              📄 <?= (int)$p['app_count'] ?> apps &nbsp;·&nbsp; 🎟️ <?= (int)$p['event_count'] ?> events &nbsp;·&nbsp; 🛍️ <?= (int)$p['listing_count'] ?> items
            </td>
            <td style="color: var(--color-muted); font-size: 13px;">
              <?= date('d M Y', strtotime($p['created_at'])) ?>
            </td>
            <td style="text-align: right;">
              <a href="<?= BASE_URL ?>/moderator/participant_editor.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">
                Edit Record →
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
