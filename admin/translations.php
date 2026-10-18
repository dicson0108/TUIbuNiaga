<?php
/**
 * Admin Translation Management
 * Matches Figma: 04 Admin workspace/12 Translation Management.png
 */

$pageTitle = "Translation Management";
require_once __DIR__ . '/../includes/admin_header.php';
$pdo = get_db();

// Handle bulk save of translations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_translations') {
    verify_csrf();
    $values = $_POST['trans'] ?? [];

    $stmtUp = $pdo->prepare("INSERT INTO translations (trans_key, lang, trans_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE trans_value = VALUES(trans_value)");
    foreach ($values as $key => $langs) {
        foreach ($langs as $lang => $text) {
            $stmtUp->execute([$key, $lang, trim($text)]);
        }
    }

    log_audit('UPDATE_TRANSLATIONS', 'translations', null, "Admin updated multi-language dictionary");
    set_flash('success', 'Translations updated and saved successfully.');
    header('Location: ' . BASE_URL . '/admin/translations.php');
    exit;
}

$search = trim($_GET['search'] ?? '');

$stmtAll = $pdo->query("SELECT trans_key, lang, trans_value FROM translations ORDER BY trans_key ASC");
$raw = $stmtAll->fetchAll();

// Group by key
$dictionary = [];
foreach ($raw as $r) {
    $dictionary[$r['trans_key']][$r['lang']] = $r['trans_value'];
}

if ($search !== '') {
    $filtered = [];
    foreach ($dictionary as $k => $vals) {
        if (stripos($k, $search) !== false || stripos($vals['en'] ?? '', $search) !== false || stripos($vals['bm'] ?? '', $search) !== false || stripos($vals['zh'] ?? '', $search) !== false) {
            $filtered[$k] = $vals;
        }
    }
    $dictionary = $filtered;
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 32px; margin-bottom: 4px;">Translation Management</h1>
    <p style="color: var(--color-muted);">Manage multi-language text strings for English (EN), Bahasa Melayu (BM), and 中文 (Chinese).</p>
  </div>
</div>

<div style="background: #fff; border: 1px solid var(--color-line); border-radius: var(--radius-md); padding: 18px 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
  <form method="GET" action="" style="display: flex; gap: 8px; flex: 1; max-width: 480px;">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search translation keys or text..." class="table-search-input" style="flex: 1;">
    <button type="submit" class="btn btn-secondary btn-sm">Search</button>
    <?php if ($search): ?>
      <a href="<?= BASE_URL ?>/admin/translations.php" class="btn btn-outline btn-sm">Reset</a>
    <?php endif; ?>
  </form>

  <div class="text-caption">
    Managing <strong><?= count($dictionary) ?></strong> translation phrases
  </div>
</div>

<form method="POST" action="">
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <input type="hidden" name="action" value="save_translations">

  <div class="table-wrapper" style="margin-bottom: 24px;">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 22%;">Phrase Key</th>
          <th style="width: 26%;">English (EN)</th>
          <th style="width: 26%;">Bahasa Melayu (BM)</th>
          <th style="width: 26%;">中文 (Chinese)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dictionary as $key => $langs): ?>
          <tr>
            <td style="font-family: monospace; font-size: 13px; font-weight: 700; color: var(--color-ink);">
              <?= e($key) ?>
            </td>
            <td>
              <textarea name="trans[<?= e($key) ?>][en]" class="form-input" style="min-height: 52px; font-size: 13px;"><?= e($langs['en'] ?? '') ?></textarea>
            </td>
            <td>
              <textarea name="trans[<?= e($key) ?>][bm]" class="form-input" style="min-height: 52px; font-size: 13px;"><?= e($langs['bm'] ?? '') ?></textarea>
            </td>
            <td>
              <textarea name="trans[<?= e($key) ?>][zh]" class="form-input" style="min-height: 52px; font-size: 13px;"><?= e($langs['zh'] ?? '') ?></textarea>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div style="display: flex; justify-content: flex-end; position: sticky; bottom: 20px; z-index: 10;">
    <button type="submit" class="btn btn-primary btn-lg" style="box-shadow: var(--shadow-card);">
      Save All Translations
    </button>
  </div>
</form>

<?php require_once __DIR__ . '/../includes/workspace_footer.php'; ?>
