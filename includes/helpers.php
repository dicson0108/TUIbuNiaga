<?php
/**
 * Shared Helper Functions
 * Taylor's University Event Management & Ibu Niaga Platform
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

// Safe HTML escaping
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Render clean site brand logo & typography (Taylor's Ibu Niaga)
function render_site_brand_html($customClass = '', $subtitle = 'Ibu Niaga'): string {
    $logoUrl = BASE_URL . '/assets/images/logo/logo.png';
    return '<span class="brand-group ' . e($customClass) . '">'
        . '<img src="' . e($logoUrl) . '" alt="Taylor\'s University" class="brand-logo-img">'
        . '<span class="brand-text">'
        . '<span class="brand-title">Taylor\'s</span>'
        . '<span class="brand-subtitle">' . e($subtitle) . '</span>'
        . '</span>'
        . '</span>';
}

// System setting getter
function get_setting($key, $default = null) {
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}


// In-app notification dispatcher
function create_notification($userId, $type, $title, $message, $link = null) {
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$userId, $type, $title, $message, $link]);
    } catch (Exception $e) {
        return false;
    }
}

// Audit logger
function log_audit($action, $entity, $entityId = null, $details = null) {
    try {
        $pdo = get_db();
        $userId = $_SESSION['user']['id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$userId, $action, $entity, $entityId, $details, $ip]);
    } catch (Exception $e) {
        return false;
    }
}

// File upload handler with strict MIME and size verification
function handle_file_upload($fileArray, $targetDir = 'uploads/documents/', $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png']) {
    if (!isset($fileArray['error']) || is_array($fileArray['error'])) {
        return ['success' => false, 'error' => 'Invalid file parameter.'];
    }

    if ($fileArray['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error code: ' . $fileArray['error']];
    }

    $maxBytes = MAX_UPLOAD_SIZE_MB * 1024 * 1024;
    if ($fileArray['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'File exceeds limit of ' . MAX_UPLOAD_SIZE_MB . 'MB.'];
    }

    $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file format. Allowed: ' . implode(', ', $allowedTypes)];
    }

    // Verify MIME
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($fileArray['tmp_name']);
    $allowedMimes = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png'
    ];

    if (!in_array($mime, $allowedMimes)) {
        return ['success' => false, 'error' => 'File content does not match extension.'];
    }

    // Randomized safe name
    $rootUploadDir = dirname(__DIR__) . '/' . trim($targetDir, '/') . '/';
    if (!is_dir($rootUploadDir)) {
        mkdir($rootUploadDir, 0755, true);
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = $rootUploadDir . $fileName;

    if (!move_uploaded_file($fileArray['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Failed to save uploaded file.'];
    }

    return [
        'success'   => true,
        'file_name' => $fileArray['name'],
        'file_path' => trim($targetDir, '/') . '/' . $fileName,
        'file_size' => $fileArray['size'],
        'mime_type' => $mime
    ];
}

// Badge color helper for application statuses
function status_badge($status) {
    switch ($status) {
        case 'Approved':
        case 'published':
        case 'Resolved':
        case 'active':
            return '<span class="badge badge-positive">' . e($status) . '</span>';
        case 'In Review':
        case 'pending':
        case 'In Progress':
            return '<span class="badge badge-attention">' . e($status) . '</span>';
        case 'Needs Documents':
        case 'Draft':
            return '<span class="badge badge-peach">' . e($status) . '</span>';
        case 'Rejected':
        case 'Cancelled':
        case 'Closed':
        case 'suspended':
            return '<span class="badge badge-destructive">' . e($status) . '</span>';
        case 'Submitted':
        default:
            return '<span class="badge badge-neutral">' . e($status) . '</span>';
    }
}
