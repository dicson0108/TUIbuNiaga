<?php
/**
 * Moderator Content Editor
 * Matches Figma: 03 Moderator workspace/11 Content Editor.png
 */

require_once __DIR__ . '/../includes/mod_header.php';
$pdo = get_db();

$type = $_GET['type'] ?? 'story';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item = null;
$errors = [];

if ($id > 0) {
    if ($type === 'story') {
        $stmt = $pdo->prepare("SELECT * FROM success_stories WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
    } elseif ($type === 'project') {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
    } elseif ($type === 'help') {
        $stmt = $pdo->prepare("SELECT * FROM help_articles WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
    }
}

// Fetch members for story dropdown
$members = $pdo->query("SELECT id, name, business_name FROM members ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if ($type === 'story') {
        $title = trim($_POST['title'] ?? '');
        $memberId = (int)($_POST['member_id'] ?? 0);
        $quote = trim($_POST['quote'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $pubDate = $_POST['published_at'] ?? date('Y-m-d');
        $photoPath = $item['photo_path'] ?? 'assets/images/22 Sample Photography - Community.png';

        if (!empty($_FILES['photo']['name'])) {
            $res = handle_file_upload($_FILES['photo'], 'uploads/photos/', ['jpg', 'jpeg', 'png']);
            if ($res['success']) $photoPath = $res['file_path'];
        }

        if (!$title || !$quote || !$body) {
            $errors[] = 'Please fill in title, quote, and story body.';
        } else {
            if ($item) {
                $pdo->prepare("UPDATE success_stories SET title = ?, member_id = ?, quote = ?, body = ?, photo_path = ?, published_at = ? WHERE id = ?")
                    ->execute([$title, $memberId, $quote, $body, $photoPath, $pubDate, $id]);
            } else {
                $pdo->prepare("INSERT INTO success_stories (title, member_id, quote, body, photo_path, published_at, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())")
                    ->execute([$title, $memberId, $quote, $body, $photoPath, $pubDate]);
            }
            set_flash('success', 'Success story saved.');
            header('Location: ' . BASE_URL . '/moderator/content.php?tab=story');
            exit;
        }

    } elseif ($type === 'project') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $year = trim($_POST['year'] ?? '2026');
        $summary = trim($_POST['summary'] ?? '');
        $outcomes = trim($_POST['outcomes'] ?? '');
        $posterPath = $item['poster_path'] ?? 'assets/images/20 Sample Photography - Kitchen.png';

        if (!empty($_FILES['photo']['name'])) {
            $res = handle_file_upload($_FILES['photo'], 'uploads/photos/', ['jpg', 'jpeg', 'png']);
            if ($res['success']) $posterPath = $res['file_path'];
        }

        if (!$title || !$summary || !$outcomes) {
            $errors[] = 'Please fill in title, summary, and outcomes.';
        } else {
            if ($item) {
                $pdo->prepare("UPDATE projects SET title = ?, category = ?, year = ?, summary = ?, outcomes = ?, poster_path = ? WHERE id = ?")
                    ->execute([$title, $category, $year, $summary, $outcomes, $posterPath, $id]);
            } else {
                $pdo->prepare("INSERT INTO projects (title, category, year, summary, outcomes, poster_path, gallery_json, created_at) VALUES (?, ?, ?, ?, ?, ?, '[]', NOW())")
                    ->execute([$title, $category, $year, $summary, $outcomes, $posterPath]);
            }
            set_flash('success', 'Project saved.');
            header('Location: ' . BASE_URL . '/moderator/content.php?tab=project');
            exit;
        }

    } elseif ($type === 'help') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $lang = in_array($_POST['language'] ?? '', ['en', 'bm', 'zh']) ? $_POST['language'] : 'en';
        $body = trim($_POST['body'] ?? '');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));

        if (!$title || !$body) {
            $errors[] = 'Title and article body are required.';
        } else {
            if ($item) {
                $pdo->prepare("UPDATE help_articles SET title = ?, slug = ?, category = ?, language = ?, body = ? WHERE id = ?")
                    ->execute([$title, $slug, $category, $lang, $body, $id]);
            } else {
                $pdo->prepare("INSERT INTO help_articles (title, slug, category, language, body, created_at) VALUES (?, ?, ?, ?, ?, NOW())")
                    ->execute([$title, $slug, $category, $lang, $body]);
            }
            set_flash('success', 'Help article saved.');
            header('Location: ' . BASE_URL . '/moderator/content.php?tab=help');
            exit;
        }
    }
}

$pageTitle = ($item ? "Edit " : "Create ") . ucfirst($type);
?>

<div style="max-width: 760px;">
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/moderator/content.php?tab=<?= urlencode($type) ?>" style="font-size: 14px; font-weight: 600; color: var(--color-primary);">← Back to Content Management</a>
  </div>

  <div style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin-bottom: 6px;"><?= $pageTitle ?></h1>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <ul style="margin-left: 20px;">
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="form-card" style="max-width: 100%;">
    <form method="POST" action="" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <?php if ($type === 'story'): ?>
        <div class="form-group">
          <label class="form-label">Story Headline / Title *</label>
          <input type="text" name="title" value="<?= e($item['title'] ?? '') ?>" class="form-input" required placeholder="e.g. From kitchen stove to campus orders">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label class="form-label">Featured Member</label>
            <select name="member_id" class="form-select">
              <option value="">None / General</option>
              <?php foreach ($members as $m): ?>
                <option value="<?= $m['id'] ?>" <?= (($item['member_id'] ?? 0) == $m['id']) ? 'selected' : '' ?>>
                  <?= e($m['name']) ?> (<?= e($m['business_name']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Published Date</label>
            <input type="date" name="published_at" value="<?= e($item['published_at'] ?? date('Y-m-d')) ?>" class="form-input">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Pull Quote *</label>
          <input type="text" name="quote" value="<?= e($item['quote'] ?? '') ?>" class="form-input" required placeholder="e.g. I started with a small idea. Now I believe in what comes next.">
        </div>

        <div class="form-group">
          <label class="form-label">Full Story Body *</label>
          <textarea name="body" class="form-textarea" required style="min-height: 180px;"><?= e($item['body'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Cover Photo (Optional)</label>
          <input type="file" name="photo" class="form-input" accept=".jpg,.jpeg,.png">
        </div>

      <?php elseif ($type === 'project'): ?>
        <div class="form-group">
          <label class="form-label">Project Title *</label>
          <input type="text" name="title" value="<?= e($item['title'] ?? '') ?>" class="form-input" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label class="form-label">Category</label>
            <input type="text" name="category" value="<?= e($item['category'] ?? 'Community Incubation') ?>" class="form-input">
          </div>

          <div class="form-group">
            <label class="form-label">Year</label>
            <input type="text" name="year" value="<?= e($item['year'] ?? '2026') ?>" class="form-input">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Summary Overview *</label>
          <textarea name="summary" class="form-textarea" required><?= e($item['summary'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Measured Outcomes & Impact *</label>
          <textarea name="outcomes" class="form-textarea" required placeholder="e.g. Graduated 25 certified food entrepreneurs; generated RM 45,000 in collective revenue."><?= e($item['outcomes'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Project Photo</label>
          <input type="file" name="photo" class="form-input" accept=".jpg,.jpeg,.png">
        </div>

      <?php elseif ($type === 'help'): ?>
        <div class="form-group">
          <label class="form-label">Article Question / Title *</label>
          <input type="text" name="title" value="<?= e($item['title'] ?? '') ?>" class="form-input" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
              <option value="Program Registration" <?= (($item['category'] ?? '') === 'Program Registration') ? 'selected' : '' ?>>Program Registration</option>
              <option value="Events & Workshops" <?= (($item['category'] ?? '') === 'Events & Workshops') ? 'selected' : '' ?>>Events & Workshops</option>
              <option value="Open Bazaar" <?= (($item['category'] ?? '') === 'Open Bazaar') ? 'selected' : '' ?>>Open Bazaar</option>
              <option value="General" <?= (($item['category'] ?? '') === 'General') ? 'selected' : '' ?>>General</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Language</label>
            <select name="language" class="form-select">
              <option value="en" <?= (($item['language'] ?? '') === 'en') ? 'selected' : '' ?>>English</option>
              <option value="bm" <?= (($item['language'] ?? '') === 'bm') ? 'selected' : '' ?>>Bahasa Melayu</option>
              <option value="zh" <?= (($item['language'] ?? '') === 'zh') ? 'selected' : '' ?>>中文 (Chinese)</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Article Answer Body *</label>
          <textarea name="body" class="form-textarea" required style="min-height: 160px;"><?= e($item['body'] ?? '') ?></textarea>
        </div>
      <?php endif; ?>

      <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--color-line);">
        <a href="<?= BASE_URL ?>/moderator/content.php?tab=<?= urlencode($type) ?>" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Save Content</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
