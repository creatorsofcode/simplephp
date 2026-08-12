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

/** Verify the CSRF token from a POST field or the X-CSRF-Token header. */
function simplephp_csrf_valid(): bool
{
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
 * Returns ['allowed' => bool, 'retry_after' => int seconds].
 * Does not record an attempt by itself - call simplephp_rate_limit_hit()
 * for that once you know whether the attempt should count.
 */
function simplephp_rate_limit_status(string $key, int $maxAttempts, int $windowSeconds): array
{
    $file = simplephp_rate_limit_file($key);
    $now = time();

    $fp = @fopen($file, 'c+b');
    if (!$fp) {
        return ['allowed' => true, 'retry_after' => 0];
    }

    flock($fp, LOCK_SH);
    $raw = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        $data = ['attempts' => [], 'locked_until' => 0];
    }

    $lockedUntil = (int) ($data['locked_until'] ?? 0);
    if ($lockedUntil > $now) {
        return ['allowed' => false, 'retry_after' => $lockedUntil - $now];
    }

    $attempts = array_values(array_filter((array) ($data['attempts'] ?? []), static fn($t) => is_numeric($t) && $t > $now - $windowSeconds));

    if (count($attempts) >= $maxAttempts) {
        return ['allowed' => false, 'retry_after' => $windowSeconds];
    }

    return ['allowed' => true, 'retry_after' => 0];
}

/** Record one attempt against the given key, locking a fresh window if the limit is exceeded. */
function simplephp_rate_limit_hit(string $key, int $maxAttempts, int $windowSeconds, int $lockoutSeconds): void
{
    $file = simplephp_rate_limit_file($key);
    $now = time();

    $fp = @fopen($file, 'c+b');
    if (!$fp) {
        return;
    }

    flock($fp, LOCK_EX);
    $raw = stream_get_contents($fp);
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        $data = ['attempts' => [], 'locked_until' => 0];
    }

    $attempts = array_values(array_filter((array) ($data['attempts'] ?? []), static fn($t) => is_numeric($t) && $t > $now - $windowSeconds));
    $attempts[] = $now;

    $lockedUntil = (int) ($data['locked_until'] ?? 0);
    if (count($attempts) >= $maxAttempts) {
        $lockedUntil = $now + $lockoutSeconds;
    }

    $data = ['attempts' => $attempts, 'locked_until' => $lockedUntil];

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

/** Clear rate-limit state for a key (e.g. on successful login). */
function simplephp_rate_limit_clear(string $key): void
{
    @unlink(simplephp_rate_limit_file($key));
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

/** Atomic write: write to a temp file, then rename over the target. */
function simplephp_json_write(string $path, $data, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE): bool
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }

    $tmp = $path . '.tmp' . bin2hex(random_bytes(4));
    $fp = @fopen($tmp, 'wb');
    if (!$fp) {
        return false;
    }

    flock($fp, LOCK_EX);
    fwrite($fp, (string) json_encode($data, $flags));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    if (!rename($tmp, $path)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

/**
 * Locked read-modify-write: holds an exclusive lock on $path for the whole
 * operation so concurrent requests can't interleave reads/writes, then
 * atomically replaces the file via a temp file + rename.
 *
 * $mutator receives the current decoded value (or $default) and must
 * return the new value to persist.
 */
function simplephp_json_update(string $path, callable $mutator, $default = [])
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }

    $fp = fopen($path, 'c+b');
    if (!$fp) {
        throw new RuntimeException("Cannot open $path for locked update");
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        throw new RuntimeException("Cannot lock $path");
    }

    $raw = stream_get_contents($fp);
    $current = ($raw === '' || $raw === false) ? $default : (json_decode($raw, true) ?? $default);

    $new = $mutator($current);

    $tmp = $path . '.tmp' . bin2hex(random_bytes(4));
    file_put_contents($tmp, (string) json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    rename($tmp, $path);

    flock($fp, LOCK_UN);
    fclose($fp);

    return $new;
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
