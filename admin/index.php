<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    header('Location: login.php');
    exit;
}

// Handle logout
if(isset($_GET['logout'])){
    session_destroy();
    header('Location: login.php');
    exit;
}

$file = __DIR__.'/../data/content.json';
$data = json_decode(file_get_contents($file), true);
if(!isset($data['menu'])) $data['menu'] = [];
if(!isset($data['pages'])) $data['pages'] = [];
$usersFile = __DIR__.'/../data/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

// Migrate old username shown in session (e.g. 'birgit') to 'admin'
if(isset($_SESSION['admin_username']) && $_SESSION['admin_username'] === 'birgit' && isset($users['admin'])){
    $_SESSION['admin_username'] = 'admin';
}

// Handle file uploads
if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
    $uploadDir = __DIR__.'/../images/';
    if(!is_dir($uploadDir)){
        mkdir($uploadDir, 0755, true);
    }
    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $targetFile = $uploadDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if(in_array($imageFileType, $allowedTypes)){
        if(move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)){
            header('Content-Type: application/json');
            // Return path relative to admin folder (../images/) so it works in WYSIWYG editor
            echo json_encode(['success' => true, 'url' => '../images/' . $fileName]);
            exit;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Image upload failed']);
    exit;
}

if($_POST && isset($_POST['action'])){
    // Handle design reset (must come before generic save)
    if($_POST['action'] === 'reset_design'){
        if(isset($data['design'])){
            unset($data['design']);
            file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = true;
        }
    }
    
    // Handle menu management
    if($_POST['action'] === 'add_menu_item'){
        $id = trim($_POST['menu_id'] ?? '');
        $label = trim($_POST['menu_label'] ?? '');
        $type = $_POST['menu_type'] ?? 'page';
        $url = trim($_POST['menu_url'] ?? '');
        
        if($id && $label){
            // Check if ID already exists
            $exists = false;
            foreach($data['menu'] as $item){
                if($item['id'] === $id){
                    $exists = true;
                    break;
                }
            }
            
            if(!$exists){
                $maxOrder = 0;
                foreach($data['menu'] as $item){
                    if(isset($item['order']) && $item['order'] > $maxOrder){
                        $maxOrder = $item['order'];
                    }
                }
                
                $newItem = [
                    'id' => $id,
                    'label' => $label,
                    'type' => $type,
                    'order' => $maxOrder + 1
                ];
                
                if($type === 'link'){
                    $newItem['url'] = $url;
                } else {
                    // Create empty page if it doesn't exist
                    if(!isset($data['pages'][$id])){
                        $data['pages'][$id] = [
                            'title' => $label,
                            'intro' => '',
                            'content' => ''
                        ];
                    }
                }
                
                $data['menu'][] = $newItem;
                file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $menu_success = 'Menu item added!';
            } else {
            $menu_error = 'A menu item with this ID already exists!';
            }
        } else {
        $menu_error = 'Please fill in all fields!';
        }
    }
    
    if($_POST['action'] === 'delete_menu_item'){
        $id = $_POST['menu_id'] ?? '';
        $deleteType = $_POST['delete_type'] ?? 'both'; // 'both' or 'content'
        
        if($id && $id !== 'home'){
            if($deleteType === 'both'){
                // Delete menu item and content
                $data['menu'] = array_filter($data['menu'], function($item) use ($id){
                    return $item['id'] !== $id;
                });
                $data['menu'] = array_values($data['menu']); // Re-index
                
                // Also delete page content if exists
                if(isset($data['pages'][$id])){
                    unset($data['pages'][$id]);
                }
                
                file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $menu_success = 'Menu item and content deleted!';
            } else {
                // Delete only content, keep menu item
                if(isset($data['pages'][$id])){
                    // Reset to empty content
                    $data['pages'][$id] = [
                        'title' => $data['pages'][$id]['title'] ?? '',
                        'intro' => '',
                        'content' => ''
                    ];
                    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $menu_success = 'Content deleted!';
                } else {
                    $menu_error = 'Content not found!';
                }
            }
        } else {
            $menu_error = 'Cannot delete the homepage!';
        }
    }
    
    if($_POST['action'] === 'update_menu_order'){
        if(isset($_POST['menu_order'])){
            $order = json_decode($_POST['menu_order'], true);
            if($order){
                foreach($data['menu'] as $index => &$item){
                    if(isset($order[$item['id']])){
                        $item['order'] = (int)$order[$item['id']];
                    }
                }
                file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $menu_success = 'Menu order updated!';
            }
        }
    }
    
    if($_POST['action'] === 'update_menu_item'){
        $id = $_POST['menu_id'] ?? '';
        $label = trim($_POST['menu_label'] ?? '');
        $type = $_POST['menu_type'] ?? 'page';
        $url = trim($_POST['menu_url'] ?? '');
        
        if($id && $label){
            foreach($data['menu'] as &$item){
                if($item['id'] === $id){
                    $item['label'] = $label;
                    $item['type'] = $type;
                    if($type === 'link'){
                        $item['url'] = $url;
                    } else {
                        unset($item['url']);
                    }
                    break;
                }
            }
            file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $menu_success = 'Menu item updated!';
        }
    }
    
    // Handle user management
    if($_POST['action'] === 'add_user'){
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if($username && $password){
            if(!isset($users[$username])){
                $users[$username] = password_hash($password, PASSWORD_DEFAULT);
                file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
                $user_success = 'User added!';
            } else {
                $user_error = 'User already exists!';
            }
        } else {
            $user_error = 'Please fill in all fields!';
        }
    }
    
    if($_POST['action'] === 'delete_user'){
        $username = $_POST['username'] ?? '';
        if($username && isset($users[$username])){
            // Don't allow deleting yourself
            if($username !== $_SESSION['admin_username']){
                unset($users[$username]);
                file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
                $user_success = 'User deleted!';
            } else {
                $user_error = 'You cannot delete your own account!';
            }
        }
    }
    
    if($_POST['action'] === 'change_password'){
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if($username && $password && isset($users[$username])){
            $users[$username] = password_hash($password, PASSWORD_DEFAULT);
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            $user_success = 'Password changed!';
        } else {
            $user_error = 'Failed to change password!';
        }
    }
    
    if($_POST['action'] === 'save'){
        // Save site data
        if(isset($_POST['site_title'])) $data['site']['title'] = $_POST['site_title'];
        if(isset($_POST['site_description'])) $data['site']['description'] = $_POST['site_description'];
        if(isset($_POST['site_phone'])) $data['site']['phone'] = $_POST['site_phone'];
        if(isset($_POST['site_email'])) $data['site']['email'] = $_POST['site_email'];
        
        // Save custom design (CSS/JS/Template)
        if(isset($_POST['custom_css']) || isset($_POST['custom_js']) || isset($_POST['template_html']) || isset($_POST['template_notes'])){
            if(!isset($data['design'])){
                $data['design'] = [];
            }
            if(isset($_POST['custom_css'])) $data['design']['custom_css'] = $_POST['custom_css'];
            if(isset($_POST['custom_js'])) $data['design']['custom_js'] = $_POST['custom_js'];
            if(isset($_POST['template_html'])) $data['design']['template_html'] = $_POST['template_html'];
            if(isset($_POST['template_notes'])) $data['design']['template_notes'] = $_POST['template_notes'];
        }
        
        // Save page content dynamically
        if(isset($_POST['page_id'])){
            $pageId = $_POST['page_id'];
            if(!isset($data['pages'][$pageId])){
                $data['pages'][$pageId] = [];
            }
            $pageData = &$data['pages'][$pageId];
            
            // Save all page fields dynamically
            foreach($_POST as $key => $value){
                if($key !== 'action' && $key !== 'page_id' && strpos($key, 'page_') === 0){
                    $field = substr($key, 5); // Remove 'page_' prefix
                    if(is_array($value)){
                        $pageData[$field] = $value;
                    } else {
                        $pageData[$field] = $value;
                    }
                }
            }
        }
        
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $success = true;
    }
}

$page = $_GET['page'] ?? 'site';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin – SimplePHP</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  body {
    background: linear-gradient(120deg, #f6f8fa, #dbeafe);
    color: #333;
    min-height: 100vh;
    padding: 20px;
  }

  .wrap {
    max-width: 1200px;
    margin: 0 auto;
  }

  .header {
    background-color: #2563eb;
    color: #fff;
    padding: 24px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  }

  .header h1 {
    font-size: 28px;
    margin-bottom: 8px;
    font-weight: 800;
  }

  .header p {
    color: rgba(255,255,255,0.9);
    font-size: 14px;
  }
  .nav-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
  }

  .nav-tab {
    background: #fff;
    border: 1px solid #e5e7eb;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: background 0.3s, transform 0.2s;
    font-size: 14px;
  }

  .nav-tab:hover {
    background: #f3f4f6;
    transform: scale(1.05);
  }

  .nav-tab.active {
    background-color: #2563eb;
    color: #fff;
    border-color: #2563eb;
    font-weight: 700;
  }

  .card {
    background: #fff;
    border: 1px solid #e5e7eb;
    padding: 28px;
    border-radius: 15px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }

  .card h2 {
    font-size: 22px;
    margin-bottom: 20px;
    color: #2563eb;
    font-weight: 800;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
  }

  .form-group input,
  .form-group textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #333;
    font-size: 14px;
    font-family: inherit;
  }

  .form-group input:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: #2563eb;
    background: #f8f9fa;
  }

  .form-group textarea {
    min-height: 100px;
    resize: vertical;
  }

  .form-group select {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #333;
    font-size: 14px;
    font-family: inherit;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 40px;
  }

  .form-group select:focus {
    outline: none;
    border-color: #2563eb;
    background-color: #f8f9fa;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%232563eb' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  }

  .form-group select option {
    background: #fff;
    color: #333;
    padding: 8px;
  }

  .form-group input[type="radio"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #2563eb;
  }

  .form-group label[style*="cursor:pointer"] {
    transition: background 0.3s, border-color 0.3s;
  }

  .form-group label[style*="cursor:pointer"]:hover {
    background: #f8f9fa;
    border-color: #2563eb;
  }

  .form-group input[type="radio"]:checked + span {
    color: #333;
  }

  .button {
    background-color: #2563eb;
    color: #fff;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s, transform 0.2s;
  }

  .button:hover {
    background-color: #1e40af;
    transform: scale(1.05);
  }

  .success {
    background: rgba(76,175,80,0.2);
    border: 1px solid rgba(76,175,80,0.4);
    color: #4caf50;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
  }

  .item-group {
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 15px;
    border-left: 3px solid #2563eb;
  }

  .item-group h3 {
    font-size: 18px;
    margin-bottom: 15px;
    color: #2563eb;
    font-weight: 700;
  }

  /* Modal */
  .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(4px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }

  .modal.active {
    display: flex;
  }

  .modal-content {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    padding: 32px;
    max-width: 450px;
    width: 100%;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    position: relative;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }

  .modal-header h3 {
    margin: 0;
    font-size: 20px;
    color: #2563eb;
    font-weight: 800;
  }

  .modal-close {
    background: none;
    border: none;
    color: #666;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: background 0.3s, color 0.3s;
  }

  .modal-close:hover {
    background: #f3f4f6;
    color: #333;
  }

  .modal-body {
    margin-bottom: 24px;
  }

  .modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
  }

  .btn-secondary {
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    color: #333;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s, border-color 0.3s;
  }

  .btn-secondary:hover {
    background: #e5e7eb;
    border-color: #d1d5db;
  }

  /* Quill Editor Styles */
  .ql-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    color: #333;
  }

  .ql-editor {
    min-height: 150px;
    color: #333;
  }

  .ql-editor img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 8px 0;
    display: block;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .ql-toolbar {
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    border-radius: 8px 8px 0 0;
  }

  .ql-container {
    border: 1px solid #e5e7eb;
    border-top: none;
    border-radius: 0 0 8px 8px;
    background: #fff;
  }

  .ql-stroke {
    stroke: #333;
  }

  .ql-fill {
    fill: #333;
  }

  .ql-picker-label {
    color: #333;
  }

  .ql-snow .ql-picker.ql-expanded .ql-picker-label {
    border-color: #e5e7eb;
  }

  .ql-snow .ql-picker-options {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
  }

  .ql-snow .ql-picker-item {
    color: #333;
  }

  .ql-snow .ql-picker-item:hover {
    background: #f8f9fa;
  }

  .ql-snow .ql-tooltip {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #333;
  }

  .ql-snow .ql-tooltip input {
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    color: #333;
  }
</style>
</head>
<body>
<div class="wrap">
<div class="header">
<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap">
<div>
<h1>SimplePHP Admin</h1>
<p>Edit all pages and content</p>
</div>
<div style="display:flex;align-items:center;gap:12px">
<span style="color:var(--muted);font-size:14px"><?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?></span>
<a href="?logout=1" class="button" style="text-decoration:none;display:inline-block;padding:10px 16px;font-size:14px">Logout</a>
</div>
</div>
</div>

<?php if(isset($success)): ?>
<div class="success">Saved!</div>
<?php endif; ?>

<?php if(isset($user_success)): ?>
<div class="success"><?= htmlspecialchars($user_success) ?></div>
<?php endif; ?>

<?php if(isset($user_error)): ?>
<div class="error" style="background:rgba(244,67,54,.2);border:1px solid rgba(244,67,54,.4);color:#f44336;padding:15px;border-radius:8px;margin-bottom:20px"><?= htmlspecialchars($user_error) ?></div>
<?php endif; ?>

<?php if(isset($menu_success)): ?>
<div class="success"><?= htmlspecialchars($menu_success) ?></div>
<?php endif; ?>

<?php if(isset($menu_error)): ?>
<div class="error" style="background:rgba(244,67,54,.2);border:1px solid rgba(244,67,54,.4);color:#f44336;padding:15px;border-radius:8px;margin-bottom:20px"><?= htmlspecialchars($menu_error) ?></div>
<?php endif; ?>

<div class="nav-tabs">
<a href="?page=site" class="nav-tab <?= $page === 'site' ? 'active' : '' ?>">Site</a>
<a href="?page=menu" class="nav-tab <?= $page === 'menu' ? 'active' : '' ?>">Menu</a>
<?php 
$menuItems = $data['menu'] ?? [];
usort($menuItems, function($a, $b) { return ($a['order'] ?? 999) <=> ($b['order'] ?? 999); });
foreach($menuItems as $item): 
    if($item['type'] === 'page'): ?>
<a href="?page=<?= htmlspecialchars($item['id']) ?>" class="nav-tab <?= $page === $item['id'] ? 'active' : '' ?>"><?= htmlspecialchars($item['label']) ?></a>
<?php endif; endforeach; ?>
<a href="?page=design" class="nav-tab <?= $page === 'design' ? 'active' : '' ?>">Design</a>
<a href="?page=users" class="nav-tab <?= $page === 'users' ? 'active' : '' ?>">Users</a>
<a href="../" class="nav-tab" target="_blank">View Site</a>
</div>

<form method="post">
<input type="hidden" name="action" value="save">

<?php if($page === 'site'): ?>
<div class="card">
<h2>Site Settings</h2>
<div class="form-group">
<label>Title</label>
<input type="text" name="site_title" value="<?= htmlspecialchars($data['site']['title']) ?>">
</div>
<div class="form-group">
<label>Description</label>
<input type="text" name="site_description" value="<?= htmlspecialchars($data['site']['description']) ?>">
</div>
<div class="form-group">
<label>Phone</label>
<input type="text" name="site_phone" value="<?= htmlspecialchars($data['site']['phone']) ?>">
</div>
<div class="form-group">
<label>Email</label>
<input type="email" name="site_email" value="<?= htmlspecialchars($data['site']['email']) ?>">
</div>
</div>

<?php elseif($page === 'menu'): ?>
<div class="card">
<h2>Add New Menu Item</h2>
<form method="post" style="margin-bottom:30px">
<input type="hidden" name="action" value="add_menu_item">
<div class="form-group">
<label>ID (lowercase, no spaces, e.g. new-page)</label>
<input type="text" name="menu_id" required pattern="[a-z0-9-]+" placeholder="uus-leht">
<small style="color:#666;font-size:12px;margin-top:4px;display:block">Used in the URL</small>
</div>
<div class="form-group">
<label>Label (text shown in the menu)</label>
<input type="text" name="menu_label" required placeholder="Uus leht">
</div>
<div class="form-group">
<label>Type</label>
<select name="menu_type" id="menu_type_select" required onchange="toggleMenuUrl()">
<option value="page">Page</option>
<option value="link">External link</option>
</select>
</div>
<div class="form-group" id="menu_url_group" style="display:none">
<label>URL (external link)</label>
<input type="url" name="menu_url" placeholder="https://example.com">
</div>
<button type="submit" class="button">Add menu item</button>
</form>
</div>

<div class="card">
<h2>Menu Items</h2>
<?php 
$menuItems = $data['menu'] ?? [];
usort($menuItems, function($a, $b) { return ($a['order'] ?? 999) <=> ($b['order'] ?? 999); });
if(empty($menuItems)): ?>
<p style="color:#666">No menu items</p>
<?php else: ?>
<div style="display:grid;gap:12px" id="menu-items-list">
<?php foreach($menuItems as $item): ?>
<div class="item-group" data-id="<?= htmlspecialchars($item['id']) ?>">
<div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap">
<div style="flex:1">
<h3 style="margin:0"><?= htmlspecialchars($item['label']) ?></h3>
<div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">
<span class="chip"><?= $item['type'] === 'page' ? 'Page' : 'Link' ?></span>
<?php if($item['type'] === 'link' && isset($item['url'])): ?>
<span class="chip" style="font-size:11px"><?= htmlspecialchars($item['url']) ?></span>
<?php endif; ?>
</div>
</div>
<div style="display:flex;gap:10px;flex-wrap:wrap">
<button type="button" class="button" style="padding:8px 16px;font-size:14px" onclick="openMenuEditModal('<?= htmlspecialchars($item['id']) ?>', '<?= htmlspecialchars($item['label']) ?>', '<?= htmlspecialchars($item['type']) ?>', '<?= htmlspecialchars($item['url'] ?? '') ?>')">Edit</button>
<?php if($item['id'] !== 'home'): ?>
<button type="button" class="button" style="padding:8px 16px;font-size:14px;background:rgba(244,67,54,.2);border-color:rgba(244,67,54,.4);color:#f44336" onclick="openDeleteModal('<?= htmlspecialchars($item['id']) ?>', '<?= htmlspecialchars($item['label']) ?>', '<?= $item['type'] === 'page' ? 'true' : 'false' ?>')">Delete</button>
<?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<?php elseif($page === 'home' || (isset($data['menu']) && in_array($page, array_column($data['menu'], 'id')))): ?>
<?php 
$pageId = $page === 'home' ? 'home' : $page;
$pageData = $data['pages'][$pageId] ?? [];
$isContactPage = ($pageId === 'contact' || strpos(strtolower($pageId), 'contact') !== false);

// If page data is empty, create default structure
if(empty($pageData)){
    if($isContactPage){
        $pageData = [
            'title' => '',
            'intro' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            'form_title' => '',
            'form_name' => '',
            'form_email' => '',
            'form_message' => '',
            'form_submit' => ''
        ];
    } else {
        $pageData = [
            'title' => '',
            'intro' => '',
            'content' => ''
        ];
    }
}
?>
  <div class="card">
<h2>Page Content: <?= htmlspecialchars($pageId) ?></h2>
    <form method="post">
<input type="hidden" name="action" value="save">
<input type="hidden" name="page_id" value="<?= htmlspecialchars($pageId) ?>">

<?php if($isContactPage): ?>
<!-- Contact Page Specific Fields -->
<div class="item-group">
<h3>Page Information</h3>
<div class="form-group">
<label>Title</label>
<input type="text" name="page_title" value="<?= htmlspecialchars($pageData['title'] ?? '') ?>">
</div>
<div class="form-group">
<label>Introduction</label>
<div class="editor-wrapper">
<div id="editor_intro" style="min-height:150px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;color:#333"></div>
<textarea name="page_intro" id="hidden_intro" style="display:none"><?= htmlspecialchars($pageData['intro'] ?? '') ?></textarea>
</div>
</div>
</div>

<div class="item-group">
<h3>Contact Information</h3>
<div class="form-group">
<label>Phone</label>
<input type="text" name="page_phone" value="<?= htmlspecialchars($pageData['phone'] ?? '') ?>" placeholder="+1 234 567 8900">
</div>
<div class="form-group">
<label>Email</label>
<input type="email" name="page_email" value="<?= htmlspecialchars($pageData['email'] ?? '') ?>" placeholder="info@example.com">
</div>
<div class="form-group">
<label>Address</label>
<input type="text" name="page_address" value="<?= htmlspecialchars($pageData['address'] ?? '') ?>" placeholder="Street Address, City, Country">
</div>
</div>

<div class="item-group">
<h3>Contact Form Settings</h3>
<div class="form-group">
<label>Form Title</label>
<input type="text" name="page_form_title" value="<?= htmlspecialchars($pageData['form_title'] ?? '') ?>" placeholder="Send Message">
</div>
<div class="form-group">
<label>Name Field Label</label>
<input type="text" name="page_form_name" value="<?= htmlspecialchars($pageData['form_name'] ?? '') ?>" placeholder="Name">
</div>
<div class="form-group">
<label>Email Field Label</label>
<input type="text" name="page_form_email" value="<?= htmlspecialchars($pageData['form_email'] ?? '') ?>" placeholder="Email">
</div>
<div class="form-group">
<label>Message Field Label</label>
<input type="text" name="page_form_message" value="<?= htmlspecialchars($pageData['form_message'] ?? '') ?>" placeholder="Message">
</div>
<div class="form-group">
<label>Submit Button Text</label>
<input type="text" name="page_form_submit" value="<?= htmlspecialchars($pageData['form_submit'] ?? '') ?>" placeholder="Send">
</div>
</div>

<?php else: ?>
<?php 
// Generate form fields dynamically based on page data for non-contact pages
if(!empty($pageData)):
foreach($pageData as $key => $value):
    if(is_array($value)):
        // Handle arrays (like features, services, steps)
        ?>
<div class="item-group">
<h3><?= htmlspecialchars(ucfirst($key)) ?></h3>
<?php foreach($value as $index => $subItem): ?>
<div style="margin-bottom:20px;padding:15px;background:#f8f9fa;border-radius:8px">
<?php foreach($subItem as $subKey => $subValue): ?>
<div class="form-group">
<label><?= htmlspecialchars(ucfirst($subKey)) ?></label>
<?php if(strlen($subValue) > 100 || in_array($subKey, ['text', 'content', 'intro'])): ?>
<div class="editor-wrapper">
<div id="editor_<?= htmlspecialchars($key) ?>_<?= $index ?>_<?= htmlspecialchars($subKey) ?>" style="min-height:150px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;color:#333"></div>
<textarea name="page_<?= htmlspecialchars($key) ?>[<?= $index ?>][<?= htmlspecialchars($subKey) ?>]" id="hidden_<?= htmlspecialchars($key) ?>_<?= $index ?>_<?= htmlspecialchars($subKey) ?>" style="display:none"><?= htmlspecialchars($subValue) ?></textarea>
</div>
<?php else: ?>
<input type="text" name="page_<?= htmlspecialchars($key) ?>[<?= $index ?>][<?= htmlspecialchars($subKey) ?>]" value="<?= htmlspecialchars($subValue) ?>">
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
<?php
    else:
        // Handle simple fields (skip contact-specific fields as they're handled above)
        if(!in_array($key, ['phone', 'email', 'address', 'form_title', 'form_name', 'form_email', 'form_message', 'form_submit'])):
        ?>
<div class="form-group">
<label><?= htmlspecialchars(ucfirst($key)) ?></label>
<?php if(strlen($value) > 100 || in_array($key, ['intro', 'content', 'text', 'hero_text'])): ?>
<div class="editor-wrapper">
<div id="editor_<?= htmlspecialchars($key) ?>" style="min-height:200px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;color:#333"></div>
<textarea name="page_<?= htmlspecialchars($key) ?>" id="hidden_<?= htmlspecialchars($key) ?>" style="display:none"><?= htmlspecialchars($value) ?></textarea>
</div>
<?php else: ?>
<input type="text" name="page_<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
<?php endif; ?>
</div>
<?php
        endif;
    endif;
endforeach;
else:
// Show default fields if page data is completely empty
?>
<div class="form-group">
<label>Title</label>
<input type="text" name="page_title" value="">
</div>
<div class="form-group">
<label>Introduction</label>
<div class="editor-wrapper">
<div id="editor_intro" style="min-height:200px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;color:#333"></div>
<textarea name="page_intro" id="hidden_intro" style="display:none"></textarea>
</div>
</div>
<div class="form-group">
<label>Content</label>
<div class="editor-wrapper">
<div id="editor_content" style="min-height:300px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;color:#333"></div>
<textarea name="page_content" id="hidden_content" style="display:none"></textarea>
</div>
</div>
<?php endif; ?>
<?php endif; ?>

<button type="submit" class="button">Save Changes</button>
</form>
</div>

<?php elseif($page === 'home'): ?>
<div class="card">
<h2>Hero sektsioon</h2>
<div class="form-group">
<label>Pealkiri</label>
<input type="text" name="home_hero_title" value="<?= htmlspecialchars($data['home']['hero_title']) ?>">
</div>
<div class="form-group">
<label>Tekst</label>
<textarea name="home_hero_text"><?= htmlspecialchars($data['home']['hero_text']) ?></textarea>
</div>
<div class="form-group">
<label>Nupu tekst</label>
<input type="text" name="home_cta_button" value="<?= htmlspecialchars($data['home']['cta_button']) ?>">
</div>
</div>

<div class="card">
<h2>Funktsioonid</h2>
<div class="form-group">
<label>Funktsioonide pealkiri</label>
<input type="text" name="home_features_title" value="<?= htmlspecialchars($data['home']['features_title']) ?>">
</div>
<?php foreach($data['home']['features'] as $index => $feature): ?>
<div class="item-group">
<h3>Funktsioon <?= $index + 1 ?></h3>
<div class="form-group">
<label>Pealkiri</label>
<input type="text" name="home_features[<?= $index ?>][title]" value="<?= htmlspecialchars($feature['title']) ?>">
</div>
<div class="form-group">
<label>Tekst</label>
<textarea name="home_features[<?= $index ?>][text]"><?= htmlspecialchars($feature['text']) ?></textarea>
</div>
</div>
<?php endforeach; ?>
</div>

<?php elseif($page === 'design'): ?>
<div class="card">
<h2>Design (HTML / CSS / JavaScript)</h2>
<p style="color:#666;margin-bottom:20px">
  Paste ChatGPT HTML5 templates here and fully customize the frontend.
  If <strong>Template HTML</strong> is empty, the site will use the default layout.
</p>

<div class="item-group">
  <h3>Template HTML (optional override)</h3>
  <p style="color:#666;margin:0 0 10px">
    Placeholders:
    <code style="background:#f8f9fa;padding:2px 6px;border-radius:4px;font-size:11px">{{SITE_TITLE}}</code>
    <code style="background:#f8f9fa;padding:2px 6px;border-radius:4px;font-size:11px">{{PAGE_TITLE}}</code>
    <code style="background:#f8f9fa;padding:2px 6px;border-radius:4px;font-size:11px">{{DESCRIPTION}}</code>
    <code style="background:#f8f9fa;padding:2px 6px;border-radius:4px;font-size:11px">{{NAV}}</code>
    <code style="background:#f8f9fa;padding:2px 6px;border-radius:4px;font-size:11px">{{CONTENT}}</code>
    <code style="background:#f8f9fa;padding:2px 6px;border-radius:4px;font-size:11px">{{CUSTOM_CSS}}</code>
    <code style="background:#f8f9fa;padding:2px 6px;border-radius:4px;font-size:11px">{{CUSTOM_JS}}</code>
    <code style="background:#f8f9fa;padding:2px 6px;border-radius:4px;font-size:11px">{{DEFAULT_CONTACT_FORM_SCRIPT}}</code>
  </p>
  <textarea name="template_html" style="width:100%;min-height:340px;padding:15px;border:1px solid #e5e7eb;border-radius:8px;background:#1e1e1e;color:#d4d4d4;font-family:'Courier New',monospace;font-size:13px;line-height:1.6" placeholder="<!DOCTYPE html>
<html lang=&quot;en&quot;>
<head>
  <meta charset=&quot;utf-8&quot;>
  <title>{{PAGE_TITLE}} – {{SITE_TITLE}}</title>
  <meta name=&quot;description&quot; content=&quot;{{DESCRIPTION}}&quot;>
  <style>
  {{CUSTOM_CSS}}
  </style>
</head>
<body>
  <header>
    <h1>{{SITE_TITLE}}</h1>
    <nav>{{NAV}}</nav>
  </header>

  {{CONTENT}}

  <script>
  {{CUSTOM_JS}}
  </script>
  {{DEFAULT_CONTACT_FORM_SCRIPT}}
</body>
</html>"><?= htmlspecialchars($data['design']['template_html'] ?? '') ?></textarea>
  <small style="color:#666;font-size:12px;margin-top:8px;display:block">
    <strong>Warning:</strong> Template override can break layout if placeholders are missing.
  </small>
</div>

<div class="item-group">
  <h3>Custom CSS</h3>
  <textarea name="custom_css" id="custom-css-editor" style="width:100%;min-height:220px;padding:15px;border:1px solid #e5e7eb;border-radius:8px;background:#1e1e1e;color:#d4d4d4;font-family:'Courier New',monospace;font-size:13px;line-height:1.6" placeholder="/* Add your custom CSS here */
body { background: #fff; }
.btn { border-radius: 999px; }"><?= htmlspecialchars($data['design']['custom_css'] ?? '') ?></textarea>
</div>

<div class="item-group">
  <h3>Custom JavaScript</h3>
  <textarea name="custom_js" style="width:100%;min-height:220px;padding:15px;border:1px solid #e5e7eb;border-radius:8px;background:#1e1e1e;color:#d4d4d4;font-family:'Courier New',monospace;font-size:13px;line-height:1.6" placeholder="// Add your custom JavaScript here
console.log('Hello from custom JS');"><?= htmlspecialchars($data['design']['custom_js'] ?? '') ?></textarea>
</div>

<div class="form-group">
  <label>Notes (optional)</label>
  <textarea name="template_notes" style="width:100%;min-height:120px"><?= htmlspecialchars($data['design']['template_notes'] ?? '') ?></textarea>
</div>

<div style="display:flex;gap:12px;flex-wrap:wrap">
  <button type="submit" class="button">Save Design</button>
  <form method="post" onsubmit="return confirm('Are you sure you want to reset to the default design? This will remove custom HTML, CSS and JS.');">
    <input type="hidden" name="action" value="reset_design">
    <button type="submit" class="button" style="background:rgba(244,67,54,.2);border-color:rgba(244,67,54,.4);color:#f44336">
      Reset to Default Design
    </button>
  </form>
</div>
</div>

<div class="card">
<h2>CSS Examples</h2>
<div style="background:#f8f9fa;padding:20px;border-radius:8px;margin-bottom:15px">
<h4 style="margin-bottom:10px;color:#2563eb">Change Header Background</h4>
<pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:8px;overflow-x:auto;font-size:12px"><code>header {
  background-color: #ff6b6b !important;
}</code></pre>
</div>
<div style="background:#f8f9fa;padding:20px;border-radius:8px;margin-bottom:15px">
<h4 style="margin-bottom:10px;color:#2563eb">Change Button Colors</h4>
<pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:8px;overflow-x:auto;font-size:12px"><code>.btn, button {
  background-color: #51cf66 !important;
  color: #fff !important;
}</code></pre>
</div>
<div style="background:#f8f9fa;padding:20px;border-radius:8px;margin-bottom:15px">
<h4 style="margin-bottom:10px;color:#2563eb">Custom Card Styling</h4>
<pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:8px;overflow-x:auto;font-size:12px"><code>.card {
  box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
  border: 2px solid #2563eb !important;
}</code></pre>
</div>
</div>

<?php elseif($page === 'users'): ?>
<div class="card">
<h2>Add New User</h2>
<form method="post" style="margin-bottom:30px">
<input type="hidden" name="action" value="add_user">
<div class="form-group">
<label>Username</label>
<input type="text" name="username" required>
</div>
<div class="form-group">
<label>Password</label>
<input type="password" name="password" required>
</div>
<button type="submit" class="button">Add user</button>
</form>
</div>

<div class="card">
<h2>Existing Users</h2>
<?php if(empty($users)): ?>
<p style="color:var(--muted)">No users</p>
<?php else: ?>
<div style="display:grid;gap:12px">
<?php foreach($users as $username => $hash): ?>
<div class="item-group">
<div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap">
<div>
<h3 style="margin:0"><?= htmlspecialchars($username) ?></h3>
<?php if($username === $_SESSION['admin_username']): ?>
<span class="chip" style="margin-top:8px;display:inline-block">Current user</span>
<?php endif; ?>
</div>
<div style="display:flex;gap:10px;flex-wrap:wrap">
<button type="button" class="button" style="padding:8px 16px;font-size:14px" onclick="openPasswordModal('<?= htmlspecialchars($username) ?>')">Change password</button>
<?php if($username !== $_SESSION['admin_username']): ?>
<form method="post" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
<input type="hidden" name="action" value="delete_user">
<input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
<button type="submit" class="button" style="padding:8px 16px;font-size:14px;background:rgba(244,67,54,.2);border-color:rgba(244,67,54,.4);color:#f44336">Delete</button>
</form>
<?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
<?php endif; ?>

<?php if($page !== 'users'): ?>
<button type="submit" class="button">Save Changes</button>
<?php endif; ?>
</form>

<?php if($page === 'menu'): ?>
<!-- Delete Menu Item Modal -->
<div id="deleteMenuModal" class="modal">
<div class="modal-content">
<div class="modal-header">
<h3>Delete Menu Item</h3>
<button type="button" class="modal-close" onclick="closeDeleteModal()">&times;</button>
</div>
<form method="post" id="deleteMenuForm">
<input type="hidden" name="action" value="delete_menu_item">
<input type="hidden" name="menu_id" id="deleteMenuId">
<div class="modal-body">
<p style="color:var(--muted);margin-bottom:20px">Choose what you want to delete:</p>
<div class="form-group">
<label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:12px;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:12px">
<input type="radio" name="delete_type" value="both" checked style="width:auto;margin:0">
<span style="flex:1">
<strong>Menu item with its content</strong><br>
<small style="color:#666;font-size:12px">Deletes both the menu item and the page content</small>
</span>
</label>
<label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:12px;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:8px" id="contentOnlyLabel">
<input type="radio" name="delete_type" value="content" style="width:auto;margin:0">
<span style="flex:1">
<strong>Content only</strong><br>
<small style="color:#666;font-size:12px">Deletes only the page content, keeps the menu item</small>
</span>
</label>
</div>
<p style="color:#666;font-size:13px;margin-top:12px" id="deleteMenuInfo"></p>
</div>
<div class="modal-footer">
<button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
<button type="submit" class="button" style="background:rgba(244,67,54,.2);border-color:rgba(244,67,54,.4);color:#f44336">Delete</button>
</div>
</form>
</div>
</div>

<!-- Menu Edit Modal -->
<div id="menuEditModal" class="modal">
<div class="modal-content">
<div class="modal-header">
<h3>Edit Menu Item</h3>
<button type="button" class="modal-close" onclick="closeMenuEditModal()">&times;</button>
</div>
<form method="post" id="menuEditForm">
<input type="hidden" name="action" value="update_menu_item">
<input type="hidden" name="menu_id" id="editMenuId">
<div class="modal-body">
<div class="form-group">
<label>Label (text shown in the menu)</label>
<input type="text" name="menu_label" id="editMenuLabel" required>
</div>
<div class="form-group">
<label>Type</label>
<select name="menu_type" id="editMenuType" required onchange="toggleEditMenuUrl()">
<option value="page">Page</option>
<option value="link">External link</option>
</select>
</div>
<div class="form-group" id="editMenuUrlGroup" style="display:none">
<label>URL (external link)</label>
<input type="url" name="menu_url" id="editMenuUrl" placeholder="https://example.com">
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn-secondary" onclick="closeMenuEditModal()">Cancel</button>
<button type="submit" class="button">Save</button>
</div>
</form>
</div>
</div>
<?php endif; ?>

<?php if($page === 'users'): ?>
<!-- Password Change Modal -->
<div id="passwordModal" class="modal">
<div class="modal-content">
<div class="modal-header">
<h3>Change Password</h3>
<button type="button" class="modal-close" onclick="closePasswordModal()">&times;</button>
</div>
<form method="post" id="passwordForm">
<input type="hidden" name="action" value="change_password">
<input type="hidden" name="username" id="modalUsername">
<div class="modal-body">
<div class="form-group">
<label>User</label>
<input type="text" id="modalUsernameDisplay" readonly style="background:#f8f9fa;opacity:.7">
</div>
<div class="form-group">
<label>New password</label>
<input type="password" name="password" id="modalPassword" required autofocus>
</div>
<div class="form-group">
<label>Confirm password</label>
<input type="password" id="modalPasswordConfirm" required>
<small style="color:#666;font-size:12px;margin-top:4px;display:block">Passwords must match</small>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn-secondary" onclick="closePasswordModal()">Cancel</button>
<button type="submit" class="button">Change password</button>
</div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
function openPasswordModal(username){
  document.getElementById('modalUsername').value = username;
  document.getElementById('modalUsernameDisplay').value = username;
  document.getElementById('modalPassword').value = '';
  document.getElementById('modalPasswordConfirm').value = '';
  document.getElementById('passwordModal').classList.add('active');
  document.getElementById('modalPassword').focus();
}

function closePasswordModal(){
  document.getElementById('passwordModal').classList.remove('active');
  document.getElementById('passwordForm').reset();
}

// Close modal on outside click
const passwordModal = document.getElementById('passwordModal');
if(passwordModal){
  passwordModal.addEventListener('click', function(e){
    if(e.target === this){
      closePasswordModal();
    }
  });
}

// Close modal on ESC key
document.addEventListener('keydown', function(e){
  if(e.key === 'Escape'){
    closePasswordModal();
  }
});

// Password confirmation validation
const passwordForm = document.getElementById('passwordForm');
if(passwordForm){
  passwordForm.addEventListener('submit', function(e){
    const password = document.getElementById('modalPassword').value;
    const confirm = document.getElementById('modalPasswordConfirm').value;
    
    if(password !== confirm){
      e.preventDefault();
      alert('Passwords do not match!');
      document.getElementById('modalPassword').focus();
      return false;
    }
    
    if(password.length < 6){
      e.preventDefault();
      alert('Password must be at least 6 characters long!');
      document.getElementById('modalPassword').focus();
      return false;
    }
  });
  
  // Real-time password match indicator
  const passwordInput = document.getElementById('modalPassword');
  const confirmInput = document.getElementById('modalPasswordConfirm');
  
  function checkPasswordMatch(){
    if(confirmInput.value && passwordInput.value){
      if(passwordInput.value === confirmInput.value){
        confirmInput.style.borderColor = 'rgba(76,175,80,.6)';
      } else {
        confirmInput.style.borderColor = 'rgba(244,67,54,.6)';
      }
    } else {
      confirmInput.style.borderColor = 'var(--line)';
    }
  }
  
  passwordInput?.addEventListener('input', checkPasswordMatch);
  confirmInput?.addEventListener('input', checkPasswordMatch);
}
</script>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
// Initialize Quill editors
document.addEventListener('DOMContentLoaded', function(){
  const editors = {};
  
  // Find all editor containers
  document.querySelectorAll('[id^="editor_"]').forEach(function(editorEl){
    const editorId = editorEl.id;
    const hiddenId = 'hidden_' + editorId.replace('editor_', '');
    const hiddenTextarea = document.getElementById(hiddenId);
    
    if(hiddenTextarea){
      // Initialize Quill
      const quill = new Quill('#' + editorId, {
        theme: 'snow',
        modules: {
          toolbar: {
            container: [
              [{ 'header': [1, 2, 3, false] }],
              ['bold', 'italic', 'underline', 'strike'],
              [{ 'list': 'ordered'}, { 'list': 'bullet' }],
              ['link', 'image'],
              [{ 'color': [] }, { 'background': [] }],
              ['clean']
            ],
            handlers: {
              image: function() {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.click();
                
                input.onchange = function() {
                  const file = input.files[0];
                  if(file){
                    const formData = new FormData();
                    formData.append('image', file);
                    
                    fetch('', {
                      method: 'POST',
                      body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                      if(data.success){
                        const range = quill.getSelection(true);
                        quill.insertEmbed(range.index, 'image', data.url);
                        quill.setSelection(range.index + 1);
                      } else {
                        alert('Image upload failed');
                      }
                    })
                    .catch(error => {
                      alert('Image upload failed');
                    });
                  }
                };
              }
            }
          }
        },
        placeholder: 'Enter text...'
      });
      
      // Set initial content
      if(hiddenTextarea.value){
        // Fix image paths for admin panel display (../images/ stays as is for admin)
        let content = hiddenTextarea.value;
        quill.root.innerHTML = content;
      }
      
      // Update hidden textarea on content change
      quill.on('text-change', function() {
        hiddenTextarea.value = quill.root.innerHTML;
      });
      
      // Update hidden textarea before form submit
      const form = hiddenTextarea.closest('form');
      if(form){
        form.addEventListener('submit', function() {
          hiddenTextarea.value = quill.root.innerHTML;
        });
      }
      
      editors[editorId] = quill;
    }
  });
});
</script>

<?php if($page === 'menu'): ?>
<script>
function toggleMenuUrl(){
  const type = document.getElementById('menu_type_select').value;
  const urlGroup = document.getElementById('menu_url_group');
  if(type === 'link'){
    urlGroup.style.display = 'block';
  } else {
    urlGroup.style.display = 'none';
  }
}

function openMenuEditModal(id, label, type, url){
  document.getElementById('editMenuId').value = id;
  document.getElementById('editMenuLabel').value = label;
  document.getElementById('editMenuType').value = type;
  if(url){
    document.getElementById('editMenuUrl').value = url;
  }
  toggleEditMenuUrl();
  document.getElementById('menuEditModal').classList.add('active');
  document.getElementById('editMenuLabel').focus();
}

function closeMenuEditModal(){
  document.getElementById('menuEditModal').classList.remove('active');
  document.getElementById('menuEditForm').reset();
}

function toggleEditMenuUrl(){
  const type = document.getElementById('editMenuType').value;
  const urlGroup = document.getElementById('editMenuUrlGroup');
  if(type === 'link'){
    urlGroup.style.display = 'block';
  } else {
    urlGroup.style.display = 'none';
  }
}

// Close modal on outside click
const menuEditModal = document.getElementById('menuEditModal');
if(menuEditModal){
  menuEditModal.addEventListener('click', function(e){
    if(e.target === this){
      closeMenuEditModal();
    }
  });
}

// Close modal on ESC key
document.addEventListener('keydown', function(e){
  if(e.key === 'Escape' && menuEditModal?.classList.contains('active')){
    closeMenuEditModal();
  }
  if(e.key === 'Escape' && document.getElementById('deleteMenuModal')?.classList.contains('active')){
    closeDeleteModal();
  }
});

// Delete modal functions
function openDeleteModal(id, label, isPage){
  document.getElementById('deleteMenuId').value = id;
  document.getElementById('deleteMenuInfo').textContent = 'Menu item: ' + label;
  
  const contentOnlyLabel = document.getElementById('contentOnlyLabel');
  if(isPage === 'true'){
    contentOnlyLabel.style.display = 'flex';
  } else {
    contentOnlyLabel.style.display = 'none';
    // If it's a link, only allow deleting the whole item
    document.querySelector('input[name="delete_type"][value="both"]').checked = true;
  }
  
  document.getElementById('deleteMenuModal').classList.add('active');
}

function closeDeleteModal(){
  document.getElementById('deleteMenuModal').classList.remove('active');
  document.getElementById('deleteMenuForm').reset();
}

// Close delete modal on outside click
const deleteMenuModal = document.getElementById('deleteMenuModal');
if(deleteMenuModal){
  deleteMenuModal.addEventListener('click', function(e){
    if(e.target === this){
      closeDeleteModal();
    }
  });
}
</script>
<?php endif; ?>
</div>
</body>
</html>
