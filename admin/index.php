<?php
/**
 * Admin Dashboard - Main Page
 * Professional Admin Interface with New Theme
 */

require_once __DIR__ . '/../includes/Security.php';
simplephp_secure_session_start();

// Logout handler - allowed even if the account is mid forced-password-change.
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

simplephp_require_admin_login();

// Demo protection: revert content.json if a scheduled auto-reset is due
require_once __DIR__ . '/../includes/ContentReset.php';
simplephp_maybe_auto_reset_content();

$usersFile = SIMPLEPHP_DATA_DIR . '/users.json';
$contentFile = SIMPLEPHP_DATA_DIR . '/content.json';
$users = simplephp_json_read($usersFile, []);
$content = simplephp_json_read($contentFile, ['menu' => [], 'pages' => []]);
$message = null;
$error = null;

if (!isset($content['menu'])) {
    $content['menu'] = [];
}
if (!isset($content['pages'])) {
    $content['pages'] = [];
}

// Migrate old username
if (isset($_SESSION['admin_username']) && $_SESSION['admin_username'] === 'birgit' && isset($users['admin'])) {
    $_SESSION['admin_username'] = 'admin';
}

// Handle user admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!simplephp_csrf_valid()) {
        $error = 'Your session expired. Please reload the page and try again.';
    } else {
    try {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_user') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($username) && !empty($password) && strlen($username) >= 3 && strlen($password) >= 5) {
            if (!isset($users[$username])) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $users = simplephp_json_update($usersFile, function ($data) use ($username, $hash) {
                    $data[$username] = $hash;
                    return $data;
                }, []);
                $message = "User '$username' added successfully!";
            } else {
                $error = "User '$username' already exists.";
            }
        } else {
            $error = 'Username must be at least 3 characters, password at least 5 characters.';
        }
    }

    if ($action === 'delete_user') {
        $username = $_POST['username'] ?? '';
        if ($username !== $_SESSION['admin_username'] && isset($users[$username])) {
            $users = simplephp_json_update($usersFile, function ($data) use ($username) {
                unset($data[$username]);
                return $data;
            }, []);
            $message = "User '$username' deleted.";
        } else {
            $error = 'Cannot delete current user.';
        }
    }

    if ($action === 'change_password') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($password) && strlen($password) >= 5 && isset($users[$username])) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $users = simplephp_json_update($usersFile, function ($data) use ($username, $hash) {
                $data[$username] = $hash;
                return $data;
            }, []);
            $message = "Password for '$username' updated.";
        } else {
            $error = 'Password must be at least 5 characters.';
        }
    }

    if ($action === 'edit_menu') {
        $menuId = $_POST['menu_id'] ?? '';
        $menuLabel = $_POST['menu_label'] ?? '';
        $menuType = $_POST['menu_type'] ?? 'page';
        $menuOrder = (int)($_POST['menu_order'] ?? 1);
        $menuUrl = $_POST['menu_url'] ?? '';

        if (!empty($menuId) && !empty($menuLabel)) {
            $found = false;
            $content = simplephp_json_update($contentFile, function ($data) use ($menuId, $menuLabel, $menuType, $menuOrder, $menuUrl, &$found) {
                foreach ($data['menu'] ?? [] as &$item) {
                    if (($item['id'] ?? '') === $menuId) {
                        $item['label'] = $menuLabel;
                        $item['type'] = $menuType;
                        $item['order'] = $menuOrder;
                        if ($menuType === 'link') {
                            $item['url'] = $menuUrl;
                        }
                        $found = true;
                        break;
                    }
                }
                unset($item);
                return $data;
            }, ['menu' => [], 'pages' => []]);

            if ($found) {
                $message = "Menu item '$menuLabel' updated successfully!";
            } else {
                $error = 'Menu item not found.';
            }
        } else {
            $error = 'Menu ID and label are required.';
        }
    }
    } catch (RuntimeException $e) {
        $error = 'Failed to save changes. Please try again.';
    }
    }
}

$page_title = 'Dashboard';
$page_header = [
    'title' => 'Dashboard',
    'subtitle' => 'Manage menus, pages, and users'
];

$breadcrumb = [
    ['text' => 'Admin', 'url' => '#', 'active' => false],
    ['text' => 'Dashboard', 'url' => '#', 'active' => true]
];

$content_file = __DIR__ . '/pages/dashboard-content.php';

include __DIR__ . '/theme/base-layout.php';
