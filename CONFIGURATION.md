# Module Configuration System Guide

## Overview

The SimplePHP Module System includes a powerful configuration interface that allows each module to define its own settings. Administrators can easily configure modules through a beautiful web interface without editing code.

## 🎯 Key Features

- **Auto-generated UI** - Configuration forms are created automatically from JSON schema
- **Multiple field types** - Text, email, URL, number, textarea, select, checkbox, color
- **Validation** - Built-in validation for required fields and field types
- **User-friendly** - Beautiful responsive interface with help text
- **Persistent storage** - Settings saved in modules.json
- **Easy access** - Configure button appears for active modules

## 📁 Configuration File Structure

Each module can have a `config.json` file that defines its configuration schema:

```
modules/
└── your-module/
    ├── module.json       # Module metadata
    ├── config.json       # Configuration schema (NEW!)
    ├── module.php        # Main module code
    ├── install.php       # Installation script
    └── uninstall.php     # Cleanup script
```

## 🔧 Creating a Configuration Schema

### Basic config.json Structure

```json
{
    "description": "Configure settings for this module",
    "fields": [
        {
            "name": "field_name",
            "label": "Field Label",
            "type": "text",
            "help": "Helpful description for users",
            "default": "default value",
            "required": true
        }
    ]
}
```

### Field Properties

| Property | Required | Description |
|----------|----------|-------------|
| `name` | Yes | Internal field name (used in code) |
| `label` | Yes | Display label shown to users |
| `type` | Yes | Field type (see below) |
| `help` | No | Help text shown under the field |
| `default` | No | Default value when first installed |
| `required` | No | If true, field must be filled |
| `placeholder` | No | Placeholder text for input fields |

## 📝 Available Field Types

### 1. Text Input

```json
{
    "name": "site_title",
    "label": "Site Title",
    "type": "text",
    "placeholder": "Enter your site title",
    "help": "The title of your website",
    "default": "My Website"
}
```

### 2. Email Input

```json
{
    "name": "contact_email",
    "label": "Contact Email",
    "type": "email",
    "placeholder": "admin@example.com",
    "help": "Email address for notifications",
    "required": true
}
```

### 3. URL Input

```json
{
    "name": "website_url",
    "label": "Website URL",
    "type": "url",
    "placeholder": "https://example.com",
    "help": "Full URL including https://"
}
```

### 4. Number Input

```json
{
    "name": "max_items",
    "label": "Maximum Items",
    "type": "number",
    "placeholder": "10",
    "help": "Maximum number of items to display",
    "default": "10"
}
```

### 5. Textarea

```json
{
    "name": "custom_code",
    "label": "Custom Code",
    "type": "textarea",
    "placeholder": "<script>/* Your code */</script>",
    "help": "Add custom HTML, CSS, or JavaScript here"
}
```

### 6. Select Dropdown

```json
{
    "name": "display_mode",
    "label": "Display Mode",
    "type": "select",
    "options": {
        "list": "List View",
        "grid": "Grid View",
        "compact": "Compact View"
    },
    "help": "Choose how content is displayed",
    "default": "list"
}
```

### 7. Checkbox

```json
{
    "name": "enable_feature",
    "label": "Enable Feature",
    "type": "checkbox",
    "checkbox_label": "Turn on this awesome feature",
    "help": "Enables the feature when checked",
    "default": true
}
```

### 8. Color Picker

```json
{
    "name": "theme_color",
    "label": "Theme Color",
    "type": "color",
    "placeholder": "#667eea",
    "help": "Pick a color for your theme",
    "default": "#667eea"
}
```

## 💻 Using Configuration in Your Module

### Reading Configuration Values

In your `module.php` file, use the ModuleManager to read configuration:

```php
<?php
// Get the module manager instance
global $moduleManager;

// Get all configuration for your module
$config = $moduleManager->getModuleConfig('your-module-id');

// Use configuration values
$greeting = $config['greeting_message'] ?? 'Hello!';
$color = $config['theme_color'] ?? '#000000';

// Or get a single value with default
$email = $moduleManager->getModuleConfigValue('your-module-id', 'contact_email', 'default@example.com');
```

### Example Module with Configuration

**modules/example-banner/config.json:**
```json
{
    "description": "Configure the banner displayed on your site",
    "fields": [
        {
            "name": "banner_text",
            "label": "Banner Text",
            "type": "text",
            "placeholder": "Welcome to our site!",
            "required": true
        },
        {
            "name": "banner_color",
            "label": "Background Color",
            "type": "color",
            "default": "#667eea"
        },
        {
            "name": "show_banner",
            "label": "Display Banner",
            "type": "checkbox",
            "checkbox_label": "Show banner on all pages",
            "default": true
        }
    ]
}
```

**modules/example-banner/module.php:**
```php
<?php
function module_hook_example_banner_page_head($data) {
    global $moduleManager;
    
    // Get configuration
    $config = $moduleManager->getModuleConfig('example-banner');
    
    // Check if banner should be shown
    if (!($config['show_banner'] ?? true)) {
        return '';
    }
    
    $text = $config['banner_text'] ?? 'Welcome!';
    $color = $config['banner_color'] ?? '#667eea';
    
    return "
    <style>
        .custom-banner {
            background: $color;
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: bold;
        }
    </style>
    ";
}

function module_hook_example_banner_page_content($data) {
    global $moduleManager;
    $config = $moduleManager->getModuleConfig('example-banner');
    
    if (!($config['show_banner'] ?? true)) {
        return [];
    }
    
    $text = htmlspecialchars($config['banner_text'] ?? 'Welcome!');
    
    return [
        'banner' => "<div class='custom-banner'>$text</div>"
    ];
}
```

## 🎨 Accessing the Configuration Panel

### From Modules Page

1. Go to `/admin/modules.php`
2. Find your active module
3. Click the **⚙️ Configure** button
4. Edit settings and click **Save Configuration**

### Direct URL

```
/admin/module-config.php?module=your-module-id
```

## 📊 Configuration Storage

Module configurations are stored in `/data/modules.json`:

```json
{
    "installed": ["module1", "module2"],
    "active": ["module1"],
    "config": {
        "module1": {
            "setting1": "value1",
            "setting2": true,
            "setting3": "#667eea"
        }
    }
}
```

## 🔒 Best Practices

### 1. Provide Clear Labels and Help Text

```json
{
    "name": "api_key",
    "label": "API Key",
    "type": "text",
    "help": "Get your API key from Settings → API in your dashboard",
    "required": true
}
```

### 2. Use Sensible Defaults

```json
{
    "name": "items_per_page",
    "type": "number",
    "default": "10"
}
```

### 3. Group Related Settings

Use clear descriptions to group settings:

```json
{
    "description": "Social Media Integration - Connect your social accounts",
    "fields": [...]
}
```

### 4. Validate Required Fields

```json
{
    "name": "admin_email",
    "type": "email",
    "required": true
}
```

### 5. Sanitize User Input

Always sanitize configuration values before using them:

```php
$userInput = htmlspecialchars($config['user_message'] ?? '');
```

### 6. Provide Examples in Placeholders

```json
{
    "placeholder": "e.g., https://example.com/image.jpg"
}
```

## 🧪 Complete Example: Contact Form Module

**modules/contact-form/config.json:**
```json
{
    "description": "Configure your contact form settings and email notifications",
    "fields": [
        {
            "name": "recipient_email",
            "label": "Recipient Email Address",
            "type": "email",
            "placeholder": "contact@example.com",
            "help": "Where contact form submissions will be sent",
            "required": true
        },
        {
            "name": "success_message",
            "label": "Success Message",
            "type": "textarea",
            "placeholder": "Thank you for contacting us!",
            "help": "Message shown after successful submission",
            "default": "Thank you! We'll get back to you soon."
        },
        {
            "name": "enable_captcha",
            "label": "Spam Protection",
            "type": "checkbox",
            "checkbox_label": "Enable CAPTCHA verification",
            "help": "Helps prevent spam submissions",
            "default": true
        },
        {
            "name": "button_color",
            "label": "Submit Button Color",
            "type": "color",
            "default": "#667eea"
        },
        {
            "name": "required_fields",
            "label": "Required Fields",
            "type": "select",
            "options": {
                "name_email": "Name & Email Only",
                "name_email_message": "Name, Email & Message",
                "all": "All Fields Required"
            },
            "default": "name_email_message"
        }
    ]
}
```

## 🐛 Troubleshooting

### Configuration Not Showing

- Ensure `config.json` exists in module directory
- Check JSON syntax is valid (use a JSON validator)
- Module must be **active** to configure

### Settings Not Saving

- Check file permissions on `/data/modules.json`
- Ensure user is logged into admin panel
- Check browser console for JavaScript errors

### Values Not Appearing in Module

```php
// Make sure to access the global module manager
global $moduleManager;

// Not: $this->moduleManager or new ModuleManager()
```

### Default Values Not Applied

Defaults are only used when reading, not stored:

```php
// Good - provides default
$value = $config['key'] ?? 'default';

// Bad - may be undefined
$value = $config['key'];
```

## 📚 Advanced Topics

### Dynamic Field Options

For select fields that need dynamic options, consider creating them in your module's admin page instead.

### Validation Beyond Required

For complex validation (regex, min/max, etc.), add JavaScript validation to a custom admin page.

### Conditional Fields

Currently not supported. Consider using multiple related fields with clear help text.

### File Uploads

Not directly supported in config. Use a custom admin page for file upload functionality.

## 🎓 Learning Resources

- Study the included demo modules in `/modules/`
- Check `seo-manager/config.json` for a complex example
- Review `hello-world/config.json` for a simple example
- Read the main [MODULES.md](../MODULES.md) guide

## 💡 Tips & Tricks

1. **Keep it simple** - Don't overwhelm users with too many options
2. **Test thoroughly** - Try all field types before deploying
3. **Document settings** - Use help text generously
4. **Provide defaults** - Make modules work out of the box
5. **Think mobile** - Configuration UI is responsive

---

**Happy Configuring! ⚙️**

The configuration system makes your modules powerful and user-friendly!
