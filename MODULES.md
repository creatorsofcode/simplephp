# 🧩 SimplePHP Module System

A powerful and flexible module system for SimplePHP CMS that allows developers to extend functionality without modifying core files.

## 📋 Table of Contents

- [Overview](#overview)
- [Module Structure](#module-structure)
- [Creating a Module](#creating-a-module)
- [Module Configuration](#module-configuration)
- [Hooks System](#hooks-system)
- [Installation & Uninstallation](#installation--uninstallation)
- [Best Practices](#best-practices)
- [Examples](#examples)

---

## 🎯 Overview

The SimplePHP Module System provides:

- **Auto-discovery** - Modules are automatically detected
- **Easy installation** - Install/uninstall with one click
- **Activation control** - Enable/disable modules without uninstalling
- **Hook system** - Integrate with the CMS core
- **Admin interface** - Beautiful UI for module management

---

## 📁 Module Structure

Each module lives in its own directory under `/modules/`. Here's the basic structure:

```
modules/
└── your-module-name/
    ├── module.json          # Required: Module metadata
    ├── module.php           # Required: Main module code
    ├── install.php          # Optional: Installation script
    ├── uninstall.php        # Optional: Uninstallation script
    ├── admin.php            # Optional: Admin interface
    └── assets/              # Optional: CSS, JS, images
        ├── style.css
        └── script.js
```

---

## 🚀 Creating a Module

### Requirements

- **Module ID** must be unique, lowercase, and use hyphens (example: `my-module`)
- **module.json** and **module.php** are required
- **module.json** must include an `id` field that matches the folder name
- **PHP** version should satisfy your `requires.php` constraint

### Step 1: Create Module Directory

Create a new directory in `/modules/` with a unique name (use lowercase and hyphens). The folder name must match the `id` in `module.json`:

```
modules/my-awesome-module/
```

### Step 2: Create module.json

This file contains your module's metadata:

```json
{
    "id": "my-awesome-module",
    "name": "My Awesome Module",
    "description": "This module does amazing things!",
    "version": "1.0.0",
    "author": "Your Name",
    "requires": {
        "php": ">=7.4"
    },
    "features": [
        "Feature 1",
        "Feature 2"
    ]
}
```

**Required fields:**
- `id` - Unique module ID (must match folder name)
- `name` - Display name of your module
- `description` - What your module does
- `version` - Semantic version number
- `author` - Your name or organization

### Step 3: Create module.php

This is the main file that runs when your module is active:

```php
<?php
/**
 * My Awesome Module
 */

// Initialization function (called when module loads)
function module_init_my_awesome_module() {
    // Setup code here
    // Create config files, set defaults, etc.
}

// Example hook function
function module_hook_my_awesome_module_page_content($data) {
    // This hook is called when page content is rendered
    return [
        'extra_content' => 'Hello from my module!'
    ];
}

// Custom functions
function my_awesome_module_do_something() {
    return "Module is working!";
}
```

**Naming convention:**
- Initialization: `module_init_{module_id}`
- Hooks: `module_hook_{module_id}_{hook_name}`
- Custom functions: `{module_id}_{function_name}`

Replace hyphens with underscores in function names.

---

## ⚙️ Module Configuration

### module.json Fields

```json
{
    "name": "Required - Display name",
    "description": "Required - Module description",
    "version": "Required - SemVer (1.0.0)",
    "author": "Required - Author name",
    "requires": {
        "php": ">=7.4",
        "modules": ["other-module"]
    },
    "features": [
        "List of features",
        "Shown in admin panel"
    ],
    "homepage": "https://example.com",
    "license": "MIT"
}
```

---

## 🔗 Hooks System

Hooks allow your module to integrate with the CMS at specific points.

### Available Hooks

1. **page_content** - Modify page content
2. **page_meta** - Add meta tags
3. **page_head** - Add code to `<head>`
4. **page_body** - Add code to `<body>`
5. **before_render** - Before page renders
6. **after_render** - After page renders

### Using Hooks

```php
// Hook into page head (add CSS/JS)
function module_hook_my_module_page_head($data) {
    return '<script src="/modules/my-module/assets/script.js"></script>';
}

// Hook into page content
function module_hook_my_module_page_content($data) {
    return [
        'sidebar_widget' => '<div class="widget">Hello!</div>'
    ];
}

// Hook into page meta tags
function module_hook_my_module_page_meta($pageData) {
    return [
        'og:title' => 'Custom Title',
        'og:description' => 'Custom Description'
    ];
}
```

### Executing Hooks in Templates

From your CMS templates, execute hooks like this:

```php
<?php
// Get module hook results
$moduleHooks = $moduleManager->executeHook('page_head', $pageData);
foreach ($moduleHooks as $moduleId => $result) {
    echo $result;
}
?>
```

---

## 📦 Installation & Uninstallation

### Uploading Your Module (ZIP)

You can upload a custom module from the admin panel:

1. Zip your module folder (example: `my-awesome-module/`)
2. Make sure the ZIP contains `module.json` at the module root
3. Go to **Admin → Modules** and upload the ZIP
4. Install and activate the module from the module list

**ZIP layout examples:**

- ✅ `my-awesome-module/module.json` (preferred)
- ✅ `module.json` at the ZIP root (single-folder ZIP also works)
- ❌ Missing `module.json` (upload will fail)

### install.php

Called when the module is installed:

```php
<?php
/**
 * Installation script
 */

function module_install_my_module() {
    // Create config file
    $config = [
        'enabled' => true,
        'api_key' => ''
    ];
    
    $configFile = __DIR__ . '/config.json';
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    
    // Create database tables (if needed)
    // Setup default settings
    // etc.
    
    return true; // Return false on failure
}
```

### uninstall.php

Called when the module is uninstalled:

```php
<?php
/**
 * Uninstallation script
 */

function module_uninstall_my_module() {
    // Remove config file
    $configFile = __DIR__ . '/config.json';
    if (file_exists($configFile)) {
        unlink($configFile);
    }
    
    // Clean up database tables
    // Remove settings
    // etc.
    
    return true;
}
```

---

## 💾 Storing Module Data

### Config Files

Store module configuration in JSON files:

```php
// Save settings
function my_module_save_settings($settings) {
    $file = __DIR__ . '/settings.json';
    file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT));
}

// Load settings
function my_module_get_settings() {
    $file = __DIR__ . '/settings.json';
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return [];
}
```

### Using CMS Data Directory

For shared data, use the CMS data directory:

```php
$dataFile = __DIR__ . '/../../data/my-module-data.json';
```

---

## ✨ Best Practices

### 1. Naming Conventions

- **Module ID**: lowercase-with-hyphens
- **Function names**: Replace hyphens with underscores
- **File names**: Lowercase, descriptive

### 2. Error Handling

Always handle errors gracefully:

```php
function module_init_my_module() {
    try {
        // Your code
    } catch (Exception $e) {
        error_log("My Module Error: " . $e->getMessage());
        return false;
    }
}
```

### 3. Dependencies

Check for required functions/extensions:

```php
function module_init_my_module() {
    if (!function_exists('curl_init')) {
        error_log("My Module requires cURL extension");
        return false;
    }
}
```

### 4. Performance

- Load resources only when needed
- Cache expensive operations
- Use lazy loading

### 5. Security

- Sanitize all user input
- Validate and escape output
- Use secure file permissions
- Never trust external data

---

## 📚 Examples

### Example 1: Simple Counter Module

**module.json:**
```json
{
    "name": "Page View Counter",
    "description": "Counts page views",
    "version": "1.0.0",
    "author": "Your Name"
}
```

**module.php:**
```php
<?php
function module_init_page_counter() {
    $counterFile = __DIR__ . '/counter.json';
    if (!file_exists($counterFile)) {
        file_put_contents($counterFile, json_encode(['count' => 0]));
    }
}

function module_hook_page_counter_page_body($data) {
    $counterFile = __DIR__ . '/counter.json';
    $counter = json_decode(file_get_contents($counterFile), true);
    $counter['count']++;
    file_put_contents($counterFile, json_encode($counter));
    
    return '<!-- Views: ' . $counter['count'] . ' -->';
}
```

### Example 2: Custom Header Module

**module.json:**
```json
{
    "name": "Custom Header",
    "description": "Adds custom header to all pages",
    "version": "1.0.0",
    "author": "Your Name"
}
```

**module.php:**
```php
<?php
function module_hook_custom_header_page_head($data) {
    return '
    <style>
        .custom-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
    </style>
    ';
}

function module_hook_custom_header_page_content($data) {
    return [
        'header' => '<div class="custom-header"><h1>Welcome to Our Site!</h1></div>'
    ];
}
```

---

## 🛠️ Module Manager API

### PHP Functions

```php
// Load module system
require_once __DIR__ . '/includes/ModuleManager.php';
$moduleManager = new ModuleManager();

// Discover all modules
$modules = $moduleManager->discoverModules();

// Get module info
$info = $moduleManager->getModuleInfo('module-id');

// Install module
$result = $moduleManager->installModule('module-id');

// Activate module
$result = $moduleManager->activateModule('module-id');

// Deactivate module
$result = $moduleManager->deactivateModule('module-id');

// Uninstall module
$result = $moduleManager->uninstallModule('module-id');

// Load active modules
$moduleManager->loadActiveModules();

// Execute hook
$results = $moduleManager->executeHook('hook_name', $data);

// Check if module is active
$isActive = $moduleManager->isModuleActive('module-id');
```

---

## 🎨 Admin Interface

Access module management at:

```
/admin/modules.php
```

Features:
- View all available modules
- Install/uninstall modules
- Activate/deactivate modules
- View module information
- See installation status

---

## 📝 Troubleshooting

### Module Not Appearing

1. Check `module.json` syntax (must be valid JSON)
2. Ensure module directory name is lowercase-with-hyphens
3. Verify file permissions
4. Check error logs

### Module Not Loading

1. Verify module is installed AND activated
2. Check for PHP errors in module.php
3. Ensure function names follow naming convention
4. Review error logs

### Hooks Not Working

1. Verify hook function name matches pattern
2. Ensure module is active
3. Check that hook is being executed in template
4. Debug with error_log()

---

## 🎉 Next Steps

1. Study the example modules in `/modules/`
2. Create your first module
3. Test installation and activation
4. Add hooks to integrate with CMS
5. Share your modules with the community!

---

## 📖 Additional Resources

- SimplePHP Documentation
- Module Examples
- Community Forum
- GitHub Repository

---

**Happy Module Building! 🚀**
