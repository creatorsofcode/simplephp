<?php
/**
 * Frontend Page Management
 * AJAX endpoint for managing pages from the frontend
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

if (!isset($data['pages'])) $data['pages'] = [];

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_pages':
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'pages' => $data['pages']]);
            break;
            
        case 'add_page':
            $id = trim($_POST['id'] ?? '');
            $title = trim($_POST['title'] ?? '');
            
            if (empty($id) || empty($title)) {
                throw new Exception('Page ID and title are required');
            }
            
            // Check if page already exists
            if (isset($data['pages'][$id])) {
                throw new Exception('Page with this ID already exists');
            }
            
            // Create new page
            $newPage = [
                'title' => $title,
                'content' => '<p>New page content. Click to edit.</p>'
            ];
            $data = simplephp_json_update($contentFile, function ($d) use ($id, $newPage) {
                $d['pages'][$id] = $newPage;
                return $d;
            }, ['menu' => [], 'pages' => []]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Page created', 'page' => $data['pages'][$id]]);
            break;
            
        case 'delete_page':
            $id = trim($_POST['id'] ?? '');
            
            if (empty($id)) {
                throw new Exception('Page ID is required');
            }
            
            // Don't allow deleting core pages
            $protectedPages = ['home', 'about', 'services', 'contact'];
            if (in_array($id, $protectedPages)) {
                throw new Exception('Cannot delete protected page: ' . $id);
            }
            
            if (!isset($data['pages'][$id])) {
                throw new Exception('Page not found');
            }
            
            simplephp_json_update($contentFile, function ($d) use ($id) {
                unset($d['pages'][$id]);
                return $d;
            }, ['menu' => [], 'pages' => []]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Page deleted']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
