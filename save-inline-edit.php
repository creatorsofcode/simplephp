<?php
/**
 * Save Inline Edit
 * AJAX endpoint for saving inline content edits
 */

session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

// Sanitize and validate data
function sanitizeContent($content) {
    if (is_array($content)) {
        return array_map('sanitizeContent', $content);
    }
    
    if (is_string($content)) {
        // Allow HTML but remove dangerous tags
        $content = strip_tags($content, '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><span><div><blockquote><code><pre>');
        
        // Remove javascript: and other dangerous protocols from links
        $content = preg_replace('/javascript:/i', '', $content);
        $content = preg_replace('/on\w+\s*=/i', '', $content); // Remove inline event handlers
        
        return $content;
    }
    
    return $content;
}

$sanitizedData = sanitizeContent($data);

// Save to content.json
$contentFile = __DIR__ . '/data/content.json';

try {
    // Create a backup before saving
    $backupFile = __DIR__ . '/data/content.backup.' . date('Y-m-d_H-i-s') . '.json';
    if (file_exists($contentFile)) {
        copy($contentFile, $backupFile);
        
        // Keep only last 5 backups
        $backups = glob(__DIR__ . '/data/content.backup.*.json');
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
    
    // Write new content
    $jsonContent = json_encode($sanitizedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if (file_put_contents($contentFile, $jsonContent) === false) {
        throw new Exception('Failed to write to content file');
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Content saved successfully',
        'backup' => basename($backupFile)
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
