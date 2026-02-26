<?php
/**
 * Users Management Page - Example
 * Note: Replace this file with actual users management logic
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

// Page configuration
$page_title = 'Users';
$page_header = [
    'title' => 'Users Management',
    'subtitle' => 'Manage system users and permissions',
    'action' => [
        'text' => 'Add User',
        'url' => '/admin/users.php?action=new',
        'icon' => 'plus'
    ]
];

$breadcrumb = [
    ['text' => 'Management', 'url' => '#', 'active' => false],
    ['text' => 'Users', 'url' => '#', 'active' => true]
];

ob_start();
?>

<div class="container-fluid">
    <!-- Users Table -->
    <div class="row">
        <div class="col-lg-12 mb-24">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">All Users</h5>
                    <p class="text-muted mt-2">List of all registered users in the system</p>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#1001</td>
                                    <td><strong>Admin</strong></td>
                                    <td>admin@example.com</td>
                                    <td><span class="badge badge-primary">Administrator</span></td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>Feb 24, 2026</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i data-feather="edit-2" style="width: 16px; height: 16px;"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user?')">
                                            <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#1002</td>
                                    <td><strong>John Doe</strong></td>
                                    <td>john@example.com</td>
                                    <td><span class="badge badge-info">Editor</span></td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>Feb 20, 2026</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i data-feather="edit-2" style="width: 16px; height: 16px;"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger">
                                            <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#1003</td>
                                    <td><strong>Jane Smith</strong></td>
                                    <td>jane@example.com</td>
                                    <td><span class="badge badge-warning">Contributor</span></td>
                                    <td><span class="badge badge-danger">Inactive</span></td>
                                    <td>Feb 15, 2026</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i data-feather="edit-2" style="width: 16px; height: 16px;"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger">
                                            <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Showing 3 of 12 users</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add User Form (Example) -->
    <div class="row mt-24">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Add New User</h5>
                    <p class="text-muted mt-2">Create a new user account</p>
                </div>
                <div class="card-body">
                    <form class="form-default" method="POST" action="/admin/users.php" data-validate>
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="new_username" class="form-label required">Username</label>
                            <input type="text" class="form-control" id="new_username" name="username" placeholder="Enter username" data-required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_email" class="form-label required">Email Address</label>
                            <input type="email" class="form-control" id="new_email" name="email" placeholder="Enter email address" data-required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password" class="form-label required">Password</label>
                            <input type="password" class="form-control" id="new_password" name="password" placeholder="Enter password" data-required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_role" class="form-label required">Role</label>
                            <select class="form-select" id="new_role" name="role" data-required>
                                <option selected>Select a role</option>
                                <option value="admin">Administrator</option>
                                <option value="editor">Editor</option>
                                <option value="contributor">Contributor</option>
                            </select>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active_status" name="active" checked>
                            <label class="form-check-label" for="active_status">
                                User is active
                            </label>
                        </div>
                        
                        <div class="btn-group mt-20">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="plus"></i> Create User
                            </button>
                            <a href="/admin/users.php" class="btn btn-outline-secondary">
                                <i data-feather="x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">User Roles & Permissions</h5>
                    <p class="text-muted mt-2">Role-based access control</p>
                </div>
                <div class="card-body">
                    <div class="role-info mb-20">
                        <h6 style="font-weight: 600; color: var(--text-primary); margin-bottom: 10px;">
                            <span class="badge badge-primary">Administrator</span>
                        </h6>
                        <p class="text-muted" style="font-size: 13px; margin: 0;">Full access to all system features and settings.</p>
                    </div>
                    
                    <div class="role-info mb-20">
                        <h6 style="font-weight: 600; color: var(--text-primary); margin-bottom: 10px;">
                            <span class="badge badge-info">Editor</span>
                        </h6>
                        <p class="text-muted" style="font-size: 13px; margin: 0;">Can create, edit, and delete content. Limited settings access.</p>
                    </div>
                    
                    <div class="role-info">
                        <h6 style="font-weight: 600; color: var(--text-primary); margin-bottom: 10px;">
                            <span class="badge badge-warning">Contributor</span>
                        </h6>
                        <p class="text-muted" style="font-size: 13px; margin: 0;">Can only create and edit their own content.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .btn-sm {
        padding: 6px 10px !important;
        font-size: 12px !important;
        margin-right: 5px !important;
    }
</style>
<?php
$page_content = ob_get_clean();
include __DIR__ . '/theme/base-layout.php';
?>
