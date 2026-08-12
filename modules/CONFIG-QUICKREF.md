# Module Configuration - Quick Reference

## Creating a Configuration File

Create `config.json` in your module directory:

```json
{
    "description": "Brief description of what can be configured",
    "fields": [
        {
            "name": "field_name",
            "label": "Label Shown to User",
            "type": "text",
            "help": "Help text",
            "default": "default value",
            "required": true
        }
    ]
}
```

## Field Types Cheat Sheet

```json
// Text Input
{"name": "title", "type": "text", "placeholder": "Enter text"}

// Email Input
{"name": "email", "type": "email", "required": true}

// URL Input
{"name": "website", "type": "url", "placeholder": "https://"}

// Number Input
{"name": "count", "type": "number", "default": "10"}

// Textarea
{"name": "description", "type": "textarea"}

// Dropdown Select
{
    "name": "mode",
    "type": "select",
    "options": {
        "option1": "Label 1",
        "option2": "Label 2"
    }
}

// Checkbox
{
    "name": "enabled",
    "type": "checkbox",
    "checkbox_label": "Enable this feature",
    "default": true
}

// Color Picker
{"name": "color", "type": "color", "default": "#667eea"}
```

## Reading Config in Module

```php
<?php
// Get all config
global $moduleManager;
$config = $moduleManager->getModuleConfig('your-module-id');

// Use values with defaults
$title = $config['title'] ?? 'Default Title';
$enabled = $config['enabled'] ?? false;
$color = $config['color'] ?? '#000000';

// Or get single value
$email = $moduleManager->getModuleConfigValue('your-module-id', 'email', 'default@example.com');
```

## Access Configuration Panel

1. Admin → Modules
2. Click **⚙️ Configure** on active module
3. Edit settings
4. Save

---

**See [CONFIGURATION.md](CONFIGURATION.md) for full documentation**
