<?php
/**
 * Frontend Appearance Settings
 * AJAX endpoint for managing appearance settings from the frontend
 */

require_once __DIR__ . '/includes/Security.php';
simplephp_secure_session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

simplephp_require_csrf_json();

$contentFile = SIMPLEPHP_DATA_DIR . '/content.json';
$data = simplephp_json_read($contentFile, ['menu' => [], 'pages' => []]);

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
            simplephp_json_update($contentFile, function ($d) use ($enabled) {
                $d['design']['search_enabled'] = $enabled;
                return $d;
            }, ['menu' => [], 'pages' => []]);

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
