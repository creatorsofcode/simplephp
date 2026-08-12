SimplePHP – Flat-File Mini CMS
================================

SimplePHP is a small flat‑file CMS built for very simple brochure sites. All content lives in a JSON file, there is a minimal admin panel, and a design editor where you can paste full HTML5/CSS/JS templates (e.g. from ChatGPT) without breaking the backend logic.

- **Project URL**:(https://simplephp.org)
- **Live demo**:(https://simplephp.org/demo)
---

## 1. Requirements

- PHP **8.1+** (tested on 8.3), with the `dom`, `json`, `session`, and `filter` extensions (all enabled by default in a standard PHP build).
- A web server (Apache via WAMP/XAMPP is fine). No database is required.
- `sendmail`/SMTP configured if you want the contact form to actually send email.
- **HTTPS in production.** The app marks session cookies `Secure` automatically when it detects HTTPS, and sends `Strict-Transport-Security` only when HTTPS is detected - so plain HTTP still works for local development, but production deployments should sit behind TLS.

Folder layout (inside your web root, e.g. `C:\wamp64\www\simplephp`):

- `index.php` – public site (frontend)
- `admin/` – admin panel (no routing, pure PHP)
- `includes/Security.php` – shared security helpers (sessions, CSRF, rate limiting, atomic JSON storage, HTML sanitization, security headers) used by every entrypoint
- `includes/paths.php` – resolves the private data directory (see §2.1)
- `images/` – uploaded images
- `contact-form.js` – AJAX contact form logic
- `send-email.php` – contact form backend handler

### 1.1 Private data directory (was `data/`)

Everything that used to live in a web-accessible `data/` folder - `users.json` (password hashes), `content.json`, backups, rate-limit state - now lives **outside the web root**, since anything under the document root can potentially be served directly over HTTP regardless of application-level checks.

- **Environment variable**: set `SIMPLEPHP_DATA_DIR` to an absolute path outside any web-served directory. This is read once, on first request, via `getenv()`.
- **Default (no env var set)**: one directory *above* whatever `$_SERVER['DOCUMENT_ROOT']` resolves to (e.g. document root `/var/www/html` → data dir `/var/www/simplephp-data`). This is computed at runtime, never hardcoded to one OS/host, so it stays correct across dev/staging/prod without extra configuration.
- **Fail-closed guard**: on every request, `includes/paths.php` checks whether the resolved data directory would fall *inside* the document root (misconfiguration, symlink, changed vhost, etc.) and refuses to continue if so, rather than silently serving private data over HTTP.
- **Defense in depth**: even though the directory is meant to be unreachable via HTTP, the app also writes a `.htaccess` (`Require all denied` / `Deny from all`) and a `403`-returning `index.php` into it automatically, in case it's ever accidentally exposed by a misconfigured alias.

**You must set `SIMPLEPHP_DATA_DIR`** (or verify the computed default is actually outside your web root) before going to production. Verify with:

```bash
php -r "require 'includes/paths.php'; echo SIMPLEPHP_DATA_DIR, PHP_EOL;"
```

Then confirm that path is **not** reachable at `https://your-site/…`.

The web server process needs read/write permission on this directory. On Linux, e.g.:

```bash
sudo mkdir -p /var/www/simplephp-data
sudo chown www-data:www-data /var/www/simplephp-data
sudo chmod 750 /var/www/simplephp-data
```

---

## 2. First run & admin setup

1. Place this project under your web root, e.g.:
   - `C:\wamp64\www\simplephp` (Windows / WAMP)
2. Point `SIMPLEPHP_DATA_DIR` at a writable directory outside the web root (see §1.1), or verify the computed default is acceptable.
3. Start Apache/PHP and open in browser:
   - Frontend: `http://localhost/simplephp/`
   - Admin: `http://localhost/simplephp/admin/login.php`

### 2.1 Creating the first admin account

There is no hardcoded production password. `SIMPLEPHP_DATA_DIR/users.json` holds `{"username": {"hash": "...", "must_change_password": true}}` entries (a legacy plain-hash-string format is also accepted for backward compatibility, treated as `must_change_password: false`).

To create the very first admin account, generate a hash and write it directly into `users.json` (there is intentionally no web-exposed "create admin" endpoint before authentication exists):

```bash
php -r "echo password_hash('choose-a-strong-password-here', PASSWORD_DEFAULT), PHP_EOL;"
```

Then create `SIMPLEPHP_DATA_DIR/users.json`:

```json
{
  "admin": { "hash": "PASTE_THE_HASH_HERE", "must_change_password": true }
}
```

`must_change_password: true` forces the account to set its own new password via `admin/force-password-change.php` immediately after the first successful login - no other admin page is reachable until that's done. This is also how you should rotate credentials after any suspected exposure: flip that flag back to `true` for the affected account.

Never commit `users.json`, never log a plaintext password, and never paste a real password into an issue, PR, or this README.

Admin session state is stored only as `$_SESSION['admin_logged_in']`, `$_SESSION['admin_username']`, `$_SESSION['must_change_password']`, and the CSRF token - no password material or other sensitive data is kept in the session.

---

## 3. Content model (`content.json`)

`content.json` (in `SIMPLEPHP_DATA_DIR`) has three main parts:

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
- **Design** – full design editor (HTML / CSS / JS) - see §6 and the trust-model note in §6.2
- **Users** – admin user management:
  - Add users
  - Change passwords (via modal)
  - Delete users (cannot delete yourself)

All saves end up in `content.json` or `users.json` in `SIMPLEPHP_DATA_DIR`, written atomically (temp file + `rename()`) under an exclusive lock, so a failed write never leaves a half-written file as the active data.

Every admin form includes a CSRF token, generated with `random_bytes()` and validated with `hash_equals()` on every state-changing request; missing/invalid tokens are rejected. All admin pages require an authenticated session, checked centrally in `includes/Security.php` (`simplephp_require_admin_login()`), not by hiding UI elements.

---

## 5. Inline Editing

SimplePHP includes a powerful **inline editing system** that allows you to edit content directly on the frontend when logged in as admin.

### Features
- **Click to Edit**: Click any element to edit it in place (including all card elements)
- **Rich Text Toolbar**: Format with bold, italic, headings
- **Add New Sections**: Double-click containers to add content
- **Persistent Edit Mode**: Stays active across page navigation
- **Live Preview**: See changes immediately
- **Auto-Save**: Changes saved to JSON with automatic backups
- **Comprehensive Coverage**: Every text element on the site is editable (titles, cards, footer, etc.)

Saved HTML passes through an allowlist sanitizer (`simplephp_sanitize_html()` in `includes/Security.php`) before being written - see §6.2 for exactly what's allowed.

### How to Use
1. Login to admin panel
2. Visit the frontend homepage
3. Look for the purple **"Edit Mode"** button in bottom-right corner
4. Click to open panel, then "Start Editing"
5. Click any highlighted element to edit (edit mode stays on across pages)
6. Use toolbar to format text
7. Click "Save All Changes" when ready
8. Click "Done Editing" to exit edit mode

For detailed documentation, see [INLINE-EDITING.md](INLINE-EDITING.md).

---

## 6. Design editor (HTML / CSS / JavaScript)

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

### 6.1 Template placeholders

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

**Reset to default design**: the Design tab has a **Reset to Default Design** button that clears `design.template_html`, `design.custom_css`, `design.custom_js`, `design.template_notes` from `content.json`. Frontend falls back to the built‑in default design (blue gradient background, white cards, responsive layout), with everything working out of the box. This is the fastest way to "fix" the site if a custom template goes wrong.

### 6.2 Trust model: admin content vs. visitor content

This is important to understand before giving anyone admin access.

- **Custom CSS/JS is trusted administrator content, by design.** It is written only by authenticated admins (CSRF-protected, session-gated) and is rendered **unescaped** into `<style>`/`<script>` tags on the public site - that's the feature (site-wide theme/behavior customization). Anyone with admin credentials can run arbitrary JavaScript in every visitor's browser, exactly like they could edit a theme file in most CMSs. **Only give admin access to people you'd trust with that.**
- **Inline-edited page content (titles, text, cards, etc.) is treated as untrusted-ish even though only admins can save it** - it goes through the allowlist HTML sanitizer (`simplephp_sanitize_html()`) before being persisted, not because we distrust the admin, but so a compromised admin session (e.g. a stolen cookie) can't be used to plant a persistent script via the content-editing surface. Allowed tags: `p, br, strong, b, em, i, u, h1–h6, ul, ol, li, a, img, span, div, blockquote, code, pre`. Allowed attributes are scoped per tag (e.g. `a` gets `href/title/target`, `img` gets `src/alt/title/width/height`, `span`/`div` get `class`). `href`/`src` values are protocol-checked against an allowlist (`http`, `https`, `mailto`, `tel`); anything else - `javascript:`, `data:`, `vbscript:`, `file:`, etc. - is stripped. All `on*` event-handler attributes are stripped unconditionally. This does **not** apply to the Custom CSS/JS fields described above, which remain full-trust admin content by design.
- **Visitor-supplied content (contact form)** never reaches stored content or gets rendered as HTML anywhere - it's validated, escaped with `htmlspecialchars()`, and only ever used inside a plain-text email body/headers (with CR/LF stripped).

Because admin-authored inline `<script>`/`<style>` content must keep working, the CSP (see §8) includes `'unsafe-inline'` for `script-src`/`style-src` - it is not a full XSS mitigation on its own for that reason. The actual defenses against *untrusted* input are output escaping and the allowlist sanitizer described above, not CSP.

---

## 7. Contact form (AJAX)

On the **Contact** page, the frontend renders a form (if `contact` page has `form_title` etc.).

- Frontend:
  - HTML in `index.php` (`renderMainHtml`) outputs the form, including a hidden honeypot field (`name="website"`) that only bots fill in.
  - JavaScript is in `contact-form.js` (loaded by default, or via `{{DEFAULT_CONTACT_FORM_SCRIPT}}` in custom templates).
  - Client-side validation (name/email/message) shows custom error messages (no native HTML5 messages) - this is a UX convenience, not a security control; everything is re-validated server-side.
- Backend (`send-email.php`):
  - Honeypot check first: a filled `website` field gets a fake success response without sending anything or touching the rate limiter.
  - Rate-limited per IP (5 submissions / 10 minutes, atomically checked-and-recorded under a single file lock - no separate check-then-record race).
  - Validates name/email/message server-side (required, length limits, `FILTER_VALIDATE_EMAIL`).
  - Reads the recipient from `content.json`: tries `pages.contact.email`, otherwise `site.email`.
  - `From:` is always a server-controlled `no-reply@<host>` address - **never** the visitor's own address, which would let visitor input dictate an auth-sensitive header and fail SPF/DKIM checks on the receiving end. The visitor's (CR/LF-stripped) address goes in `Reply-To:` instead.
  - Returns generic JSON errors; PHP/mail-server warnings are never shown to the client.
  - Returns JSON: `{"success": true, "message": "..."}` or `{"success": false, "errors": {...}}` / `{"success": false, "errors": {"general": "..."}}`.

If mail sending fails (server not configured), frontend will show a generic error, but the site itself is still fine.

---

## 8. Security architecture

A summary of what's implemented, for anyone auditing or extending this app:

| Area | Mechanism |
|---|---|
| CSRF | `random_bytes()`-generated per-session token, `hash_equals()` verification, required on every admin POST (form field or `X-CSRF-Token` header for AJAX) |
| Session fixation | `session_regenerate_id(true)` after login; cookies are `HttpOnly`, `SameSite=Lax`, and `Secure` when HTTPS is detected |
| Brute force | Atomic, lock-held, **fail-closed** rate limiter (`simplephp_rate_limit_attempt()`) - per-IP and per-username, temporary lockout, no permanent ban based only on IP |
| Passwords | `password_hash()`/`password_verify()` (bcrypt via `PASSWORD_DEFAULT`); no hardcoded production password (see §2.1); forced password-change flag support |
| HTML sanitization | DOM-based allowlist sanitizer, not regex/`strip_tags` (see §6.2) |
| Private data | Outside the web root, portable path resolution, fail-closed misconfiguration guard, `.htaccess`/403-stub defense in depth (see §1.1) |
| Backups | Stored in `SIMPLEPHP_DATA_DIR/backups`, not web-reachable; last 5 kept, oldest pruned automatically; the active data file is written via temp-file+`rename()`, never deleted directly |
| JSON storage | Exclusive locking, write-to-temp-then-`rename()`, `JSON_THROW_ON_ERROR`, restrictive file permissions (`0600`/`0750`), every failure mode checked and handled - never a half-written file becomes the active data |
| Error handling | Internal exception details are logged server-side (`error_log()`) and never echoed to the client; generic messages only |
| Admin authorization | Centralized in `simplephp_require_admin_login()` - every admin page calls it, none rely on hidden UI or `Referer` |
| HTTP headers | `Content-Security-Policy`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, `X-Frame-Options`, and `Strict-Transport-Security` when HTTPS is detected - sent automatically by every entrypoint (see §6.2 for the CSP's known trade-off) |

### 8.1 Production checklist

- [ ] Set `SIMPLEPHP_DATA_DIR` to a path outside the web root and confirm it via `curl` that it 404s/403s over HTTP.
- [ ] Serve the site over HTTPS (required for `Secure` session cookies and HSTS to actually kick in).
- [ ] Create the first admin account per §2.1 with `must_change_password: true`, and rotate any credential you suspect was ever exposed.
- [ ] Set `display_errors = Off` and `error_reporting` to a sane production level in `php.ini` - the app never echoes exception internals itself, but PHP-level warnings should also stay out of responses.
- [ ] Verify directory permissions: the web server user needs read/write on `SIMPLEPHP_DATA_DIR` and its subdirectories, and should **not** need write access to the application code directories.
- [ ] Configure `sendmail`/SMTP if the contact form needs to actually deliver mail.
- [ ] Only grant admin credentials to people you'd trust with the ability to run JavaScript in every visitor's browser (§6.2).

---

## 9. Module System

SimplePHP includes a powerful module system that allows you to extend functionality without modifying core files.

### Features

- **Module Discovery**: Automatically scans the `/modules` directory for available modules
- **Easy Installation**: Install modules with one click from the admin panel
- **Configuration Panel**: Auto-generated configuration forms based on JSON schemas
- **Hook System**: Modules can inject content at key points in the frontend
- **5 Demo Modules Included**:
  - Hello World - Display a customizable welcome banner
  - SEO Manager - Manage meta tags and SEO settings
  - Analytics Tracker - Integrate Google Analytics and tracking codes
  - Backup Manager - Create and restore backups
  - Theme Customizer - Customize colors and appearance

### Module Structure

Each module lives in `/modules/module-id/` and contains:
- `module.json` - Module metadata (name, version, author, description)
- `module.php` - Main module logic and hook implementations
- `config.json` (optional) - Configuration schema for auto-generated forms
- `install.php` (optional) - Installation logic
- `uninstall.php` (optional) - Cleanup logic

Module IDs are validated against `^[a-zA-Z0-9_-]+$` before ever being used to build a filesystem path, so a malformed/malicious `module_id` in a request can't be used for path traversal.

### Available Hooks

Modules can register hooks to inject content:
- `page_meta` - Meta tags in `<head>`
- `page_head` - CSS/scripts before `</head>`
- `page_body_start` - Content after `<body>`
- `page_content` - Main content area
- `page_body_end` - Scripts/tracking before `</body>`

### Managing Modules

1. Go to **Admin** → **Modules** (`/admin/modules.php`)
2. **Install** a discovered module
3. **Activate** to enable it
4. **Configure** (⚙️) to customize settings
5. **Deactivate** or **Uninstall** as needed

For detailed documentation, see `MODULES.md`, `MODULE-CONFIG-SYSTEM.md`, and `FRONTEND-MODULES.md`.

Module code (`module.php`, `install.php`, etc.) runs with full PHP privileges once installed/activated - treat installing a module as equivalent to deploying code, and only install modules you trust (built-in ones, or ones you've reviewed).

---

## 10. Dependencies

None beyond PHP itself and its standard extensions (`dom`, `json`, `session`, `filter`). No `composer.json`, no third-party PHP packages - the HTML sanitizer, rate limiter, and JSON storage layer are all implemented directly against PHP's standard library to keep the app dependency-free and easy to audit. Frontend assets (Bootstrap, Feather Icons, Google Fonts) are loaded from their respective CDNs in the admin UI only; the CSP in §8 pins exactly which external origins are allowed.

---

## 11. Putting the project on GitHub

Your GitHub account: **`creatorsofcode.com`**
Repository name: **`simplephp`**

From the project root (`simplephp`), run these commands in a terminal **on your machine**:

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

**Never commit `SIMPLEPHP_DATA_DIR` contents** (`users.json`, `content.json`, backups, rate-limit state) - they live outside the project directory by design and shouldn't be part of version control regardless.

After this, you'll have the whole SimplePHP project in `https://github.com/creatorsofcode/simplephp` with this `README.md` as documentation.
