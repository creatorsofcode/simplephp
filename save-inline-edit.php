<?php
/**
 * Save Inline Edit
 * AJAX endpoint for saving inline content edits
 */

require_once __DIR__ . '/includes/Security.php';
simplephp_secure_session_start();

require_once __DIR__ . '/includes/ContentReset.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

simplephp_require_csrf_json();

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

// Sanitize and validate data (allowlist HTML sanitizer, see includes/Security.php)
function sanitizeContent($content) {
    if (is_array($content)) {
        return array_map('sanitizeContent', $content);
    }

    if (is_string($content)) {
        return simplephp_sanitize_html($content);
    }

    return $content;
}

$sanitizedData = sanitizeContent($data);

// Save to content.json
$contentFile = SIMPLEPHP_DATA_DIR . '/content.json';
$backupDir = SIMPLEPHP_DATA_DIR . '/backups';

try {
    // Create a backup before saving
    $backupFile = $backupDir . '/content.backup.' . date('Y-m-d_H-i-s') . '.json';
    if (file_exists($contentFile)) {
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0750, true);
        }
        copy($contentFile, $backupFile);

        // Keep only last 5 backups
        $backups = glob($backupDir . '/content.backup.*.json');
        if (count($backups) > 5) {
            usort($backups, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            // Delete oldest backups
            for ($i = 0; $i < count($backups) - 5; $i++) {
                unlink($backups[$i]);
            }
        }
    }

    // Write new content atomically (temp file + rename)
    if (!simplephp_json_write($contentFile, $sanitizedData)) {
        throw new Exception('Failed to write to content file');
    }

    // Demo protection: auto-revert this edit back to the default content
    // 30 seconds after the last save (checked lazily on the next page load).
    simplephp_schedule_content_reset(30);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Content saved successfully',
        'backup' => basename($backupFile),
        'reset_in_seconds' => 30
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
