<?php
/**
 * Frontend Appearance Settings
 * AJAX endpoint for managing appearance settings from the frontend
 */

session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$contentFile = __DIR__ . '/data/content.json';
$data = json_decode(file_get_contents($contentFile), true);

// Ensure design section exists
if (!isset($data['design'])) {
    $data['design'] = [
        'search_enabled' => true,
        'template_html' => '',
        'custom_css' => '',
        'custom_js' => ''
    ];
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_settings':
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'design' => $data['design']]);
            break;
            
        case 'toggle_search':
            $enabled = isset($_POST['enabled']) ? (bool)$_POST['enabled'] : true;
            $data['design']['search_enabled'] = $enabled;
            
            file_put_contents($contentFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Search setting updated', 'search_enabled' => $enabled]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
