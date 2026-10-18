<?php
/**
 * Admin User & Role Management
 * Matches Figma: 04 Admin workspace/02 User & Role Management.png & 04 Delete User.png
 */

$pageTitle = "User & Role Management";
require_once __DIR__ . '/../includes/admin_header.php';
$pdo = get_db();

// Handle deletion or status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf();
    $targetId = (int)$_POST['user_id'];
    $act = $_POST['action'];

    if ($act === 'delete') {
        if ($targetId === $user['id']) {
            set_flash('error', 'Cannot delete your own active administrator account.');
        } else {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$targetId]);
            log_audit('DELETE_USER', 'users', $targetId, "Admin deleted user #{$targetId}");
            set_flash('info', 'User account permanently deleted.');
        }
    } elseif ($act === 'toggle_status') {
        $stmtTarget = $pdo->prepare("SELECT status FROM users WHERE id = ?");
        $stmtTarget->execute([$targetId]);
        $currentStatus = $stmtTarget->fetchColumn();
        $newStatus = ($currentStatus === 'active') ? 'suspended' : 'active';

        $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$newStatus, $targetId]);
        log_audit('TOGGLE_USER_STATUS', 'users', $targetId, "Changed user #{$targetId} status to {$newStatus}");
        set_flash('success', "User account status updated to {$newStatus}.");
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$roleFilter = $_GET['role'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($roleFilter !== '') {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR business_name LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usersList = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">User & Role Management</h1>
    <p style="color: var(--color-muted);">Create university administrative staff, assign moderator permissions, or manage community member accounts.</p>
  </div>

  <a href="<?= BASE_URL ?>/admin/user_editor.php" class="btn btn-primary">
    + Create New User
  </a>
</div>

<!-- Filters -->
<div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 18px 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
  <div style="display: flex; gap: 8px;">
    <a href="?role=" class="btn <?= ($roleFilter === '') ? 'btn-primary' : 'btn-outline' ?> btn-sm">All Roles</a>
    <a href="?role=user" class="btn <?= ($roleFilter === 'user') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Users / Members</a>
    <a href="?role=moderator" class="btn <?= ($roleFilter === 'moderator') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Moderators</a>
    <a href="?role=admin" class="btn <?= ($roleFilter === 'admin') ? 'btn-primary' : 'btn-outline' ?> btn-sm">Administrators</a>
  </div>

  <form method="GET" action="" style="display: flex; gap: 8px;">
    <input type="hidden" name="role" value="<?= e($roleFilter) ?>">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search name or email..." class="table-search-input">
    <button type="submit" class="btn btn-secondary btn-sm">Search</button>
  </form>
</div>

<div class="table-wrapper">
  <table class="data-table">
    <thead>
      <tr>
        <th>User</th>
        <th>Role</th>
        <th>Business Name</th>
        <th>Status</th>
        <th>Email Verified</th>
        <th>Created</th>
        <th style="text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($usersList)): ?>
        <tr>
          <td colspan="7" style="text-align: center; color: var(--color-muted); padding: 48px;">
            No users found matching current filters.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($usersList as $u): ?>
          <tr>
            <td>
              <div style="font-weight: 700; color: var(--color-ink);"><?= e($u['name']) ?></div>
              <div class="text-caption"><?= e($u['email']) ?></div>
            </td>
            <td>
              <?php if ($u['role'] === 'admin'): ?>
                <span class="badge badge-peach">Administrator</span>
              <?php elseif ($u['role'] === 'moderator'): ?>
                <span class="badge badge-positive">Moderator</span>
              <?php else: ?>
                <span class="badge badge-neutral">Member</span>
              <?php endif; ?>
            </td>
            <td style="color: var(--color-primary); font-weight: 500;">
              <?= e($u['business_name'] ?: '-') ?>
            </td>
            <td><?= status_badge($u['status']) ?></td>
            <td style="font-size: 13px;">
              <?= $u['email_verified_at'] ? '<span style="color:#2e7d32;">✓ Verified</span>' : '<span style="color:#e65100;">Pending</span>' ?>
            </td>
            <td style="color: var(--color-muted); font-size: 13px;">
              <?= date('d M Y', strtotime($u['created_at'])) ?>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <a href="<?= BASE_URL ?>/admin/user_editor.php?id=<?= $u['id'] ?>" class="btn btn-secondary btn-sm">
                Edit
              </a>

              <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm">
                  <?= ($u['status'] === 'active') ? 'Suspend' : 'Activate' ?>
                </button>
              </form>

              <?php if ($u['id'] !== $user['id']): ?>
                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to permanently delete user <?= e($u['name']) ?>? All associated applications and records will be deleted.');">
                  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn btn-destructive btn-sm">
                    Delete
                  </button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
