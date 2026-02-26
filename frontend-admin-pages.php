<?php
/**
 * Frontend Page Management
 * AJAX endpoint for managing pages from the frontend
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
            $data['pages'][$id] = [
                'title' => $title,
                'content' => '<p>New page content. Click to edit.</p>'
            ];
            
            file_put_contents($contentFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
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
            
            unset($data['pages'][$id]);
            
            file_put_contents($contentFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
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
