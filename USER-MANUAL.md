# SimplePHP User Manual 📖

A comprehensive guide for using SimplePHP – a flat-file CMS for creating beautiful websites without coding.

---

## 📑 Table of Contents

1. [Getting Started](#getting-started)
2. [Admin Panel Overview](#admin-panel-overview)
3. [Managing Your Site](#managing-your-site)
4. [Managing Pages](#managing-pages)
5. [Managing Your Menu](#managing-menu)
6. [Inline Editing](#inline-editing)
7. [Design & Themes](#design--themes)
8. [User Management](#user-management)
9. [Frequently Asked Questions](#frequently-asked-questions)
10. [Troubleshooting](#troubleshooting)

---

## Getting Started

### Installation & Initial Setup

#### Step 1: Download SimplePHP

Download or clone SimplePHP from Github to your web server directory:
- Windows (XAMPP): `C:\xampp\htdocs\simplephp`
- Mac/Linux: `/var/www/html/simplephp` or `/home/user/public_html/simplephp`

#### Step 2: Start Your Web Server

- **Windows (XAMPP)**: Start Apache and MySQL from XAMPP Control Panel
- **Mac/Linux**: Start Apache/PHP and MySQL services

#### Step 3: Access SimplePHP

Open your browser and navigate to:
- **Frontend**: `http://localhost/simplephp/`
- **Admin Panel**: `http://localhost/simplephp/admin/login.php`

#### Step 4: Login with Default Credentials

| Field | Value |
|-------|-------|
| **Username** | `admin` |
| **Password** | `admin123` |

⚠️ **IMPORTANT**: Change your password immediately after first login!

---

## Admin Panel Overview

The SimplePHP admin panel is organized into **tabs** at the top for easy navigation.

### Main Tabs

| Tab | Purpose |
|-----|---------|
| **Site** | Configure global settings (title, description, contact info) |
| **Menu** | Manage navigation menu items |
| **Design** | Edit the HTML/CSS/JavaScript of your website |
| **Themes** | Switch between pre-made themes or create custom ones |
| **Modules** | Install and configure system extensions |
| **Users** | Add/edit/delete admin user accounts |
| **Page tabs** | Edit individual pages (added dynamically) |

### Dashboard

When you log in, you see the **Dashboard** showing:
- Welcome message
- Quick links to main features
- Recent activity (if enabled)
- System status

---

## Managing Your Site

### Basic Site Settings

The **Site** tab lets you configure core website information:

#### Site Settings Form

1. **Site Title**
   - Display name of your website
   - Shows in browser tab and search results
   - Example: "My Awesome Business"

2. **Site Description**
   - Brief tagline or description
   - Appears below title on homepage
   - Keep it concise (50-100 characters)

3. **Contact Information**
   - **Phone**: Your business phone number
   - **Email**: Contact email address
   - Used in footer and contact forms

#### Saving Changes

Click **Save** at the bottom of the form. A notification will appear confirming your changes were saved.

### Sample Data

SimplePHP includes sample pages and content to help you get started:

- **Home Page**: Welcome message and site intro
- **About**: Your business information
- **Services**: List of services offered
- **Contact**: Contact form and information

Feel free to edit these or delete them to create your own pages!

---

## Managing Pages

### Creating a New Page

#### Step 1: Add a Menu Item

1. Click the **Menu** tab
2. Scroll to "Add New Menu Item"
3. Fill in:
   - **Label**: Text shown in navigation (e.g., "Blog", "Gallery")
   - **Type**: Select "Page"
   - **Slug/ID**: Unique identifier (lowercase, no spaces, e.g., "blog")
   - **Order**: Number to control menu position (lower numbers appear first)

4. Click **Add Item**

#### Step 2: Edit the Page

A new tab will appear with your page name. Click it and:

1. Add your content using the form fields
2. Rich text fields support formatting (bold, italic, headings)
3. Add images by uploading them
4. For complex layouts, use the **Design** tab

### Editing Existing Pages

#### Method 1: Via Admin Panel

1. Locate the page tab you want to edit
2. Click the tab to open the page
3. Modify fields as needed
4. Click **Save**

#### Method 2: Inline Editing (On Frontend)

1. Log into admin panel
2. Close the admin panel or open the front-end
3. Click any text element to edit it directly
4. Changes save automatically

### Deleting a Page

1. Click the **Menu** tab
2. Find the page in the "Menu Items" list
3. Click the **Delete** button
4. Confirm deletion

The page content will be removed, but you can restore from backups if needed.

### Page Structure

Each page can contain:

- **Title**: Main heading
- **Description**: Subtitle or intro text
- **Content**: Main body text (supports rich formatting)
- **Sections**: Special content blocks (images, features, team members, etc.)
- **Custom Fields**: Additional data specific to your page

---

## Managing Menu

The **Menu** tab controls your website's navigation.

### Understanding Menu Items

Menu items are links that appear in your site's navigation bar. Each item can be:

1. **A Page**: Links to a page within SimplePHP
2. **An External Link**: Links to external websites

### Adding Menu Items

#### Add a Page Link

1. Go to **Menu** tab
2. Scroll to "Add New Menu Item" form
3. Enter:
   - **Label**: Text shown in menu (e.g., "Home", "About Us")
   - **Type**: Select "Page"
   - **Slug**: Page ID (must match an existing page)
   - **Order**: Position in menu (1, 2, 3, etc.)
4. Click **Add Item**

#### Add an External Link

1. Go to **Menu** tab
2. Scroll to "Add New Menu Item" form
3. Enter:
   - **Label**: Text shown in menu (e.g., "Blog", "Partner Site")
   - **Type**: Select "Link"
   - **URL**: Full web address (e.g., `https://myblog.com`)
   - **Order**: Position in menu
4. Click **Add Item**

### Reordering Menu Items

Change the "Order" number for any item:
- Lower numbers = appear first in menu
- Higher numbers = appear later

Example order:
- Home: 1
- About: 2
- Services: 3
- Contact: 4

### Editing Menu Items

1. Find the item in the menu list
2. Click **Edit**
3. Change label, type, or order
4. Click **Save**

### Removing Menu Items

1. Find the item in the menu list
2. Click **Delete**
3. Confirm when prompted

**Note**: Deleting a menu item doesn't delete the page itself – the page just won't appear in navigation.

---

## Inline Editing

SimplePHP includes powerful **Inline Editing** – edit content directly on your website!

### Enabling Inline Editing

1. Log into the admin panel (bottom right corner)
2. The admin panel opens
3. Your website is now in edit mode

### Editing Content

#### Edit Text

1. Click any text on your website
2. An editing toolbar appears
3. Format using bold, italic, headings, etc.
4. Click outside to finish editing
5. Changes save automatically ✅

#### Edit Images

1. Click an image
2. Choose **Replace Image** or **Delete**
3. Upload a new image if replacing
4. Changes save automatically

#### Add New Content

1. Double-click an empty content area
2. Add new text or image
3. Changes save automatically

### Inline Editor Features

| Feature | How to Use |
|---------|-----------|
| **Bold** | Select text → Click **B** or Ctrl+B |
| **Italic** | Select text → Click *I* or Ctrl+I |
| **Heading 1** | Highlight text → Select from format menu |
| **Heading 2** | Highlight text → Select from format menu |
| **Heading 3** | Highlight text → Select from format menu |
| **Link** | Highlight text → Click link icon → Enter URL |
| **Undo** | Ctrl+Z |
| **Redo** | Ctrl+Y |
| **Save** | Click Save button (or auto-save) |

### Disabling Inline Editing

Click **Exit Edit Mode** in the admin panel to view your site normally.

---

## Design & Themes

### What are Themes?

Themes control the **look and feel** of your website – colors, fonts, layout, etc. SimplePHP includes:

- **Light Theme** ☀️ - Clean, bright appearance
- **Dark Theme** 🌙 - Easy on the eyes, perfect for night
- **Ocean Theme** 🌊 - Cool blue tones (disabled by default)

### Switching Themes

#### Method 1: Frontend Theme Panel (Easiest)

1. Log into the admin panel
2. Click the **🎨 Themes** tab
3. Browse available themes
4. Click **Activate** on a theme
5. Your website reloads with the new theme!

#### Method 2: Backend Admin

1. Go to `http://localhost/simplephp/admin/themes.php`
2. Click on a theme
3. Click **Activate**
4. Refresh your website

### Creating Custom Themes

#### Create a New Theme

1. Go to **Themes** tab in admin
2. Scroll to "Create New Theme"
3. Fill in:
   - **Theme Name**: Display name (e.g., "Corporate Blue")
   - **Primary Color**: Main accent color (click color picker)
   - **Background Color**: Page background
   - **Text Color**: Default text color
   - **Theme ID**: Unique identifier (e.g., `corporate-blue`)
4. Check "Enable this theme immediately" if desired
5. Click **Create Theme**

Your theme is now available in the themes list!

### Customizing Theme Colors

1. Go to **Themes** tab
2. Find your theme
3. Click **Edit** (if available)
4. Adjust colors using the color pickers
5. Click **Save**

### Advanced Theme Customization

For developers: Edit theme files directly in `themes/` folder:

```
themes/
└── my-theme/
    ├── theme.json       # Theme metadata & colors
    └── theme.css        # Custom theme styles
```

---

## User Management

The **Users** tab lets you add, edit, and remove admin accounts.

### Adding a New User

1. Click the **Users** tab
2. Go to "Add New User" section
3. Fill in:
   - **Username**: Login name (e.g., `john_doe`)
   - **Password**: Strong password (mix of upper/lowercase, numbers, special chars)
   - **Confirm Password**: Re-enter password
4. Click **Add User**

### Changing a Password

1. Click the **Users** tab
2. Find the user in the "Users List"
3. Click **Change Password**
4. Enter new password (twice)
5. Click **Update Password**

### Deleting a User

1. Click the **Users** tab
2. Find the user in the "Users List"
3. Click **Delete**
4. Confirm when prompted

**Note**: You cannot delete yourself. Have another admin delete your account if needed.

### Security Best Practices

- ✅ Use strong passwords (12+ characters)
- ✅ Include uppercase, lowercase, numbers, and symbols
- ✅ Change passwords regularly
- ✅ Don't share login credentials
- ✅ Use unique usernames
- ✅ Log out when leaving your computer

---

## Modules

Modules extend SimplePHP with new features and functionality.

### Built-in Modules

SimplePHP includes several pre-installed modules:

1. **Analytics Tracker** 📊
   - Track visitor statistics
   - See page views and visitor info

2. **Backup Manager** 💾
   - Create automatic backups
   - Restore from previous backups

3. **SEO Manager** 🔍
   - Optimize for search engines
   - Configure meta tags and keywords

4. **Theme Customizer** 🎨
   - Create and manage custom themes
   - Edit theme colors without code

### Managing Modules

#### View All Modules

1. Click **Modules** tab
2. See list of all modules with status

#### Enable a Module

1. Locate the module
2. If greyed out, click **Install**
3. Click **Enable** button
4. Module is now active

#### Disable a Module

1. Locate the active module
2. Click **Disable**
3. Module features stop working

#### Configure a Module

For installed modules:
1. Find the module
2. Click **Configure** button
3. Adjust settings (varies by module)
4. Click **Save**

#### Remove a Module

1. Locate the module
2. Click **Uninstall**
3. Confirm
4. Module is removed

---

## Frequently Asked Questions

### Q: How do I change the site title?
**A:** Click the **Site** tab and modify "Site Title" in the form. Click Save.

### Q: Can I have multiple admin users?
**A:** Yes! Go to **Users** tab and click "Add New User". Each user needs a unique username and password.

### Q: How do I change colors?
**A:** Go to **Themes** tab, select a theme, and click **Edit** to change colors. Or create a new custom theme.

### Q: Can I restore deleted content?
**A:** SimplePHP automatically creates backups. See the **Backup Manager** module to restore previous versions.

### Q: How do I add my logo?
**A:** Upload your logo image to the `images/` folder, then edit it into the header via the **Design** tab or inline editing.

### Q: How do I publish new pages?
**A:** Create a menu item (pointing to a new page ID), then edit the page from the page tab. New pages are live immediately.

### Q: Can I password-protect pages?
**A:** This requires custom development. See the Design tab to add code-based authentication.

### Q: How do I add a blog?
**A:** Create a "Blog" page with multiple post sections. Use inline editing to add new posts quickly.

### Q: What's the difference between online and offline?
**A:** SimplePHP is always "live" – changes appear immediately on the website. To hide content, edit the page or delete the menu item.

### Q: How do I migrate to a new server?
**A:** Copy the entire `simplephp/` folder to the new server. Update file permissions if needed.

---

## Troubleshooting

### I can't login to the admin panel

**Problem**: Login fails or page shows error

**Solutions**:
1. Check username and password are correct (default: `admin` / `admin123`)
2. Verify PHP is running on your server
3. Check that `data/users.json` exists
4. Manually reset password using terminal (advanced)

### Pages aren't saving

**Problem**: After editing and clicking Save, changes don't appear

**Solutions**:
1. Refresh the page (Ctrl+R)
2. Check file permissions on `data/content.json` (should be writable)
3. Verify there's enough disk space
4. Check browser console for errors (F12)
5. Try a different browser

### Images won't upload

**Problem**: Upload fails or images don't appear

**Solutions**:
1. Check file size (images should be under 5MB)
2. Use common formats: JPG, PNG, GIF
3. Verify `images/` folder exists and is writable
4. Check file permissions (should be 755)
5. Increase PHP upload limit if needed

### Menu items don't show

**Problem**: Added menu items but they don't appear on site

**Solutions**:
1. Verify item "Order" numbers are correct (1, 2, 3, etc.)
2. Check that page/slug exists for page links
3. Refresh the website (Ctrl+R or Cmd+R)
4. Check browser cache (clear if needed)
5. Review the theme CSS – items might be hidden

### Theme colors not changing

**Problem**: Changed theme colors but site still looks the same

**Solutions**:
1. Verify theme is "Activated" (green checkmark)
2. Hard refresh browser (Ctrl+Shift+R)
3. Clear browser cache
4. Try a different browser
5. Check theme.json file is valid JSON

### Contact form not sending emails

**Problem**: Visitors submit contact form but emails don't arrive

**Solutions**:
1. Configure SMTP/sendmail on your server
2. Check PHP.ini settings for mail function
3. Verify email address in Site settings is correct
4. Check spam/junk folder
5. Contact your hosting provider about mail setup

### Can't delete users

**Problem**: Delete button is disabled or won't work

**Solutions**:
1. You cannot delete yourself – use another admin account
2. Verify user exists in users list
3. Check file permissions on `data/users.json`
4. Try logging out and back in

### Site slow or unresponsive

**Problem**: Website loading slowly or timing out

**Solutions**:
1. Check server CPU and memory usage
2. Verify PHP version is 8.1+
3. Optimize images (reduce file size)
4. Disable unnecessary modules
5. Contact hosting provider for server resources

### Getting 404 errors

**Problem**: Pages showing "not found" errors

**Solutions**:
1. Verify the page exists in Menu
2. Check the page slug is correct (no spaces, lowercase)
3. Verify .htaccess file exists (if using Apache)
4. Check web server is running
5. Verify SimplePHP folder is in correct location

---

## Tips & Best Practices

### ✅ Good Practices

- **Regular Backups**: Use the Backup Manager to create regular backups
- **Strong Passwords**: Use 12+ character passwords with mixed characters
- **Test Before Publishing**: Edit pages and preview before going live
- **Optimize Images**: Compress images to reduce page load time
- **Keep it Simple**: Too many features can confuse visitors
- **Update Regularly**: Keep your website current with fresh content
- **Mobile Testing**: View your site on phones and tablets
- **Security**: Change default passwords immediately

### ❌ Avoid

- ❌ Deleting content without backup
- ❌ Using weak or simple passwords
- ❌ Uploading huge images (use 72dpi web images)
- ❌ Too many colors or fonts
- ❌ Broken links (test links before publishing)
- ❌ Outdated information
- ❌ Sharing login credentials

---

## Support & Resources

### Need Help?

1. **Read the Documentation**: Check [INDEX.md](INDEX.md) for developer docs
2. **Check FAQs**: See Frequently Asked Questions section above
3. **Review Settings**: Open `data/content.json` to understand file structure
4. **Contact Support**: Visit [https://github.com/creatorsofcode/simplephp](https://github.com/creatorsofcode/simplephp)

### Additional Documentation

- **[THEME-SYSTEM.md](THEME-SYSTEM.md)** - Complete theme system guide
- **[INLINE-EDITING.md](INLINE-EDITING.md)** - Advanced inline editing features
- **[MODULES.md](MODULES.md)** - Module system documentation
- **[CONFIGURATION.md](CONFIGURATION.md)** - Module configuration guide

---

## Summary

Congratulations! You now know how to:

✅ Login and navigate the admin panel
✅ Create and manage pages
✅ Organize your menu
✅ Edit content inline on your website
✅ Change themes and customize colors
✅ Manage user accounts
✅ Install and configure modules
✅ Troubleshoot common issues

**Ready to build your website?** Start creating pages, customizing your theme, and adding your content!

---

**SimplePHP © 2026** | [GitHub](https://github.com/creatorsofcode/simplephp)
