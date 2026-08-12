<?php
/**
 * Frontend Menu Management
 * AJAX endpoint for managing menus from the frontend
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
            
            simplephp_json_update($contentFile, function ($d) use ($newItem) {
                $d['menu'][] = $newItem;
                return $d;
            }, ['menu' => [], 'pages' => []]);

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
            simplephp_json_update($contentFile, function ($d) use ($id, $label, $type, $url, &$found) {
                foreach ($d['menu'] as &$item) {
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
                unset($item);
                return $d;
            }, ['menu' => [], 'pages' => []]);

            if (!$found) {
                throw new Exception('Menu item not found');
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Menu item updated']);
            break;
            
        case 'delete_menu':
            $id = trim($_POST['id'] ?? '');
            
            if (empty($id)) {
                throw new Exception('ID is required');
            }
            
            simplephp_json_update($contentFile, function ($d) use ($id) {
                $d['menu'] = array_values(array_filter($d['menu'], function ($item) use ($id) {
                    return $item['id'] !== $id;
                }));
                return $d;
            }, ['menu' => [], 'pages' => []]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Menu item deleted']);
            break;
            
        case 'reorder_menus':
            $order = json_decode($_POST['order'] ?? '[]', true);
            
            if (!is_array($order)) {
                throw new Exception('Invalid order data');
            }
            
            simplephp_json_update($contentFile, function ($d) use ($order) {
                // Update order for each menu item
                foreach ($d['menu'] as &$item) {
                    $key = array_search($item['id'], $order);
                    if ($key !== false) {
                        $item['order'] = $key + 1;
                    }
                }
                unset($item);

                // Sort by order
                usort($d['menu'], function ($a, $b) {
                    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
                });
                return $d;
            }, ['menu' => [], 'pages' => []]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Menu order updated']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => simplephp_safe_error($e, 'frontend-admin-menus')]);
}
