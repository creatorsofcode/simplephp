# Professional Admin Theme - Installation & Usage Guide

## Overview

This is a professional, modern admin theme inspired by **Star Admin 2** - a clean and responsive Bootstrap 5 admin template. The theme provides a complete theming system for your PHP admin interface with a sidebar navigation, professional forms, and a modern UI design.

## Features

✅ **Responsive Design** - Works perfectly on desktop, tablet, and mobile devices
✅ **Bootstrap 5 Integration** - Built on the latest Bootstrap framework
✅ **Feather Icons** - Beautiful, lightweight icon library
✅ **Professional Forms** - Multiple form layouts and styled form elements
✅ **Sidebar Navigation** - Fixed sidebar with smooth animations
✅ **Color Scheme** - Professional color palette with primary, success, danger, and warning colors
✅ **Accessibility** - WCAG compliant color contrasts and semantic HTML
✅ **Mobile Optimized** - Touch-friendly navigation and responsive tables

## File Structure

```
admin/
├── theme/
│   ├── base-layout.php           # Main layout template
│   ├── form-elements-example.php # Form examples
│   ├── assets/
│   │   ├── css/
│   │   │   └── admin-theme.css   # Main stylesheet
│   │   ├── js/
│   │   │   └── admin-theme.js    # JavaScript functionality
│   │   └── images/
│   └── components/
│       ├── header.php
│       ├── sidebar.php
│       └── footer.php
```

## Installation

### 1. Include the Base Layout

The new theme uses a base layout template that handles all common elements (sidebar, header, etc.). To use it in your admin pages:

```php
<?php
// Set page configuration BEFORE including the layout
$page_title = 'Page Title';
$page_header = [
    'title' => 'Page Title',
    'subtitle' => 'Optional subtitle',
    'action' => [
        'text' => 'Add Item',
        'url' => '/admin/add.php',
        'icon' => 'plus'
    ]
];

$breadcrumb = [
    ['text' => 'Section', 'url' => '/admin/section/', 'active' => false],
    ['text' => 'Current Page', 'url' => '#', 'active' => true]
];

// Include the base layout
$base_url = dirname(dirname(__FILE__));
include __DIR__ . '/theme/base-layout.php';
?>

<!-- Your page content here -->
<div class="container-fluid">
    <!-- Your content -->
</div>
```

### 2. CSS Classes

The theme provides extensive CSS classes for styling:

#### Cards
```html
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Card Title</h5>
    </div>
    <div class="card-body">
        <!-- Content -->
    </div>
    <div class="card-footer">
        <!-- Footer content -->
    </div>
</div>
```

#### Forms
```html
<form class="form-default">
    <div class="form-group">
        <label for="input" class="form-label required">Label</label>
        <input type="text" class="form-control" id="input" placeholder="Text...">
    </div>
</form>
```

#### Buttons
```html
<button class="btn btn-primary">Primary</button>
<button class="btn btn-secondary">Secondary</button>
<button class="btn btn-success">Success</button>
<button class="btn btn-danger">Danger</button>
<button class="btn btn-outline-primary">Outline</button>
```

#### Alerts
```html
<div class="alert alert-info alert-dismissible fade show">
    Info message
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

#### Input Sizes
```html
<input type="text" class="form-control form-control-lg" placeholder="Large input">
<input type="text" class="form-control" placeholder="Default input">
<input type="text" class="form-control form-control-sm" placeholder="Small input">
```

#### Badges
```html
<span class="badge badge-primary">Primary</span>
<span class="badge badge-success">Success</span>
<span class="badge badge-danger">Danger</span>
```

#### Tables
```html
<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>Column 1</th>
                <th>Column 2</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Data 1</td>
                <td>Data 2</td>
            </tr>
        </tbody>
    </table>
</div>
```

## Form Layouts

### Default Form
Single column form layout (vertical):
```html
<form class="form-default">
    <div class="form-group">
        <label for="field" class="form-label required">Field Label</label>
        <input type="text" class="form-control" id="field" data-required>
    </div>
</form>
```

### Horizontal Form (Two Column)
```html
<form class="form-horizontal">
    <div class="form-group">
        <div>
            <label for="field1" class="form-label required">Field 1</label>
            <input type="text" class="form-control" id="field1">
        </div>
        <div>
            <label for="field2" class="form-label required">Field 2</label>
            <input type="text" class="form-control" id="field2">
        </div>
    </div>
</form>
```

## JavaScript API

The theme provides a JavaScript API in `window.AdminTheme`:

```js
// Show loading state
AdminTheme.showButtonLoading(button);

// Hide loading state
AdminTheme.hideButtonLoading(button);

// Show success message
AdminTheme.showSuccessMessage('Success message');

// Show error message
AdminTheme.showErrorMessage('Error message');

// Validate form
if (AdminTheme.validateForm(form)) {
    // Submit form
}
```

## Color Scheme

| Color | Hex Code | Usage |
|-------|----------|-------|
| Primary | #4680ff | Main actions, active states |
| Primary Dark | #3565dd | Hover states |
| Success | #2ed8b6 | Success messages, positive actions |
| Danger | #ff5370 | Errors, destructive actions |
| Warning | #ffa502 | Warnings, caution messages |
| Info | #4680ff | Information messages |
| Light | #f8f9fa | Background colors |
| Dark | #343a40 | Text colors |

## Responsive Breakpoints

- **Desktop**: Full sidebar (260px)
- **Tablet (≤991px)**: Narrower sidebar (220px)
- **Mobile (≤768px)**: Collapsible sidebar (hidden by default)
- **Small Mobile (≤576px)**: Limited layout, optimized for touch

## Navigation Menu

To add items to the sidebar menu, edit the navigation in `base-layout.php`:

```php
<ul class="nav-menu">
    <li class="nav-item">
        <a href="/admin/" class="nav-link active">
            <i data-feather="home"></i>
            <span>Dashboard</span>
        </a>
    </li>
</ul>
```

Available Feather icons: https://feathericons.com/

## Page Headers with Actions

```php
$page_header = [
    'title' => 'Users',
    'subtitle' => 'Manage system users',
    'action' => [
        'text' => 'Add User',
        'url' => '/admin/users?action=new',
        'icon' => 'plus'
    ]
];
```

## Message Display

Messages are shown using SESSION variables:

```php
$_SESSION['message'] = 'Your message here';
$_SESSION['message_type'] = 'success'; // info, success, danger, warning
```

## Customization

### Custom Colors

Edit the CSS variables in `admin-theme.css`:

```css
:root {
    --color-primary: #4680ff;
    --color-success: #2ed8b6;
    --color-danger: #ff5370;
    /* ... etc */
}
```

### Custom Fonts

Add your font imports at the top of `admin-theme.css`:

```css
@import url('https://fonts.googleapis.com/css2?family=Your+Font');

body {
    font-family: 'Your Font', sans-serif;
}
```

## Examples

### User Management Page
See `admin-users-example.php` for a complete example with tables and forms.

### Form Elements
See `form-elements-example.php` for all available form elements and layouts.

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- Minimal CSS (< 30KB)
- No jQuery dependency
- Uses native Bootstrap 5
- Optimized icon library
- Pure JavaScript (no frameworks)

## License

This theme is provided for your SimplePHP project. Feel free to modify and customize as needed.

## Support

For issues or questions, refer to Bootstrap 5 documentation: https://getbootstrap.com
Feather Icons: https://feathericons.com/
