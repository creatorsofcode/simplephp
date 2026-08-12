<?php
/**
 * Frontend User Management
 * AJAX endpoint for managing users from the frontend
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

$usersFile = SIMPLEPHP_DATA_DIR . '/users.json';
$users = simplephp_json_read($usersFile, []);

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_users':
            // Return usernames only (no passwords)
            $userList = array_keys($users);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'users' => $userList]);
            break;
            
        case 'add_user':
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            
            if (empty($username) || empty($password)) {
                throw new Exception('Username and password are required');
            }

            if (strlen($username) > 60) {
                throw new Exception('Username must be 60 characters or fewer');
            }

            if (strlen($password) < 6 || strlen($password) > 512) {
                throw new Exception('Password must be 6-512 characters');
            }

            if (isset($users[$username])) {
                throw new Exception('Username already exists');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            simplephp_json_update($usersFile, function ($data) use ($username, $hash) {
                $data[$username] = $hash;
                return $data;
            }, []);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'User added successfully']);
            break;
            
        case 'delete_user':
            $username = trim($_POST['username'] ?? '');
            $currentUser = $_SESSION['admin_username'] ?? 'admin';
            
            if (empty($username)) {
                throw new Exception('Username is required');
            }
            
            if ($username === $currentUser) {
                throw new Exception('Cannot delete your own account');
            }
            
            if (!isset($users[$username])) {
                throw new Exception('User not found');
            }

            simplephp_json_update($usersFile, function ($data) use ($username) {
                unset($data[$username]);
                return $data;
            }, []);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            break;
            
        case 'change_password':
            $username = trim($_POST['username'] ?? '');
            $newPassword = trim($_POST['password'] ?? '');
            
            if (empty($username) || empty($newPassword)) {
                throw new Exception('Username and new password are required');
            }
            
            if (strlen($newPassword) < 6 || strlen($newPassword) > 512) {
                throw new Exception('Password must be 6-512 characters');
            }

            if (!isset($users[$username])) {
                throw new Exception('User not found');
            }

            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            simplephp_json_update($usersFile, function ($data) use ($username, $newHash) {
                $data[$username] = $newHash;
                return $data;
            }, []);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => simplephp_safe_error($e, 'frontend-admin-users')]);
}
