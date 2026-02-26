# SimplePHP CMS - Module System 🧩

Welcome to the SimplePHP Module System! This powerful extension framework allows you to add new features to your CMS without modifying core files.

## 🚀 Quick Start

### Accessing Module Manager

1. Log into your admin panel at `/admin/`
2. Click on the **🧩 Modules** button in the navigation
3. Browse available modules and manage installations

### Demo Modules Included

Your installation includes 4 example modules to get you started:

#### 1. **Hello World Module** 👋
- Simple demonstration module
- Shows basic module structure
- Perfect starting point for learning

#### 2. **SEO Manager** 📈
- Meta tags management
- Open Graph support
- Twitter Card integration
- Sitemap generation

#### 3. **Analytics Tracker** 📊
- Google Analytics integration
- Facebook Pixel support
- Custom tracking codes
- Built-in visit counter

#### 4. **Backup Manager** 💾
- Automatic content backups
- One-click restore
- Backup history
- Download backups

## 📖 Creating Your Own Modules

### Basic Module Structure

```
modules/
└── your-module-name/
    ├── module.json       # Module metadata
    ├── module.php        # Main module code
    ├── install.php       # Installation script
    └── uninstall.php     # Cleanup script
```

### Minimal module.json

```json
{
    "name": "My Custom Module",
    "description": "What my module does",
    "version": "1.0.0",
    "author": "Your Name"
}
```

### Minimal module.php

```php
<?php
// Initialization
function module_init_my_custom_module() {
    // Setup code
}

// Hook example
function module_hook_my_custom_module_page_content($data) {
    return ['message' => 'Hello from my module!'];
}
```

## 🎯 Key Features

- **✅ Auto-Discovery** - Modules are automatically detected
- **✅ Easy Installation** - One-click install/uninstall
- **✅ Activation Control** - Enable/disable without uninstalling
- **✅ Hook System** - Integrate with CMS core
- **✅ Beautiful Admin UI** - Modern, responsive interface
- **✅ Safe Operations** - Installation scripts with error handling

## 📚 Documentation

Full documentation available in [MODULES.md](MODULES.md)

Topics covered:
- Module structure and configuration
- Hook system and available hooks
- Installation and uninstallation scripts
- Best practices and security
- Complete API reference
- Troubleshooting guide

## 🛠️ Module Manager API

### Basic Usage

```php
// Initialize
require_once 'includes/ModuleManager.php';
$moduleManager = new ModuleManager();

// Load active modules
$moduleManager->loadActiveModules();

// Execute hooks
$results = $moduleManager->executeHook('page_content', $data);
```

### Available Methods

- `discoverModules()` - Find all available modules
- `installModule($id)` - Install a module
- `activateModule($id)` - Activate installed module
- `deactivateModule($id)` - Deactivate module
- `uninstallModule($id)` - Uninstall module
- `executeHook($name, $data)` - Run hook in active modules

## 🎨 Admin Interface

Access the beautiful module management interface:

```
http://your-site.com/admin/modules.php
```

Features:
- 📊 Dashboard with statistics
- 🎴 Card-based module display
- 🔍 Module information and features
- ⚡ Quick action buttons
- ✨ Real-time status updates
- 🎯 Color-coded status badges

## 📦 Module Installation States

1. **Not Installed** (Orange badge)
   - Module discovered but not installed
   - Can install with one click

2. **Installed** (Blue badge)
   - Module installed but not active
   - Can activate or uninstall

3. **Active** (Green badge)
   - Module running and functional
   - Can deactivate or uninstall

## 🔌 Available Hooks

Modules can hook into these system events:

- `page_content` - Modify page content
- `page_meta` - Add meta tags
- `page_head` - Add code to `<head>`
- `page_body` - Add code to `<body>`
- `before_render` - Before page render
- `after_render` - After page render
- `content_saved` - When content is saved

## 🌟 Best Practices

1. **Use descriptive module IDs** - lowercase-with-hyphens
2. **Provide clear descriptions** - Help users understand your module
3. **Handle errors gracefully** - Return false on installation failure
4. **Clean up on uninstall** - Remove files and settings
5. **Test thoroughly** - Install, activate, deactivate, uninstall
6. **Version your modules** - Use semantic versioning

## 🎁 Example: Quick Module

Create a simple "Coming Soon" banner module:

**modules/coming-soon-banner/module.json:**
```json
{
    "name": "Coming Soon Banner",
    "description": "Shows a coming soon banner on all pages",
    "version": "1.0.0",
    "author": "Your Name"
}
```

**modules/coming-soon-banner/module.php:**
```php
<?php
function module_hook_coming_soon_banner_page_head($data) {
    return '<style>
        .coming-soon-banner {
            background: #ff6b6b;
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: bold;
        }
    </style>';
}

function module_hook_coming_soon_banner_page_content($data) {
    return [
        'banner' => '<div class="coming-soon-banner">🚀 New features coming soon!</div>'
    ];
}
```

That's it! Install and activate through the admin panel.

## 🐛 Troubleshooting

**Module not showing up?**
- Check module.json syntax (valid JSON)
- Ensure directory name is lowercase-with-hyphens
- Verify file permissions

**Module won't activate?**
- Check for PHP errors in module.php
- Ensure it's installed first
- Review error logs

**Hooks not working?**
- Verify function naming: `module_hook_{id}_{hookname}`
- Check module is active
- Make sure hook is being called in template

## 📞 Support

- 📖 Read [MODULES.md](MODULES.md) for detailed documentation
- 🔍 Check example modules for reference
- 🐛 Review error logs for issues
- 💡 Study the ModuleManager class

## 🎉 Get Started!

1. Visit `/admin/modules.php`
2. Install the demo modules
3. Activate them to see how they work
4. Create your own module
5. Share with the community!

---

**Happy module building! 🚀**

Built with ❤️ for SimplePHP CMS
