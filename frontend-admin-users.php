<?php
/**
 * Frontend User Management
 * AJAX endpoint for managing users from the frontend
 */

session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$usersFile = __DIR__ . '/data/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

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
            
            if (strlen($password) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            if (isset($users[$username])) {
                throw new Exception('Username already exists');
            }
            
            $users[$username] = password_hash($password, PASSWORD_DEFAULT);
            
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            
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
            
            unset($users[$username]);
            
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            break;
            
        case 'change_password':
            $username = trim($_POST['username'] ?? '');
            $newPassword = trim($_POST['password'] ?? '');
            
            if (empty($username) || empty($newPassword)) {
                throw new Exception('Username and new password are required');
            }
            
            if (strlen($newPassword) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            if (!isset($users[$username])) {
                throw new Exception('User not found');
            }
            
            $users[$username] = password_hash($newPassword, PASSWORD_DEFAULT);
            
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
