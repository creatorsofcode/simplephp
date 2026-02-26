<?php
/**
 * Frontend Menu Management
 * AJAX endpoint for managing menus from the frontend
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

if (!isset($data['menu'])) $data['menu'] = [];

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_menus':
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'menus' => $data['menu']]);
            break;
            
        case 'add_menu':
            $id = trim($_POST['id'] ?? '');
            $label = trim($_POST['label'] ?? '');
            $type = $_POST['type'] ?? 'page';
            $url = trim($_POST['url'] ?? '');
            
            if (empty($id) || empty($label)) {
                throw new Exception('ID and label are required');
            }
            
            // Check if ID already exists
            foreach ($data['menu'] as $item) {
                if ($item['id'] === $id) {
                    throw new Exception('Menu item with this ID already exists');
                }
            }
            
            $maxOrder = 0;
            foreach ($data['menu'] as $item) {
                if (isset($item['order']) && $item['order'] > $maxOrder) {
                    $maxOrder = $item['order'];
                }
            }
            
            $newItem = [
                'id' => $id,
                'label' => $label,
                'type' => $type,
                'order' => $maxOrder + 1
            ];
            
            if ($type === 'link' && !empty($url)) {
                $newItem['url'] = $url;
            }
            
            $data['menu'][] = $newItem;
            
            file_put_contents($contentFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Menu item added', 'menu' => $newItem]);
            break;
            
        case 'update_menu':
            $id = trim($_POST['id'] ?? '');
            $label = trim($_POST['label'] ?? '');
            $type = $_POST['type'] ?? 'page';
            $url = trim($_POST['url'] ?? '');
            
            if (empty($id)) {
                throw new Exception('ID is required');
            }
            
            $found = false;
            foreach ($data['menu'] as &$item) {
                if ($item['id'] === $id) {
                    $item['label'] = $label;
                    $item['type'] = $type;
                    if ($type === 'link') {
                        $item['url'] = $url;
                    } else {
                        unset($item['url']);
                    }
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                throw new Exception('Menu item not found');
            }
            
            file_put_contents($contentFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Menu item updated']);
            break;
            
        case 'delete_menu':
            $id = trim($_POST['id'] ?? '');
            
            if (empty($id)) {
                throw new Exception('ID is required');
            }
            
            $data['menu'] = array_values(array_filter($data['menu'], function($item) use ($id) {
                return $item['id'] !== $id;
            }));
            
            file_put_contents($contentFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Menu item deleted']);
            break;
            
        case 'reorder_menus':
            $order = json_decode($_POST['order'] ?? '[]', true);
            
            if (!is_array($order)) {
                throw new Exception('Invalid order data');
            }
            
            // Update order for each menu item
            foreach ($data['menu'] as &$item) {
                $key = array_search($item['id'], $order);
                if ($key !== false) {
                    $item['order'] = $key + 1;
                }
            }
            
            // Sort by order
            usort($data['menu'], function($a, $b) {
                return ($a['order'] ?? 999) - ($b['order'] ?? 999);
            });
            
            file_put_contents($contentFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Menu order updated']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
