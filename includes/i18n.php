<?php
/**
 * Internationalization (i18n) Helper
 * Supports English (en), Bahasa Melayu (bm), and 中文 (zh)
 */

require_once __DIR__ . '/db.php';

// Handle language switch via query param (?lang=en|bm|zh)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bm', 'zh'])) {
    $_SESSION['lang'] = $_GET['lang'];
    // Update user profile preference if logged in
    if (!empty($_SESSION['user']['id'])) {
        try {
            $pdo = get_db();
            $stmt = $pdo->prepare("UPDATE users SET language = ? WHERE id = ?");
            $stmt->execute([$_SESSION['lang'], $_SESSION['user']['id']]);
            $_SESSION['user']['language'] = $_SESSION['lang'];
        } catch (Exception $e) {
            // ignore
        }
    }
}

// Current language code
function current_lang() {
    return $_SESSION['lang'] ?? $_SESSION['user']['language'] ?? 'en';
}

// Translation helper: looks up cached translations from DB, falls back to default
function __($key, $default = null) {
    static $translations = null;
    $lang = current_lang();

    if ($translations === null) {
        $translations = [];
        try {
            $pdo = get_db();
            $stmt = $pdo->query("SELECT trans_key, lang, trans_value FROM translations");
            while ($row = $stmt->fetch()) {
                $translations[$row['lang']][$row['trans_key']] = $row['trans_value'];
            }
        } catch (Exception $e) {
            // DB not yet ready or empty
        }
    }

    if (isset($translations[$lang][$key])) {
        return htmlspecialchars($translations[$lang][$key], ENT_QUOTES, 'UTF-8');
    }
    // Fallback to English if available
    if ($lang !== 'en' && isset($translations['en'][$key])) {
        return htmlspecialchars($translations['en'][$key], ENT_QUOTES, 'UTF-8');
    }

    return htmlspecialchars($default !== null ? $default : $key, ENT_QUOTES, 'UTF-8');
}
