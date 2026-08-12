<?php
/**
 * Security helpers shared across the whole app:
 * secure sessions, CSRF tokens, brute-force rate limiting,
 * atomic/locked JSON storage, and HTML sanitization.
 */

declare(strict_types=1);

require_once __DIR__ . '/paths.php';

/* ----------------------------------------------------------------------
 * Sessions
 * -------------------------------------------------------------------- */

function simplephp_secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/* ----------------------------------------------------------------------
 * HTTP security headers
 * -------------------------------------------------------------------- */

/**
 * Send a CSP + supporting headers compatible with what this app actually
 * does today: Bootstrap/Feather Icons from jsdelivr, Google Fonts, and -
 * importantly - admin-authored custom CSS/JS (design.custom_css/custom_js)
 * rendered inline on the public site by design (see docs/CUSTOM_CODE.md).
 * That last feature is why script-src/style-src need 'unsafe-inline': the
 * whole point of that admin feature is to run inline code the admin wrote.
 * CSP here is defense-in-depth around that, not the primary XSS defense -
 * the real defenses against untrusted (visitor-supplied) content are
 * output escaping (htmlspecialchars) and the allowlist HTML sanitizer in
 * simplephp_sanitize_html(), which never let visitor input reach a
 * <script> tag or an event-handler attribute in the first place.
 */
function simplephp_send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    $csp = implode('; ', [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.googletagmanager.com https://connect.facebook.net",
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
        "font-src 'self' https://fonts.gstatic.com data:",
        "img-src 'self' data: https:",
        "connect-src 'self' https://www.google-analytics.com https://www.facebook.com",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
    ]);

    header("Content-Security-Policy: $csp");
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    header('X-Frame-Options: SAMEORIGIN'); // legacy fallback for browsers that ignore frame-ancestors

    if ($isHttps) {
        // Only sent over HTTPS - sending it over plain HTTP would be a lie
        // (and could break a not-yet-HTTPS deployment for a year).
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/* ----------------------------------------------------------------------
 * Admin authentication (centralized so no admin page can forget a check)
 * -------------------------------------------------------------------- */

/**
 * Require an authenticated admin for an HTML admin page: starts the
 * secure session, redirects to $loginUrl if not logged in, and - unless
 * the current script IS $forceChangeUrl - redirects there instead if the
 * account is flagged to require a new password before doing anything else.
 *
 * $loginUrl/$forceChangeUrl are relative URLs resolved by the browser
 * against the current page, so callers just pass the filename appropriate
 * to their own directory depth (both admin/*.php files sit next to
 * login.php and force-password-change.php, so the defaults work there).
 */
function simplephp_require_admin_login(string $loginUrl = 'login.php', string $forceChangeUrl = 'force-password-change.php'): void
{
    simplephp_secure_session_start();

    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ' . $loginUrl);
        exit;
    }

    $currentScript = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (!empty($_SESSION['must_change_password']) && $currentScript !== basename($forceChangeUrl)) {
        header('Location: ' . $forceChangeUrl);
        exit;
    }
}

/* ----------------------------------------------------------------------
 * User records (users.json)
 *
 * Two formats are supported per-entry:
 *   "username": "$2y$...hash..."                              (legacy)
 *   "username": {"hash": "$2y$...", "must_change_password": true}
 * so existing accounts keep working unchanged while new/flagged accounts
 * can require a password reset before they can use the admin area.
 * -------------------------------------------------------------------- */

function simplephp_user_hash($record): ?string
{
    if (is_string($record)) {
        return $record;
    }
    if (is_array($record) && isset($record['hash']) && is_string($record['hash'])) {
        return $record['hash'];
    }
    return null;
}

function simplephp_user_must_change_password($record): bool
{
    return is_array($record) && !empty($record['must_change_password']);
}

/* ----------------------------------------------------------------------
 * CSRF protection
 * -------------------------------------------------------------------- */

function simplephp_csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function simplephp_csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(simplephp_csrf_token(), ENT_QUOTES) . '">';
}

/**
 * Verify the CSRF token from a POST field or the X-CSRF-Token header.
 * Always requires the request itself to be a POST - a GET (or any other
 * method) never carries a valid CSRF proof, regardless of caller.
 */
function simplephp_csrf_valid(): bool
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return false;
    }

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        return false;
    }

    $submitted = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!is_string($submitted) || $submitted === '') {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $submitted);
}

/** For JSON/AJAX endpoints: verify or terminate with a 403 JSON error. */
function simplephp_require_csrf_json(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }
    if (!simplephp_csrf_valid()) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid or missing security token. Please reload the page and try again.']);
        exit;
    }
}

/* ----------------------------------------------------------------------
 * Brute-force / rate limiting (file-backed, locked)
 * -------------------------------------------------------------------- */

function simplephp_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return is_string($ip) ? $ip : 'unknown';
}

function simplephp_rate_limit_file(string $key): string
{
    $safeKey = preg_replace('/[^a-zA-Z0-9_.:-]/', '_', $key) ?? 'key';
    return SIMPLEPHP_DATA_DIR . '/security/ratelimit_' . $safeKey . '.json';
}

/**
 * Atomically check-and-record one attempt against $key under a single
 * exclusive lock, so concurrent requests can't both slip through between
 * a separate "check" and "record" step (the old status()+hit() pair had
 * exactly that race).
 *
 * Fails CLOSED: if the rate-limit store can't be opened or locked, the
 * attempt is treated as NOT allowed rather than silently let through.
 *
 * Returns ['allowed' => bool, 'retry_after' => int seconds].
 */
function simplephp_rate_limit_attempt(string $key, int $maxAttempts, int $windowSeconds, int $lockoutSeconds): array
{
    $file = simplephp_rate_limit_file($key);
    $now = time();

    $fileIsNew = !file_exists($file);
    $fp = @fopen($file, 'c+b');
    if (!$fp) {
        return ['allowed' => false, 'retry_after' => $windowSeconds];
    }
    if ($fileIsNew) {
        @chmod($file, 0600);
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return ['allowed' => false, 'retry_after' => $windowSeconds];
    }

    $raw = stream_get_contents($fp);
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        $data = ['attempts' => [], 'locked_until' => 0];
    }

    $lockedUntil = (int) ($data['locked_until'] ?? 0);
    if ($lockedUntil > $now) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return ['allowed' => false, 'retry_after' => $lockedUntil - $now];
    }

    $attempts = array_values(array_filter((array) ($data['attempts'] ?? []), static fn($t) => is_numeric($t) && $t > $now - $windowSeconds));

    // $maxAttempts already used up within the window -> this request is the
    // one that gets rejected and starts the lockout (doesn't get counted
    // again itself, so maxAttempts genuinely means "N tries allowed").
    if (count($attempts) >= $maxAttempts) {
        $lockedUntil = $now + $lockoutSeconds;
        $writeOk = ftruncate($fp, 0) && rewind($fp) !== false;
        $bytesWritten = $writeOk ? fwrite($fp, (string) json_encode(['attempts' => $attempts, 'locked_until' => $lockedUntil])) : false;
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $bytesWritten === false
            ? ['allowed' => false, 'retry_after' => $windowSeconds]
            : ['allowed' => false, 'retry_after' => $lockoutSeconds];
    }

    $attempts[] = $now;
    $result = ['allowed' => true, 'retry_after' => 0];

    $writeOk = ftruncate($fp, 0) && rewind($fp) !== false;
    $bytesWritten = $writeOk ? fwrite($fp, (string) json_encode(['attempts' => $attempts, 'locked_until' => 0])) : false;
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    if ($bytesWritten === false) {
        // Couldn't persist the attempt - fail closed rather than risk an
        // unbounded number of un-tracked attempts getting through.
        return ['allowed' => false, 'retry_after' => $windowSeconds];
    }

    return $result;
}

/** Clear rate-limit state for a key (e.g. on successful login). */
function simplephp_rate_limit_clear(string $key): void
{
    @unlink(simplephp_rate_limit_file($key));
}

/**
 * Log the real exception message server-side and return a generic message
 * safe to show the client. Exception messages can contain file paths,
 * internal state, or other details we don't want to hand to whoever
 * triggered the error - so callers should surface this return value to
 * the client instead of $e->getMessage() directly.
 */
function simplephp_safe_error(Throwable $e, string $context = ''): string
{
    error_log(($context !== '' ? "[$context] " : '') . get_class($e) . ': ' . $e->getMessage());
    return 'An internal error occurred. Please try again.';
}

/* ----------------------------------------------------------------------
 * Atomic / locked JSON storage
 * -------------------------------------------------------------------- */

function simplephp_json_read(string $path, $default = null)
{
    if (!file_exists($path)) {
        return $default;
    }

    $fp = @fopen($path, 'rb');
    if (!$fp) {
        return $default;
    }

    flock($fp, LOCK_SH);
    $raw = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    $data = json_decode((string) $raw, true);
    if ($data === null && trim((string) $raw) !== 'null') {
        return $default;
    }
    return $data;
}

/**
 * Atomic write: write to a temp file, then rename over the target.
 * Every failure mode (can't create dir, can't encode, can't lock, partial
 * write, can't rename) is checked - a failure never leaves a truncated or
 * empty file in place of good data, since we only ever write to the temp
 * file and rename() is atomic on the same filesystem.
 */
function simplephp_json_write(string $path, $data, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE): bool
{
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
        return false;
    }

    try {
        $json = json_encode($data, $flags | JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        return false;
    }

    $tmp = $path . '.tmp' . bin2hex(random_bytes(4));
    $fp = @fopen($tmp, 'wb');
    if (!$fp) {
        return false;
    }

    $ok = flock($fp, LOCK_EX);
    if ($ok) {
        $bytesWritten = fwrite($fp, $json);
        $ok = $bytesWritten !== false && $bytesWritten === strlen($json);
        $ok = fflush($fp) && $ok;
        flock($fp, LOCK_UN);
    }
    fclose($fp);

    if (!$ok) {
        @unlink($tmp);
        return false;
    }

    // Restrictive permissions (owner read/write only) before the file is
    // visible at its final name - no window where it's briefly world/group
    // readable. No-op on Windows filesystems that don't honor POSIX bits.
    @chmod($tmp, 0600);

    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

/**
 * Locked read-modify-write: holds an exclusive lock for the whole operation
 * so concurrent requests can't interleave reads/writes, then atomically
 * replaces $path via a temp file + rename.
 *
 * The lock is held on a separate $path.lock file, NOT on $path itself.
 * On Windows, rename() cannot replace a file that currently has an open
 * handle - even one held by this same process - so $path must never be
 * kept open while we rename a temp file over it.
 *
 * $mutator receives the current decoded value (or $default) and must
 * return the new value to persist.
 *
 * Every step (open, lock, read, decode, encode, write, rename) is checked
 * and raises a RuntimeException on failure. The lock is always released
 * via finally, even if the mutator callback itself throws - otherwise a
 * bug in a mutator would leave the file locked for the rest of the request.
 */
function simplephp_json_update(string $path, callable $mutator, $default = [])
{
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
        throw new RuntimeException("Cannot create directory for $path");
    }

    $lockPath = $path . '.lock';
    $lockIsNew = !file_exists($lockPath);
    $lockFp = @fopen($lockPath, 'c+b');
    if (!$lockFp) {
        throw new RuntimeException("Cannot open lock file for $path");
    }
    if ($lockIsNew) {
        @chmod($lockPath, 0600);
    }

    if (!flock($lockFp, LOCK_EX)) {
        fclose($lockFp);
        throw new RuntimeException("Cannot lock $path");
    }

    try {
        $raw = file_exists($path) ? @file_get_contents($path) : '';
        if ($raw === false) {
            throw new RuntimeException("Cannot read $path");
        }

        if (trim($raw) === '') {
            $current = $default;
        } else {
            try {
                $current = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                // Existing file is corrupt/unreadable JSON - treat as the
                // caller-supplied default rather than propagating a decode
                // error for state we didn't just write ourselves.
                $current = $default;
            }
        }

        $new = $mutator($current);

        try {
            $json = json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Failed to encode data for $path: " . $e->getMessage(), 0, $e);
        }

        $tmp = $path . '.tmp' . bin2hex(random_bytes(4));
        $written = @file_put_contents($tmp, $json);
        if ($written !== false && $written === strlen($json)) {
            // Restrictive permissions before the file becomes visible at
            // its final name (see simplephp_json_write for the same logic).
            @chmod($tmp, 0600);
        }
        $renamed = $written !== false && $written === strlen($json) && @rename($tmp, $path);

        if (!$renamed) {
            @unlink($tmp);
            throw new RuntimeException("Failed to persist $path");
        }

        return $new;
    } finally {
        flock($lockFp, LOCK_UN);
        fclose($lockFp);
    }
}

/* ----------------------------------------------------------------------
 * HTML sanitization (allowlist, DOM-based - no external dependency)
 * -------------------------------------------------------------------- */

function simplephp_sanitize_html(string $html): string
{
    static $allowedTags = [
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [],
        'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'span' => ['class'], 'div' => ['class'],
        'blockquote' => [], 'code' => [], 'pre' => [],
    ];
    static $allowedProtocols = ['http', 'https', 'mailto', 'tel'];

    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML(
        '<?xml encoding="utf-8"?><div id="simplephp-sanitize-root">' . $html . '</div>',
        LIBXML_NOERROR | LIBXML_NOWARNING
    );
    libxml_clear_errors();

    $root = $doc->getElementById('simplephp-sanitize-root');
    if (!$root) {
        return '';
    }

    simplephp_sanitize_node($root, $allowedTags, $allowedProtocols);

    $out = '';
    foreach (iterator_to_array($root->childNodes) as $child) {
        $out .= $doc->saveHTML($child);
    }
    return trim($out);
}

function simplephp_sanitize_node(DOMNode $node, array $allowedTags, array $allowedProtocols): void
{
    foreach (iterator_to_array($node->childNodes) as $child) {
        if ($child instanceof DOMText) {
            continue;
        }

        if (!$child instanceof DOMElement) {
            // Comments, processing instructions, CDATA, etc. - drop.
            $node->removeChild($child);
            continue;
        }

        $tag = strtolower($child->tagName);

        if (!isset($allowedTags[$tag])) {
            // Unknown/disallowed tag (script, iframe, svg, style, object, ...):
            // unwrap it - keep the safe text/children, drop the tag itself.
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }

        $allowedAttrs = $allowedTags[$tag];
        foreach (iterator_to_array($child->attributes ?? []) as $attr) {
            $attrName = strtolower($attr->name);

            if (strpos($attrName, 'on') === 0 || !in_array($attrName, $allowedAttrs, true)) {
                $child->removeAttribute($attr->name);
                continue;
            }

            if (in_array($attrName, ['href', 'src'], true)) {
                $value = trim($attr->value);
                if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $value, $m)) {
                    if (!in_array(strtolower($m[1]), $allowedProtocols, true)) {
                        $child->removeAttribute($attr->name);
                        continue;
                    }
                } elseif (stripos($value, '//') === 0) {
                    // protocol-relative URL - fine, treated as http(s)
                }
            }
        }

        if ($tag === 'a') {
            $child->setAttribute('rel', 'noopener noreferrer');
        }

        simplephp_sanitize_node($child, $allowedTags, $allowedProtocols);
    }
}

// Every file in the app requires_once this file before producing any
// output, so sending the security headers here - once - guarantees they're
// present everywhere without relying on each entrypoint remembering to
// call it individually.
simplephp_send_security_headers();
