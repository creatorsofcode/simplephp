# Professional Admin Theme - Quick Reference

## 🎯 Most Used Classes & Components

### Basic Page Structure

```php
<?php
session_start();
$base_url = dirname(dirname(__FILE__));
$page_title = 'Page Title';
$page_header = [
    'title' => 'Page Title',
    'subtitle' => 'Description',
    'action' => ['text' => 'Add', 'url' => '/admin/add', 'icon' => 'plus']
];
$breadcrumb = [
    ['text' => 'Section', 'url' => '#', 'active' => false],
    ['text' => 'Current', 'url' => '#', 'active' => true]
];
include __DIR__ . '/theme/base-layout.php';
?>
<div class="container-fluid">
    <!-- Content -->
</div>
```

### Card

```html
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Title</h5>
    </div>
    <div class="card-body">
        Content
    </div>
</div>
```

### Form - Default Layout

```html
<form class="form-default">
    <div class="form-group">
        <label for="field" class="form-label required">Label</label>
        <input type="text" class="form-control" id="field" required>
    </div>
    <div class="btn-group mt-20">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="reset" class="btn btn-outline-secondary">Cancel</button>
    </div>
</form>
```

### Form - Two Column Layout

```html
<form class="form-horizontal">
    <div class="form-group">
        <div>
            <label for="f1" class="form-label required">Field 1</label>
            <input type="text" class="form-control" id="f1" required>
        </div>
        <div>
            <label for="f2" class="form-label required">Field 2</label>
            <input type="text" class="form-control" id="f2" required>
        </div>
    </div>
</form>
```

### Table

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
                <td>Data</td>
                <td>Data</td>
                <td>
                    <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### Buttons

```html
<!-- Primary (Blue) -->
<button class="btn btn-primary">Primary</button>

<!-- Secondary (Gray) -->
<button class="btn btn-secondary">Secondary</button>

<!-- Success (Green) -->
<button class="btn btn-success">Success</button>

<!-- Danger (Red) -->
<button class="btn btn-danger">Danger</button>

<!-- Outlined -->
<button class="btn btn-outline-primary">Outlined</button>

<!-- Icon Button -->
<button class="btn btn-primary">
    <i data-feather="plus"></i> Add Item
</button>

<!-- Sizes -->
<button class="btn btn-sm">Small</button>
<button class="btn">Normal</button>
<button class="btn btn-lg">Large</button>
```

### Alerts

```html
<!-- Info -->
<div class="alert alert-info alert-dismissible fade show">
    Message <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Success -->
<div class="alert alert-success">Success message</div>

<!-- Error -->
<div class="alert alert-danger">Error message</div>

<!-- Warning -->
<div class="alert alert-warning">Warning message</div>
```

### Badges

```html
<span class="badge badge-primary">Primary</span>
<span class="badge badge-success">Success</span>
<span class="badge badge-danger">Danger</span>
<span class="badge badge-warning">Warning</span>
<span class="badge badge-info">Info</span>
```

### Form Elements

```html
<!-- Text Input -->
<div class="form-group">
    <label class="form-label required">Input Label</label>
    <input type="text" class="form-control" placeholder="Text...">
</div>

<!-- Email Input -->
<div class="form-group">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" placeholder="email@example.com">
</div>

<!-- Password Input -->
<div class="form-group">
    <label class="form-label">Password</label>
    <input type="password" class="form-control">
</div>

<!-- Textarea -->
<div class="form-group">
    <label class="form-label">Message</label>
    <textarea class="form-control" rows="5"></textarea>
</div>

<!-- Select -->
<div class="form-group">
    <label class="form-label">Choose</label>
    <select class="form-select">
        <option>Select option</option>
        <option>Option 1</option>
        <option>Option 2</option>
    </select>
</div>

<!-- Checkbox -->
<div class="form-check">
    <input type="checkbox" class="form-check-input" id="check" checked>
    <label class="form-check-label" for="check">Checkbox label</label>
</div>

<!-- Radio -->
<div class="form-check">
    <input type="radio" class="form-check-input" name="radio" id="r1" checked>
    <label class="form-check-label" for="r1">Option 1</label>
</div>

<!-- Input with Icon -->
<div class="input-group">
    <span class="input-group-text">$</span>
    <input type="number" class="form-control" placeholder="0.00">
</div>
```

### Color Classes for Text

```html
<p class="text-primary">Primary text</p>
<p class="text-success">Success text</p>
<p class="text-danger">Danger text</p>
<p class="text-warning">Warning text</p>
<p class="text-muted">Muted text</p>
```

### Spacing

```html
<!-- Margin Top -->
<div class="mt-5">5px</div>
<div class="mt-10">10px</div>
<div class="mt-15">15px</div>
<div class="mt-20">20px</div>

<!-- Margin Bottom -->
<div class="mb-5">5px</div>
<div class="mb-10">10px</div>
<div class="mb-15">15px</div>
<div class="mb-20">20px</div>

<!-- Gap Between Elements -->
<div class="d-flex gap-10">
    Item 1
    Item 2
</div>
```

### Flex & Grid

```html
<!-- Flexbox -->
<div class="d-flex justify-content-between align-items-center">
    <span>Left</span>
    <span>Right</span>
</div>

<!-- Grid (for buttons) -->
<div class="d-grid gap-2">
    <button class="btn btn-primary">Button 1</button>
    <button class="btn btn-primary">Button 2</button>
</div>
```

## JavaScript API

```js
// Show loading on button
AdminTheme.showButtonLoading(button);

// Hide loading
AdminTheme.hideButtonLoading(button);

// Show success message
AdminTheme.showSuccessMessage('Action completed!');

// Show error message
AdminTheme.showErrorMessage('Something went wrong!');

// Validate form
const form = document.querySelector('form');
if (AdminTheme.validateForm(form)) {
    // Form is valid, submit
}
```

## Common Icons

```html
<!-- Navigation -->
<i data-feather="home"></i>      <!-- Home -->
<i data-feather="users"></i>     <!-- Users -->
<i data-feather="file-text"></i> <!-- Content -->
<i data-feather="settings"></i>  <!-- Settings -->
<i data-feather="grid"></i>      <!-- Modules -->

<!-- Actions -->
<i data-feather="plus"></i>      <!-- Add -->
<i data-feather="edit-2"></i>    <!-- Edit -->
<i data-feather="trash-2"></i>   <!-- Delete -->
<i data-feather="check"></i>     <!-- Confirm -->
<i data-feather="x"></i>         <!-- Cancel -->

<!-- Status -->
<i data-feather="arrow-up"></i>     <!-- Up/Success -->
<i data-feather="arrow-down"></i>   <!-- Down/Error -->
<i data-feather="check-circle"></i> <!-- Success -->
<i data-feather="x-circle"></i>     <!-- Error -->

<!-- UI -->
<i data-feather="search"></i>    <!-- Search -->
<i data-feather="menu"></i>      <!-- Menu -->
<i data-feather="copy"></i>      <!-- Copy -->
<i data-feather="log-out"></i>   <!-- Logout -->
```

More icons: https://feathericons.com/

## Display Classes

```html
<!-- Hide/Show -->
<div style="display: none;">Hidden</div>

<!-- Text Alignment -->
<div class="text-center">Centered</div>
<div class="text-end">Right</div>

<!-- Width Control -->
<div style="max-width: 400px;">Limited width</div>
```

## Common Grid Layouts

### Two Column
```html
<div class="row">
    <div class="col-lg-6">Column 1</div>
    <div class="col-lg-6">Column 2</div>
</div>
```

### Three Column
```html
<div class="row">
    <div class="col-lg-4">Column 1</div>
    <div class="col-lg-4">Column 2</div>
    <div class="col-lg-4">Column 3</div>
</div>
```

### Four Column
```html
<div class="row">
    <div class="col-md-3">Column 1</div>
    <div class="col-md-3">Column 2</div>
    <div class="col-md-3">Column 3</div>
    <div class="col-md-3">Column 4</div>
</div>
```

### Sidebar + Content
```html
<div class="row">
    <div class="col-lg-3">Sidebar</div>
    <div class="col-lg-9">Main content</div>
</div>
```

## File Paths

```
CSS:  /admin/theme/assets/css/admin-theme.css
JS:   /admin/theme/assets/js/admin-theme.js
Base: /admin/theme/base-layout.php
```

---

**Quick Tips:**
- Always set `$page_title` and `$page_header` before including layout
- Use `data-required` attribute for form validation
- Wrap tables in `table-wrapper` div for responsive scrolling
- Use `.form-default` for vertical forms, `.form-horizontal` for two-column
- Add icons with `<i data-feather="icon-name"></i>`
- Group buttons with `btn-group` div
- Use `.badge` for status indicators
- Use `.alert` for messages and notifications
