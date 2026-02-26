# 🎯 Module System - Frontend Integration

## ✨ Modules Now Work on the Frontend!

Your module system is now fully integrated with the frontend. Installed and activated modules automatically inject their content and functionality into your website.

---

## 🔗 Available Frontend Hooks

Modules can hook into these points on the frontend:

### 1. **page_meta** - Meta Tags
- Inject Open Graph tags, Twitter Cards, SEO meta tags
- Called in `<head>` section
- **Example use**: SEO Manager adds meta descriptions

### 2. **page_head** - Head Content
- Add CSS stylesheets, JavaScript files, fonts
- Called before `</head>`
- **Example use**: Theme Customizer adds custom styles

### 3. **page_body_start** - Body Start
- Add content right after `<body>` opens
- Perfect for banners, notifications
- **Example use**: Cookie consent banners

### 4. **page_content** - Main Content
- Add content after main page content, before footer
- **Example use**: Hello World module shows greeting banner

### 5. **page_body_end** - Body End
- Add scripts before `</body>` closes
- Perfect for analytics, tracking codes
- **Example use**: Analytics Tracker adds Google Analytics

---

## 🚀 Try It Now!

### Quick Test:

1. **Visit your admin panel**: `/admin/modules.php`

2. **Install "Hello World" module**:
   - Click **Install**
   - Click **Activate**
   - Click **⚙️ Configure**
   - Set your greeting message
   - Choose a color
   - Save

3. **Visit your homepage**: `/`
   - You'll see a beautiful colored banner with your greeting!

4. **Try Theme Customizer**:
   - Install & activate Theme Customizer
   - Configure colors, fonts, spacing
   - See your entire site's theme change!

5. **Enable Analytics Tracker**:
   - Install & activate Analytics Tracker
   - Add your Google Analytics ID
   - It starts tracking immediately!

---

## 📊 How It Works

### Backend (Admin)
```
/admin/modules.php → Install → Activate → Configure
```

### Frontend (Automatic)
```php
1. Module loads: module_init_yourmodule()
2. Hooks execute: module_hook_yourmodule_page_head()
3. Content displays automatically on frontend
```

---

## 🎨 Example: Hello World Module

When activated, the Hello World module:

1. **Injects CSS** via `page_head` hook:
   ```php
   function module_hook_hello_world_page_head($data) {
       return '<style>.banner { color: red; }</style>';
   }
   ```

2. **Shows Banner** via `page_content` hook:
   ```php
   function module_hook_hello_world_page_content($data) {
       return '<div class="banner">Hello!</div>';
   }
   ```

3. **Result**: Beautiful banner appears on every page!

---

## 🎯 Real-World Examples

### Analytics Tracking

**Module**: Analytics Tracker
**Hook**: `page_head` and `page_body_end`
**Result**: Google Analytics and Facebook Pixel automatically added to every page

### SEO Enhancement

**Module**: SEO Manager
**Hook**: `page_meta`
**Result**: Open Graph tags for social media sharing

### Visual Customization

**Module**: Theme Customizer
**Hook**: `page_head`
**Result**: Site-wide custom colors and fonts

### Content Injection

**Module**: Hello World
**Hook**: `page_content`
**Result**: Custom banners and notifications

---

## 🛠️ Creating Your Own Frontend Module

### 1. Create module structure:
```
modules/
└── my-banner/
    ├── module.json
    ├── module.php
    └── config.json
```

### 2. Add hook in module.php:
```php
<?php
function module_hook_my_banner_page_content($data) {
    global $moduleManager;
    $config = $moduleManager->getModuleConfig('my-banner');
    
    $text = $config['banner_text'] ?? 'Welcome!';
    
    return '<div class="my-banner">' . htmlspecialchars($text) . '</div>';
}
```

### 3. Install and activate in admin

### 4. Visit frontend - banner appears automatically! 🎉

---

## 📋 Hook Reference

| Hook Name | Position | Use Cases |
|-----------|----------|-----------|
| `page_meta` | `<head>` | SEO, Open Graph, Twitter Cards |
| `page_head` | Before `</head>` | CSS, JS, Fonts, Analytics setup |
| `page_body_start` | After `<body>` | Top banners, notifications |
| `page_content` | After main content | Widgets, banners, CTAs |
| `page_body_end` | Before `</body>` | Analytics, tracking, chat widgets |

---

## 💡 Tips & Tricks

### 1. Use Configuration
```php
global $moduleManager;
$config = $moduleManager->getModuleConfig('your-module-id');
$enabled = $config['show_banner'] ?? true;

if (!$enabled) return ''; // Respect user settings
```

### 2. Sanitize Output
```php
$userInput = htmlspecialchars($config['message']);
return "<div>$userInput</div>";
```

### 3. Check Dependencies
```php
if (!isset($_SESSION)) {
    session_start();
}
```

### 4. Return HTML Strings
```php
// Good
return '<div>Content</div>';

// Also good - for multiple items
return [
    'banner' => '<div>Banner</div>',
    'widget' => '<div>Widget</div>'
];
```

---

## 🎭 Live Examples

Visit your site to see modules in action:

### With Hello World Active:
- Colored greeting banner on every page
- Customizable message and color
- Optional timestamp

### With Analytics Tracker Active:
- Google Analytics tracking (invisible)
- Visit counter increments
- Facebook Pixel tracking

### With Theme Customizer Active:
- Custom site colors
- Different fonts
- Rounded corners
- Box shadows

### All Three Combined:
- Beautiful themed site
- Custom greeting
- Full analytics
- Professional appearance

---

## 🐛 Debugging

### Module not showing?

1. **Check it's activated** (not just installed)
2. **Verify hook name**:
   ```php
   function module_hook_MODULE_ID_HOOK_NAME($data)
   ```
3. **Check for errors**:
   ```php
   error_log("Module hook called");
   ```

### Content not appearing?

1. **Return a string or array** from hooks
2. **Check global $moduleManager** is accessible
3. **View page source** - look for module comments

---

## 🎓 Next Steps

1. ✅ Modules work on frontend
2. ✅ Configuration system ready
3. ✅ 5 demo modules included
4. 🚀 **Create your own module!**
5. 🎨 **Customize your site!**
6. 📊 **Track your visitors!**

---

## 📚 Documentation

- [MODULES.md](MODULES.md) - Complete module system guide
- [CONFIGURATION.md](CONFIGURATION.md) - Configuration system
- [MODULE-CONFIG-SYSTEM.md](MODULE-CONFIG-SYSTEM.md) - Config overview
- This file - Frontend integration

---

**Your module system is now fully operational! 🎉**

Install modules in admin → They work automatically on frontend!
