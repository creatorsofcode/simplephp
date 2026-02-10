SimplePHP – Flat-File Mini CMS
================================

SimplePHP is a small flat‑file CMS built for very simple brochure sites. All content lives in a JSON file, there is a minimal admin panel, and a design editor where you can paste full HTML5/CSS/JS templates (e.g. from ChatGPT) without breaking the backend logic.<br>
<a href='https://simplephp.howtomakefreewebsite.com/'>SimplePHP DEMO</a>
---

## 1. Requirements

- PHP **8.1+** (works fine with 8.2)
- Web server (Apache via XAMPP is fine)
- `sendmail`/SMTP configured if you want the contact form to actually send email

Folder layout (inside your web root, e.g. `C:\xampp\htdocs\flatphp`):

- `index.php` – public site (frontend)
- `admin/` – admin panel (no routing, pure PHP)
- `data/content.json` – site settings, menu, pages
- `data/users.json` – admin users (username → password hash)
- `images/` – uploaded images
- `contact-form.js` – AJAX contact form logic
- `send-email.php` – contact form backend handler

---

## 2. First run & default admin login

1. Place this project under your web root, e.g.:
   - `C:\xampp\htdocs\flatphp` (Windows / XAMPP)
2. Start Apache/PHP and open in browser:
   - Frontend: `http://localhost/flatphp/`
   - Admin: `http://localhost/flatphp/admin/login.php`
3. Default admin user:
   - **Username**: `admin`
   - **Password**: `admin123`
4. After first login, it is strongly recommended to:
   - Go to **Users** tab
   - Change the password for `admin` to something strong

Admin session is stored in `$_SESSION['admin_logged_in']` and `$_SESSION['admin_username']`.

---

## 3. Content model (`data/content.json`)

`content.json` has three main parts:

- `site` – global info:
  - `title`, `description`, `phone`, `email`
- `menu` – array of menu items:
  - `id`: string, used in URL (`?page=id`)
  - `label`: string shown in navigation
  - `type`: `"page"` or `"link"`
  - `url`: for `"link"` items only
  - `order`: numeric sort order
- `pages` – keyed by `id` (e.g. `home`, `services`, `about`, `contact`):
  - Each page can have arbitrary fields (`title`, `intro`, `content`, arrays like `features`, `services`, `steps`, etc.).
  - The frontend is flexible and renders these dynamically.

You normally **edit this via admin**, not by hand.

---

## 4. Admin panel overview

Open `admin/index.php` (after login). The top tab bar:

- **Site** – basic site settings:
  - Title, description, phone, email
- **Menu** – manage navigation:
  - Add items (pages or external links)
  - See list of menu items
  - Edit / delete items using modals
- **Page tabs** – each page from the menu appears as a tab:
  - Dynamic form generated from `pages[pageId]`
  - Textareas use Quill rich text editor for long fields
- **Design** – full design editor (HTML / CSS / JS)
- **Users** – admin user management:
  - Add users
  - Change passwords (via modal)
  - Delete users (cannot delete yourself)

All saves end up in `data/content.json` or `data/users.json`.

---

## 5. Design editor (HTML / CSS / JavaScript)

Go to **Admin → Design**.

There you have:

- **Template HTML (optional override)**:
  - If empty → the **default blue/white design** is used.
  - If NOT empty → your HTML completely wraps the site.
  - You can paste a full HTML5 template (e.g. from ChatGPT).
- **Custom CSS**:
  - Injected inside `<style>` in the `<head>`.
- **Custom JavaScript**:
  - Injected via `<script>…</script>` at the end of `body`.
- **Notes** (optional):
  - For your own documentation, not used by the system.

### 5.1 Template placeholders

When `Template HTML` is set, `index.php` replaces these placeholders:

- `{{SITE_TITLE}}` – `site.title`
- `{{PAGE_TITLE}}` – current page title (fallback `site.title`)
- `{{DESCRIPTION}}` – `site.description`
- `{{NAV}}` – rendered menu `<a>` links from `data.menu`
- `{{CONTENT}}` – rendered content of current page (default layout, including cards, contact form, etc.)
- `{{CUSTOM_CSS}}` – content from **Custom CSS** field
- `{{CUSTOM_JS}}` – content from **Custom JavaScript** field
- `{{DEFAULT_CONTACT_FORM_SCRIPT}}` – `<script src="contact-form.js" defer></script>`

Example minimal template you can paste:

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{PAGE_TITLE}} – {{SITE_TITLE}}</title>
  <meta name="description" content="{{DESCRIPTION}}">
  <style>
  {{CUSTOM_CSS}}
  </style>
</head>
<body>
  <header>
    <h1>{{SITE_TITLE}}</h1>
    <nav>
      {{NAV}}
    </nav>
  </header>

  <main>
    {{CONTENT}}
  </main>

  <script>
  {{CUSTOM_JS}}
  </script>
  {{DEFAULT_CONTACT_FORM_SCRIPT}}
</body>
</html>
```

> If you break the template (e.g. remove `{{CONTENT}}`), the backend still works but nothing is shown – just reset design (see below).

### 5.2 Reset to default design

In **Design** tab there is a button:

- **Reset to Default Design**
  - Clears `design.template_html`, `design.custom_css`, `design.custom_js`, `design.template_notes` from `content.json`.
  - Frontend falls back to the built‑in default design (blue gradient background, white cards, responsive layout), with everything working out of the box.

This is the fastest way to “fix” the site if a custom template goes wrong.

---

## 6. Contact form (AJAX)

On the **Contact** page, the frontend renders a form (if `contact` page has `form_title` etc.).

- Frontend:
  - HTML in `index.php` (`renderMainHtml`) outputs the form.
  - JavaScript is in `contact-form.js` (loaded by default, or via `{{DEFAULT_CONTACT_FORM_SCRIPT}}` in custom templates).
  - Client-side validation (name/email/message) shows custom error messages (no native HTML5 messages).
- Backend:
  - `send-email.php` reads `data/content.json`:
    - Tries `pages.contact.email`, otherwise `site.email`.
  - Validates data again and calls `mail()` with `From`/`Reply-To` from user email.
  - Returns JSON:
    - `{"success": true, "message": "..."}`
    - or `{"success": false, "errors": { field: "message", ... }}`

If mail sending fails (server not configured), frontend will show a generic error, but the site itself is still fine.

---

## 7. Security notes

- Change the default `admin123` password immediately in production.
- Protect `/admin` with additional HTTP auth if you put this on a public server.
- Do not keep obvious passwords in the UI (we only show login link in frontend footer, not credentials).
- Custom HTML/CSS/JS is trusted admin content – only give admin access to people you trust.

---

## 8. Putting the project on GitHub

Your GitHub account: **`creatorsofcode.com`**  
Repository name: **`simplephp`**

From the project root (`flatphp`), run these commands in a terminal **on your machine**:

```bash
# 1. Initialize git repo (if not already)
git init

# 2. Add all files
git add .

# 3. Commit
git commit -m "Initial SimplePHP commit"

# 4. Add GitHub remote (HTTPS)
git remote add origin https://github.com/creatorsofcode/simplephp.git

# 5. Push to GitHub main branch
git branch -M main
git push -u origin main
```

> If the repo already exists on GitHub, just make sure the URL matches and run steps 2–5.

After this, you’ll have the whole SimplePHP project in `https://github.com/creatorsofcode/simplephp` with this `README.md` as documentation.

