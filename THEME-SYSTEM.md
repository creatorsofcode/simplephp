# SimplePHP Theme System 🎨

A powerful, modular theme management system that allows administrators to switch between different website themes from the frontend admin panel.

## 📁 System Structure

```
themes/
├── light/
│   ├── theme.json          # Theme metadata and colors
│   └── theme.css           # Theme styles
├── dark/
│   ├── theme.json
│   └── theme.css
└── ocean/
    ├── theme.json
    └── theme.css

data/
└── theme-config.json       # Active theme configuration

frontend-admin-themes.php   # Backend theme handler
```

## 🚀 Features

### ✅ Built-in Themes

1. **Light Theme** - Clean, bright appearance with white backgrounds
   - Primary: #3B82F6 (Blue)
   - Background: #FFFFFF
   - Best for daytime viewing

2. **Dark Theme** - Eye-friendly dark mode
   - Primary: #60A5FA (Light Blue)
   - Background: #1F2937 (Dark Gray)
   - Perfect for night viewing

3. **Ocean Theme** - Ocean-inspired cool colors (disabled by default)
    - Primary: #0284C7 (Deep Blue)
    - Background: #0F172A (Deep Navy)
    - Features cyan accents and glowing effects

### 🎛️ Admin Panel Integration

The theme manager is integrated into the frontend admin panel with:

- **🎨 Themes Tab** - Easy-to-use theme selector
- **Live Preview** - See colors before activating
- **One-Click Activation** - Switch themes instantly
- **Active Theme Display** - Shows current active theme
- **Enable/Disable** - Keep only approved themes available
- **Create Theme** - Build a custom theme from the admin panel
- **Responsive Grid** - Beautiful card-based layout

## 📋 How to Use

### For Administrators

1. **Open Admin Panel**
   - Click the "Admin" button (bottom right)

2. **Navigate to Themes**
    - Option A: Click the "🎨 Themes" tab in the frontend admin panel
    - Option B: Open **Admin → Themes** in the backend dashboard

3. **Select a Theme**
    - Browse available themes
    - View color preview and details
    - Click "Activate" button
    - Page automatically reloads with new theme

4. **Enable/Disable Themes**
    - Click "Enable" or "Disable" on any theme card
    - At least one theme must stay enabled

5. **Create a Theme**
    - Use the "Create New Theme" form
    - Choose colors and a slug (ID)
    - Optionally enable it immediately

4. **View Active Theme**
   - See current active theme in "Active Theme" section

### For Developers

#### Creating a New Theme

1. **Create Theme Directory**
```bash
mkdir themes/my-theme
```

2. **Create theme.json**
```json
{
    "name": "My Custom Theme",
    "description": "Description of your theme",
    "version": "1.0.0",
    "author": "Your Name",
    "thumbnail": "thumbnail.png",
    "colors": {
        "background": "#FFFFFF",
        "text": "#333333",
        "primary": "#3B82F6",
        "secondary": "#F3F4F6",
        "accent": "#1F2937"
    },
    "active": false
}
```

3. **Create theme.css**
```css
/* Ocean Theme */
:root {
    --theme-bg: #FFFFFF;
    --theme-text: #333333;
    --theme-primary: #3B82F6;
    --theme-secondary: #F3F4F6;
    --theme-accent: #1F2937;
    --theme-border: #E5E7EB;
}

body.theme-my-theme {
    background-color: var(--theme-bg);
    color: var(--theme-text);
}

/* Add your custom styles */
```

4. **Theme is Automatically Detected**
   - Place files in `themes/my-theme/`
   - Theme appears in admin panel immediately

## 📐 Theme Configuration

### theme.json Properties

| Property | Type | Description |
|----------|------|-------------|
| `name` | string | Display name in admin panel |
| `description` | string | Theme description |
| `version` | string | Theme version (semantic versioning) |
| `author` | string | Theme creator name |
| `thumbnail` | string | Thumbnail image path |
| `colors` | object | Color palette used by theme |
| `active` | boolean | Internal flag (managed by system) |

### Color Properties

```json
"colors": {
    "background": "#FFFFFF",    // Main background color
    "text": "#333333",          // Primary text color
    "primary": "#3B82F6",       // Primary accent color
    "secondary": "#F3F4F6",     // Secondary background
    "accent": "#1F2937"         // Accent highlights
}
```

## 🔧 API Reference

### Frontend Admin Methods

#### `loadThemes()`
Fetches all available themes and renders them in the theme selector.

#### `renderThemes(themes, activeTheme)`
Displays theme cards with preview and activation buttons.
- `themes` - Array of theme objects
- `activeTheme` - ID of currently active theme

#### `selectTheme(themeId)`
Activates a theme and saves configuration.
- `themeId` - Theme folder name

### Backend Endpoint

**File:** `frontend-admin-themes.php`

#### GET Request
```
GET /frontend-admin-themes.php?action=get_themes
```

Response:
```json
{
    "success": true,
    "themes": [
        {
            "id": "light",
            "name": "Light Theme",
            "description": "...",
            "colors": {...}
        }
    ],
    "active_theme": "light"
}
```

#### POST Request
```
POST /frontend-admin-themes.php
action=set_theme&theme_id=dark
```

Response:
```json
{
    "success": true,
    "message": "Theme activated successfully",
    "active_theme": "dark"
}
```

## 💅 Customizing Themes

### CSS Variables

Themes use CSS custom properties for flexibility:

```css
:root {
    --theme-bg: #FFFFFF;
    --theme-text: #333333;
    --theme-primary: #3B82F6;
    --theme-secondary: #F3F4F6;
    --theme-accent: #1F2937;
    --theme-border: #E5E7EB;
}
```

### Body Class Indicator

When a theme is active, the body tag gets a class:
```html
<body class="theme-dark">
```

Use this for theme-specific CSS:
```css
body.theme-dark .admin-panel {
    background-color: #1F2937;
    color: #F3F4F6;
}
```

## 📊 Theme Grid Display

The admin panel shows themes in a responsive grid:

- **Desktop:** 3 themes per row
- **Tablet:** 2 themes per row
- **Mobile:** 1 theme per row

### Theme Card Components

- **Preview Box** - Shows background color and preview text
- **Theme Info** - Name, description, and color swatches
- **Activation Button** - "Activate" or "✓ Active" (if current)

## 🔒 Security

- Admin authentication required to change themes
- Session validation on all theme operations
- Theme IDs validated against file system
- SQL injection safe (file-based system)
- HTML sanitization in theme paths

## 📝 Data Storage

### theme-config.json
```json
{
    "active_theme": "light"
}
```

This file stores the currently active theme name and enabled themes list. It's located in the `data/` directory and auto-created if missing.

```json
{
    "active_theme": "light",
    "enabled_themes": ["light", "dark"]
}
```

## 🎯 Future Enhancements

- [ ] Custom color picker for theme customization
- [ ] Theme preview modal before activation
- [ ] Theme upload/installation from ZIP files
- [ ] Per-page theme override
- [ ] Theme export functionality
- [ ] Animation and effect customization

## 📞 Support

For issues or questions about the theme system:

1. Check theme files are in `themes/[theme-name]/` directory
2. Ensure `theme.json` has all required properties
3. Verify `theme.css` uses proper CSS syntax
4. Check file permissions (755 for directories, 644 for files)
5. Look at browser console for JavaScript errors

## 📄 License

Part of SimplePHP CMS © 2026
