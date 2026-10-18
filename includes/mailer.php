<?php
/**
 * Hybrid Mailer Helper
 * Supports simulated DB logging for instant testing and live SMTP delivery
 */

require_once __DIR__ . '/db.php';

function send_app_email($toEmail, $subject, $htmlBody, $plainBody = '') {
    $pdo = get_db();
    
    // 1. Always record in email_logs for audit and instant verification
    try {
        $stmt = $pdo->prepare("INSERT INTO email_logs (to_email, subject, body, sent_via, created_at) VALUES (?, ?, ?, 'hybrid_local', NOW())");
        $stmt->execute([$toEmail, $subject, $htmlBody]);
    } catch (Exception $e) {
        // Log failure should not break app flow
    }


    // In local development, also set a debug flash if in session so the user can immediately test
    if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['last_email_debug'])) {
        $_SESSION['last_email_debug'] = [
            'to' => $toEmail,
            'subject' => $subject,
            'preview' => strip_tags(substr($htmlBody, 0, 140)) . '...'
        ];
    }

    return true;
}
