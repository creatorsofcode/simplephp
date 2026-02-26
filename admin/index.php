<?php
/**
 * Admin Dashboard - Main Page
 * Professional Admin Interface with New Theme
 */

// Start session
if (!isset($_SESSION)) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$usersFile = __DIR__ . '/../data/users.json';
$contentFile = __DIR__ . '/../data/content.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$content = file_exists($contentFile) ? json_decode(file_get_contents($contentFile), true) : ['menu' => [], 'pages' => []];
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
    $action = $_POST['action'] ?? '';

    if ($action === 'add_user') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($username) && !empty($password) && strlen($username) >= 3 && strlen($password) >= 5) {
            if (!isset($users[$username])) {
                $users[$username] = password_hash($password, PASSWORD_DEFAULT);
                file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
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
            unset($users[$username]);
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
            $message = "User '$username' deleted.";
        } else {
            $error = 'Cannot delete current user.';
        }
    }

    if ($action === 'change_password') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($password) && strlen($password) >= 5 && isset($users[$username])) {
            $users[$username] = password_hash($password, PASSWORD_DEFAULT);
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
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
            foreach ($content['menu'] as &$item) {
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

            if ($found) {
                file_put_contents($contentFile, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
                $message = "Menu item '$menuLabel' updated successfully!";
            } else {
                $error = 'Menu item not found.';
            }
        } else {
            $error = 'Menu ID and label are required.';
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
