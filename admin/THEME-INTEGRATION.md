# Professional Admin Theme - Integration Guide

## Quick Start

Your new professional admin theme has been installed! Here's everything you need to know to get started.

## 📁 What's Included

```
admin/
└── theme/
    ├── base-layout.php              # Main layout template (REQUIRED)
    ├── form-elements-example.php    # Form examples & reference
    ├── README.md                    # Theme documentation
    ├── assets/
    │   ├── css/
    │   │   └── admin-theme.css      # Complete stylesheet
    │   └── js/
    │       └── admin-theme.js       # JavaScript functionality
    
# Additional new pages
admin/
├── login-new.php                    # New professional login page
├── index.php                        # Updated dashboard (created)
├── users-example.php                # User management example
```

## 🚀 Getting Started

### Step 1: Test the Theme

1. **View NEW Login Page:**
   - Navigate to: `http://localhost/simplephp/admin/login-new.php`
   - Demo credentials: `admin` / `admin`
   - This shows the professional login interface

2. **View Form Examples:**
   - Access: `http://localhost/simplephp/admin/theme/form-elements-example.php`
   - Shows all available form elements and layouts

3. **View User Management Example:**
   - Access: `http://localhost/simplephp/admin/users-example.php`
   - Shows how to use the theme with real page content

### Step 2: Update Your Existing Admin Pages

To use the new theme in your existing admin pages, follow this pattern:

**Template:**
```php
<?php
// Set base URL
$base_url = dirname(dirname(__FILE__));

// Page configuration
$page_title = 'Page Title';
$page_header = [
    'title' => 'Page Title',
    'subtitle' => 'Optional description',
    'action' => [
        'text' => 'Button Text',
        'url' => '/admin/path',
        'icon' => 'icon-name'
    ]
];

// Optional breadcrumb
$breadcrumb = [
    ['text' => 'Section', 'url' => '#', 'active' => false],
    ['text' => 'Current', 'url' => '#', 'active' => true]
];

// Include base layout
include __DIR__ . '/theme/base-layout.php';
?>

<!-- Your page content goes here -->
<div class="container-fluid">
    <!-- Content -->
</div>
```

### Step 3: Customize Navigation

Edit the sidebar navigation in `theme/base-layout.php`:

Find the `<ul class="nav-menu">` section and modify menu items:

```php
<ul class="nav-menu">
    <li class="nav-item">
        <a href="<?php echo $base_url; ?>/admin/" class="nav-link">
            <i data-feather="home"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="<?php echo $base_url; ?>/admin/your-page.php" class="nav-link">
            <i data-feather="icon-name"></i>
            <span>Menu Label</span>
        </a>
    </li>
</ul>
```

**Available Feather Icons:** https://feathericons.com/

## 📖 Common Tasks

### Create a New Admin Page

```php
<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$base_url = dirname(dirname(__FILE__));
$page_title = 'My Page';
$page_header = [
    'title' => 'My Page Title',
    'subtitle' => 'Subtitle goes here'
];

include __DIR__ . '/theme/base-layout.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Card Title</h5>
                </div>
                <div class="card-body">
                    <!-- Your content -->
                </div>
            </div>
        </div>
    </div>
</div>
```

### Add a Form

```html
<form class="form-default" method="POST" action="/admin/save.php">
    <div class="form-group">
        <label for="name" class="form-label required">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    
    <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email">
    </div>
    
    <div class="btn-group mt-20">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="reset" class="btn btn-outline-secondary">Reset</button>
    </div>
</form>
```

### Add a Table

```html
<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>Column 1</th>
                <th>Column 2</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Data 1</td>
                <td>Data 2</td>
                <td>
                    <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### Display Messages

**In PHP:**
```php
$_SESSION['message'] = 'Success message';
$_SESSION['message_type'] = 'success'; // success, info, danger, warning

// Then redirect or display page
```

**In JavaScript:**
```js
AdminTheme.showSuccessMessage('Item saved successfully!');
AdminTheme.showErrorMessage('An error occurred!');
```

### Form Validation

```js
// Add data-required to inputs
<input type="text" class="form-control" data-required>

// Validate form
const form = document.querySelector('form');
if (AdminTheme.validateForm(form)) {
    // Form is valid
}
```

## 🎨 Styling Classes

### Layout
- `.container-fluid` - Full width container
- `.row` - Bootstrap row
- `.col-lg-12` - Column sizing
- `.mb-24` - Margin bottom (24px)
- `.mt-20` - Margin top (20px)
- `.gap-20` - Gap between elements

### Cards
- `.card` - Card wrapper
- `.card-header` - Card header
- `.card-title` - Card title
- `.card-body` - Card content
- `.card-footer` - Card footer

### Forms
- `.form-group` - Form field wrapper
- `.form-label` - Label styling
- `.form-control` - Input styling
- `.form-select` - Select dropdown
- `.form-label.required` - Adds required asterisk
- `.form-control-lg` - Large input
- `.form-control-sm` - Small input
- `.form-check` - Checkbox/radio wrapper
- `.form-check-input` - Checkbox/radio input
- `.form-check-label` - Checkbox/radio label

### Buttons
- `.btn` - Base button
- `.btn-primary` - Blue button
- `.btn-secondary` - Gray button
- `.btn-success` - Green button
- `.btn-danger` - Red button
- `.btn-outline-primary` - Outlined button
- `.btn-sm` - Small button
- `.btn-lg` - Large button
- `.btn-block` - Full width button
- `.btn-group` - Button container with gap

### Alerts
- `.alert` - Alert wrapper
- `.alert-info` - Info alert (blue)
- `.alert-success` - Success alert (green)
- `.alert-danger` - Error alert (red)
- `.alert-warning` - Warning alert (orange)
- `.alert-dismissible` - Dismissible alert
- `.fade .show` - Animation classes

### Badges
- `.badge` - Base badge
- `.badge-primary` - Blue badge
- `.badge-success` - Green badge
- `.badge-danger` - Red badge
- `.badge-warning` - Orange badge
- `.badge-info` - Blue info badge

### Text
- `.text-muted` - Gray text
- `.text-primary` - Blue text
- `.text-success` - Green text
- `.text-danger` - Red text
- `.text-warning` - Orange text

### Tables
- `.table-wrapper` - Wrapper for responsive tables
- `.table` - Table styling

### Display
- `.d-flex` - Flexbox
- `.d-grid` - Grid layout
- `.justify-content-between` - Space between
- `.align-items-center` - Center vertically
- `.gap-10` - Gap 10px

## 🔐 Security Notes

1. **Authentication:** Update the login logic in `login-new.php`
   - Replace demo credentials with actual user verification
   - Check against your users database

2. **Session Management:** Verify sessions are properly handled
   - Check `session_start()` is called at top
   - Verify authentication on each page

3. **Input Validation:** Always validate user input
   - Sanitize with `htmlspecialchars()`
   - Use prepared statements for database queries
   - Validate data types and formats

## 🎯 Customization

### Change Colors

Edit `admin/theme/assets/css/admin-theme.css`:

```css
:root {
    --color-primary: #4680ff;      /* Change this */
    --color-success: #2ed8b6;
    --color-danger: #ff5370;
    --color-warning: #ffa502;
}
```

### Change Fonts

Add at top of CSS file:

```css
@import url('https://fonts.googleapis.com/css2?family=YourFont:wght@400;600&display=swap');

body {
    font-family: 'YourFont', sans-serif;
}
```

### Add Custom CSS

After including `admin-theme.css`:

```html
<link rel="stylesheet" href="/admin/theme/assets/css/admin-theme.css">
<link rel="stylesheet" href="/admin/your-custom.css">
```

## 📱 Responsive Behavior

- **Desktop (1200px+):** Full sidebar (260px)
- **Tablet (768px - 991px):** Narrower sidebar (220px)
- **Mobile (<768px):** Collapsible sidebar (toggle button)
- **Small Mobile (<576px):** Optimized touch interface

## 🔗 Available Icons

All icons from https://feathericons.com/ are available:

```html
<i data-feather="home"></i>
<i data-feather="users"></i>
<i data-feather="file-text"></i>
<i data-feather="settings"></i>
<i data-feather="plus"></i>
<i data-feather="edit-2"></i>
<i data-feather="trash-2"></i>
<i data-feather="check"></i>
<i data-feather="x"></i>
<i data-feather="arrow-right"></i>
```

Browse all at: https://feathericons.com/

## 🐛 Troubleshooting

### Sidebar not showing
- Check that `base-layout.php` is properly included
- Verify CSS file path is correct
- Clear browser cache

### Icons not displaying
- Verify Feather Icons CDN is accessible
- Check browser console for errors
- Ensure `feather.replace()` is called

### Form validation not working
- Add `data-required` attribute to inputs
- Ensure `form[data-validate]` is on form element
- Check JavaScript console for errors

### Buttons not styled correctly
- Use `.btn` class with `.btn-primary` (or other color)
- Don't forget to include Bootstrap CSS

### Colors look different
- Check CSS variables are loading
- Clear cache and reload
- Check browser DevTools for applied styles

## 📚 Resources

- **Bootstrap 5:** https://getbootstrap.com
- **Feather Icons:** https://feathericons.com
- **HTML/CSS:** https://developer.mozilla.org
- **PHP Documentation:** https://www.php.net/docs

## 📝 Next Steps

1. ✅ Test the theme by visiting the example pages
2. ✅ Update your existing admin pages to use the new theme
3. ✅ Customize colors and fonts to match your brand
4. ✅ Replace demo authentication with real logic
5. ✅ Add your actual pages and content

## 💡 Tips

- Keep page titles concise and descriptive
- Use consistent breadcrumbs for navigation
- Group related form fields together
- Use alerts for important messages
- Test on mobile devices
- Use meaningful icon names
- Keep card titles short and clear

---

**For more information, see:** `admin/theme/README.md`

Questions? Check the theme documentation or review the example files!
