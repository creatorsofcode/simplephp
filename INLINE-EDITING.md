# Inline Editing System

SimplePHP now includes a powerful inline editing system that allows you to edit content directly on the frontend when logged in as admin.

## Features

- **Visual Editing**: Click any element to edit it directly on the page
- **Rich Text Toolbar**: Format text with bold, italic, underline, headings
- **Add New Content**: Double-click containers to add new sections
- **Persistent Edit Mode**: Edit mode stays active across page navigation
- **Auto-Save to JSON**: Changes are saved to `content.json` with automatic backups
- **Live Preview**: See changes immediately as you edit
- **Safe & Secure**: Only available when logged in as admin

## How to Use

### 1. Login as Admin
First, login to the admin panel at `/admin/login.php`

### 2. Visit the Frontend
Go to the main site (`index.php` or just `/`)

### 3. Enable Edit Mode
Look for the **purple edit button** in the bottom-right corner of the screen. Click it to open the edit panel, then click "Start Editing".

**Note**: Edit mode **stays active** when you navigate to other pages. This allows you to edit multiple pages without re-enabling edit mode each time.

### 4. Edit Content across pages
- Edit mode persists as you navigate between pages
- **Edit Existing Content**: Click any highlighted element (shown with dashed outline) to edit it
- **Edit Card Elements**: Click directly on card titles or text (not the card background)
- **Format Text**: Use the toolbar to apply bold, italic, underline, or change headings
- **Add New Content**: Double-click on containers (shown with dotted outline) to add new sections
- **Save Changes**: Click the ✓ Save button on the toolbar after editing each element

### 5. Save and Finish
- Click **"Save All Changes"** to persist all edits to the server
- Click **"Done Editing"** button to exit edit mode when completely finished
- Edit mode will remain active until you explicitly click "Done Editing"

## Editable Elements

The following content areas are editable:

### Site-Wide
- Site title (in header and footer)
- Site phone number (footer)
- Site email address (footer)
- Demo note text
- Login info text

### Home Page
- Hero title (`pages.home.hero_title`)
- Hero text (`pages.home.hero_text`)
- CTA button text (`pages.home.cta_button`)
- Features title (`pages.home.features_title`)
- Each feature card:
  - Title (`pages.home.features.0.title`)
  - Text (`pages.home.features.0.text`)

### Contact Page
- Page title
- Intro text
- Phone number
- Email address
- Address
- Contact form title

### Services/About Pages
- Page title (`pages.{page}.title`)
- Page intro (`pages.{page}.intro`)
- Page content (`pages.{page}.content`)
- Each service/feature card:
  - Title (`pages.{page}.services.0.title`)
  - Text (`pages.{page}.services.0.text`)

### All Card Elements
Every card element on the website is individually editable, including:
- Card titles
- Card descriptions/text
- Card content

**Note**: Click on the specific text within a card to edit it, not the card background.

## Technical Details

### Files Created
- `inline-editor.js` - Core inline editing JavaScript
- `inline-editor.css` - Styles for the edit interface
- `save-inline-edit.php` - AJAX endpoint for saving changes

### Frontend Integration
The inline editor is automatically loaded when:
1. Admin is logged in (`$_SESSION['admin_logged_in'] === true`)
2. Visiting the frontend

### Data Attributes
Elements are marked as editable using data attributes:

```html
<!-- Editable element -->
<h1 data-editable="site.title">My Site</h1>

<!-- Container for adding new elements -->
<div data-editable-container="main-content">
  ...
</div>
```

### Security Features
- **Session Validation**: Only admins can save changes
- **HTML Sanitization**: Dangerous tags and scripts are stripped
- **Automatic Backups**: Previous versions saved to `data/content.backup.*.json`
- **Backup Rotation**: Keeps last 5 backups automatically

### Allowed HTML Tags
The following HTML tags are allowed in edited content:
- Text: `<p>`, `<br>`, `<strong>`, `<em>`, `<u>`
- Headings: `<h1>`, `<h2>`, `<h3>`, `<h4>`, `<h5>`, `<h6>`
- Lists: `<ul>`, `<ol>`, `<li>`
- Links & Media: `<a>`, `<img>`
- Containers: `<span>`, `<div>`, `<blockquote>`
- Code: `<code>`, `<pre>`

## Toolbar Buttons

| Button | Function |
|--------|----------|
| **B** | Bold text |
| **I** | Italic text |
| **U** | Underline text |
| **H1** | Format as Heading 1 |
| **H2** | Format as Heading 2 |
| **P** | Format as Paragraph |
| **✓ Save** | Save current element |
| **✗ Cancel** | Cancel and restore original |

## Edit Panel Features

### Status Indicators
- **Edit Mode**: Shows ON (green) or OFF (red)
- **Change Counter**: Shows number of unsaved changes

### Action Buttons
- **Enable Editing**: Toggle edit mode on/off
- **Save All Changes**: Persist all changes to server
- **Cancel All**: Discard all unsaved changes

## Keyboard Shortcuts

While editing an element:
- **Ctrl+B**: Bold
- **Ctrl+I**: Italic
- **Ctrl+U**: Underline
- **Enter**: New paragraph
- **Shift+Enter**: Line break

## Notifications

The system shows notifications for:
- ✅ **Success**: Changes saved successfully
- ❌ **Error**: Failed to save changes
- ℹ️ **Info**: Edit mode toggled, changes cancelled

## Backup System

### Automatic Backups
Every time you save changes, the previous version is automatically backed up to:
```
data/content.backup.YYYY-MM-DD_HH-MM-SS.json
```

### Backup Rotation
- Keeps the last **5 backups**
- Older backups are automatically deleted
- Manual restoration: Copy backup file to `content.json`

### Restore from Backup
1. Go to `data/` folder
2. Find the backup file you want to restore
3. Copy its contents to `content.json`
4. Reload the frontend

## Customization

### Add More Editable Elements
In your PHP template, add `data-editable` attribute:

```php
<h2 data-editable="pages.about.subtitle">
  <?= htmlspecialchars($pageData['subtitle']) ?>
</h2>
```

### Add Editable Containers
For areas where users can add new content:

```php
<section data-editable-container="custom-section">
  <!-- Users can double-click here to add content -->
</section>
```

### Customize Toolbar
Edit `inline-editor.js` in the `createEditToolbar()` function to add or remove buttons.

### Change Styles
Edit `inline-editor.css` to customize:
- Button colors and position
- Panel appearance
- Highlight colors
- Notification styles

## Best Practices

1. **Save Frequently**: Click "Save All" regularly to avoid losing work
2. **Test Changes**: Preview your edits before saving
3. **Keep Backups**: Download important versions of `content.json`
4. **Use Semantic HTML**: Format headings as H2, H3, etc., not just bold text
5. **Avoid Inline Styles**: Use the toolbar formatting instead
6. **Edit Card Elements Individually**: Click on specific text within cards (title or description) rather than the entire card
7. **Navigate While Editing**: Edit mode stays on, so you can edit multiple pages in one session

## Troubleshooting

### Edit Button Not Showing
- Make sure you're logged in as admin
- Check browser console for JavaScript errors
- Verify `inline-editor.js` and `inline-editor.css` files exist

### Changes Not Saving
- Check that `data/content.json` is writable (permissions)
- Look for error messages in notifications
- Check browser Network tab for AJAX errors
- Verify you're still logged in (session valid)

### Elements Not Editable
- Ensure element has `data-editable` attribute
- Check that edit mode is enabled (panel shows ON)
- Verify element is within the editable areas

### Content Looks Wrong After Edit
- Use "Cancel All" to restore original content
- Or restore from a backup file in `data/` folder
- Edit the `content.json` file directly if needed

## Browser Compatibility

The inline editor works in:
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

Requires JavaScript enabled.

## Security Considerations

1. **Always logout** when done editing
2. **Change default admin password** (admin123)
3. **Don't edit from public computers**
4. **Review changes** before saving
5. **Keep backups** of important content

## API Reference

### JavaScript API

If you need to interact with the inline editor programmatically:

```javascript
// Access the editor instance
const editor = window.inlineEditor;

// Toggle edit mode
editor.toggleEditMode();

// Save all changes
editor.saveAllChanges();

// Cancel all changes
editor.cancelAllChanges();

// Show notification
editor.showNotification('Custom message', 'success');
```

### Save Endpoint

**URL**: `/save-inline-edit.php`  
**Method**: POST  
**Content-Type**: application/json

**Request Body**:
```json
{
  "site": { ... },
  "pages": { ... },
  "menu": [ ... ],
  "design": { ... }
}
```

**Response**:
```json
{
  "success": true,
  "message": "Content saved successfully",
  "backup": "content.backup.2026-02-24_14-30-00.json"
}
```

## Future Enhancements

Potential features for future versions:
- Image upload and editing
- Drag-and-drop element reordering
- Undo/redo functionality
- Revision history
- Multi-user editing with conflict detection
- Preview mode before saving
- Mobile-friendly editing interface

---

**Happy Editing!** 🎨✨

For more information, see the main [README.md](README.md) or visit the [admin panel](admin/).
