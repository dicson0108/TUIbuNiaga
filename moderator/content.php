<?php
/**
 * Moderator Content Management
 * Matches Figma: 03 Moderator workspace/10 Content Management.png
 */

$pageTitle = "Content Management";
require_once __DIR__ . '/../includes/mod_header.php';
$pdo = get_db();

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    verify_csrf();
    $type = $_POST['type'] ?? '';
    $itemId = (int)$_POST['item_id'];

    if ($type === 'story') {
        $pdo->prepare("DELETE FROM success_stories WHERE id = ?")->execute([$itemId]);
        set_flash('info', 'Success story deleted.');
    } elseif ($type === 'project') {
        $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$itemId]);
        set_flash('info', 'Project deleted.');
    } elseif ($type === 'help') {
        $pdo->prepare("DELETE FROM help_articles WHERE id = ?")->execute([$itemId]);
        set_flash('info', 'Help article deleted.');
    }
    header('Location: ' . BASE_URL . '/moderator/content.php?tab=' . urlencode($type));
    exit;
}

$tab = $_GET['tab'] ?? 'story';

$stories = $pdo->query("SELECT s.*, m.name as member_name, m.business_name FROM success_stories s LEFT JOIN members m ON s.member_id = m.id ORDER BY s.id DESC")->fetchAll();
$projects = $pdo->query("SELECT * FROM projects ORDER BY year DESC, id DESC")->fetchAll();
$articles = $pdo->query("SELECT * FROM help_articles ORDER BY sort_order ASC, id ASC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Content Management</h1>
    <p style="color: var(--color-muted);">Publish and edit Success Stories, Past Impact Projects, and Help Center articles.</p>
  </div>

  <div style="display: flex; gap: 10px;">
    <a href="<?= BASE_URL ?>/moderator/content_editor.php?type=<?= urlencode($tab) ?>" class="btn btn-primary">
      + Create New <?= ucfirst($tab) ?>
    </a>
  </div>
</div>

<!-- Tabs -->
<div style="display: flex; gap: 12px; margin-bottom: 28px; border-bottom: 1px solid var(--color-line); padding-bottom: 12px;">
  <a href="?tab=story" class="btn <?= ($tab === 'story') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Success Stories (<?= count($stories) ?>)
  </a>
  <a href="?tab=project" class="btn <?= ($tab === 'project') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Past Projects (<?= count($projects) ?>)
  </a>
  <a href="?tab=help" class="btn <?= ($tab === 'help') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Help Articles (<?= count($articles) ?>)
  </a>
</div>

<?php if ($tab === 'story'): ?>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>Story Title</th>
          <th>Featured Member</th>
          <th>Quote</th>
          <th>Published Date</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($stories as $s): ?>
          <tr>
            <td style="font-weight: 700; color: var(--color-ink);"><?= e($s['title']) ?></td>
            <td style="color: var(--color-primary); font-weight: 600;"><?= e($s['member_name']) ?> (<?= e($s['business_name']) ?>)</td>
            <td style="font-style: italic; max-width: 280px;">“<?= e(substr($s['quote'], 0, 80)) ?>...”</td>
            <td style="color: var(--color-muted); font-size: 13px;"><?= date('d M Y', strtotime($s['published_at'])) ?></td>
            <td style="text-align: right;">
              <a href="<?= BASE_URL ?>/moderator/content_editor.php?type=story&id=<?= $s['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
              <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this story?');">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="type" value="story">
                <input type="hidden" name="item_id" value="<?= $s['id'] ?>">
                <button type="submit" class="btn btn-destructive btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'project'): ?>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>Project Title</th>
          <th>Category</th>
          <th>Year</th>
          <th>Outcomes</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $pj): ?>
          <tr>
            <td style="font-weight: 700; color: var(--color-ink);"><?= e($pj['title']) ?></td>
            <td><span class="badge badge-neutral"><?= e($pj['category']) ?></span></td>
            <td><?= e($pj['year']) ?></td>
            <td style="max-width: 320px; font-size: 13px;"><?= e($pj['outcomes']) ?></td>
            <td style="text-align: right;">
              <a href="<?= BASE_URL ?>/moderator/content_editor.php?type=project&id=<?= $pj['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
              <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this project?');">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="type" value="project">
                <input type="hidden" name="item_id" value="<?= $pj['id'] ?>">
                <button type="submit" class="btn btn-destructive btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php else: ?>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>Article Title</th>
          <th>Category</th>
          <th>Language</th>
          <th>Sort Order</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($articles as $art): ?>
          <tr>
            <td style="font-weight: 700; color: var(--color-ink);"><?= e($art['title']) ?></td>
            <td><?= e($art['category']) ?></td>
            <td><span class="badge badge-neutral"><?= strtoupper(e($art['language'])) ?></span></td>
            <td><?= (int)$art['sort_order'] ?></td>
            <td style="text-align: right;">
              <a href="<?= BASE_URL ?>/moderator/content_editor.php?type=help&id=<?= $art['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
              <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this article?');">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="type" value="help">
                <input type="hidden" name="item_id" value="<?= $art['id'] ?>">
                <button type="submit" class="btn btn-destructive btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
