<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role('admin');

$page_title = 'System Audit Logs';

$search = trim($_GET['search'] ?? '');
$action_filter = trim($_GET['action_filter'] ?? '');
$entity_filter = trim($_GET['entity_filter'] ?? '');

$sql = "SELECT a.*, u.name as full_name, u.email 
        FROM audit_logs a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (a.action LIKE ? OR a.entity LIKE ? OR a.details LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR a.ip_address LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like, $like, $like]);
}
if ($action_filter !== '') {
    $sql .= " AND a.action = ?";
    $params[] = $action_filter;
}
if ($entity_filter !== '') {
    $sql .= " AND a.entity = ?";
    $params[] = $entity_filter;
}

$sql .= " ORDER BY a.created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="audit_logs_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Timestamp', 'User', 'Email', 'Action', 'Entity Type', 'Entity ID', 'Details', 'IP Address']);
    foreach ($logs as $l) {
        fputcsv($out, [
            $l['id'],
            $l['created_at'],
            $l['full_name'] ?? 'System',
            $l['email'] ?? 'N/A',
            $l['action'],
            $l['entity'] ?? '',
            $l['entity_id'] ?? '',
            $l['details'] ?? '',
            $l['ip_address'] ?? ''
        ]);
    }
    exit;
}


require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-between align-center mb-3">
  <div>
    <h1 style="margin:0 0 4px;font-size:24px;">System Audit Logs</h1>
    <p class="text-muted" style="margin:0;font-size:14px;">Tamper-evident trail of user actions, moderation events, and configuration updates.</p>
  </div>
  <div>
    <a href="audit_logs.php?export=csv<?= $search ? '&search='.urlencode($search) : '' ?><?= $action_filter ? '&action_filter='.urlencode($action_filter) : '' ?><?= $entity_filter ? '&entity_filter='.urlencode($entity_filter) : '' ?>" class="btn btn-outline btn-sm">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Export CSV
    </a>
  </div>
</div>

<div class="card p-3 mb-3">
  <form method="GET" class="d-flex gap-2" style="flex-wrap:wrap;">
    <div style="flex:1;min-width:220px;">
      <input type="text" name="search" class="form-control" placeholder="Search by user, action, details, or IP..." value="<?= e($search) ?>">
    </div>
    <div style="width:160px;">
      <select name="action_filter" class="form-control">
        <option value="">All Actions</option>
        <option value="login" <?= $action_filter==='login'?'selected':'' ?>>login</option>
        <option value="logout" <?= $action_filter==='logout'?'selected':'' ?>>logout</option>
        <option value="create" <?= $action_filter==='create'?'selected':'' ?>>create</option>
        <option value="update" <?= $action_filter==='update'?'selected':'' ?>>update</option>
        <option value="delete" <?= $action_filter==='delete'?'selected':'' ?>>delete</option>
        <option value="approve" <?= $action_filter==='approve'?'selected':'' ?>>approve</option>
        <option value="reject" <?= $action_filter==='reject'?'selected':'' ?>>reject</option>
        <option value="status_change" <?= $action_filter==='status_change'?'selected':'' ?>>status_change</option>
      </select>
    </div>
    <div style="width:160px;">
      <select name="entity_filter" class="form-control">
        <option value="">All Entities</option>
        <option value="user" <?= $entity_filter==='user'?'selected':'' ?>>user</option>
        <option value="application" <?= $entity_filter==='application'?'selected':'' ?>>application</option>
        <option value="bazaar_listing" <?= $entity_filter==='bazaar_listing'?'selected':'' ?>>bazaar_listing</option>
        <option value="event" <?= $entity_filter==='event'?'selected':'' ?>>event</option>
        <option value="ticket" <?= $entity_filter==='ticket'?'selected':'' ?>>ticket</option>
        <option value="system_setting" <?= $entity_filter==='system_setting'?'selected':'' ?>>system_setting</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <?php if ($search || $action_filter || $entity_filter): ?>
      <a href="audit_logs.php" class="btn btn-outline btn-sm">Clear</a>
    <?php endif; ?>
  </form>
</div>

<div class="card p-0">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th style="width:160px;">Timestamp</th>
          <th>Actor / User</th>
          <th style="width:110px;">Action</th>
          <th style="width:130px;">Entity</th>
          <th>Details</th>
          <th style="width:110px;">IP Address</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
          <tr><td colspan="6" class="text-center text-muted p-4">No audit log records match filter criteria.</td></tr>
        <?php else: ?>
          <?php foreach ($logs as $l): ?>
            <tr>
              <td style="font-size:12px;color:var(--color-ink-muted);white-space:nowrap;">
                <?= date('d M Y, H:i:s', strtotime($l['created_at'])) ?>
              </td>
              <td>
                <?php if ($l['user_id']): ?>
                  <div style="font-weight:600;font-size:13px;"><?= e($l['full_name'] ?? 'User #'.$l['user_id']) ?></div>
                  <div style="font-size:11px;color:var(--color-ink-muted);"><?= e($l['email'] ?? '') ?></div>
                <?php else: ?>
                  <span class="badge" style="background:#f2f2f2;color:#666;">System</span>
                <?php endif; ?>
              </td>
              <td>
                <?php
                  $color = match($l['action']) {
                    'create', 'approve' => '#2e7d32',
                    'delete', 'reject' => '#d71920',
                    'login' => '#1565c0',
                    'update', 'status_change' => '#e65100',
                    default => '#555'
                  };
                ?>
                <span class="badge" style="background:<?= $color ?>15;color:<?= $color ?>;border:1px solid <?= $color ?>30;font-size:11px;">
                  <?= e($l['action']) ?>
                </span>
              </td>
              <td style="font-size:12px;">
                <strong><?= e($l['entity']) ?></strong>
                <?php if ($l['entity_id']): ?>
                  <span class="text-muted">#<?= (int)$l['entity_id'] ?></span>
                <?php endif; ?>
              </td>

              <td style="font-size:12px;color:var(--color-ink);max-width:320px;word-break:break-word;">
                <?= e($l['details'] ?? '-') ?>
              </td>
              <td style="font-size:12px;color:var(--color-ink-muted);font-family:monospace;">
                <?= e($l['ip_address'] ?? '-') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
