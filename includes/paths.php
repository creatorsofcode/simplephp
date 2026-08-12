<?php
/**
 * Central location for the external (outside web root) data directory.
 * Everything that used to live in web-accessible data/ - users.json,
 * content.json, backups, etc. - now lives here instead.
 */

declare(strict_types=1);

if (!defined('SIMPLEPHP_DATA_DIR')) {
    $externalDataDir = getenv('SIMPLEPHP_DATA_DIR');

    if ($externalDataDir === false || trim($externalDataDir) === '') {
        // Portable default: one directory above whatever the web server's
        // document root is - never hardcode an OS-specific absolute path
        // here, since that would silently misplace (or fail to create)
        // the data directory on any other host/deployment.
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
            $externalDataDir = dirname($docRoot) . DIRECTORY_SEPARATOR . 'simplephp-data';
        } else {
            // CLI / cron context: climb from includes/ -> app root -> web root -> parent.
            $externalDataDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'simplephp-data';
        }
    }

    define('SIMPLEPHP_DATA_DIR', rtrim($externalDataDir, '/\\'));
}

foreach (['', '/backups', '/module-backups', '/core-backups', '/security'] as $sub) {
    $dir = SIMPLEPHP_DATA_DIR . $sub;
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
}

// Fail CLOSED: if the resolved data directory turns out to be inside the
// web-served document root (misconfigured SIMPLEPHP_DATA_DIR, unexpected
// hosting layout, symlinks, ...), refuse to continue rather than silently
// serving credentials/content over HTTP.
if (!empty($_SERVER['DOCUMENT_ROOT'])) {
    $docRootReal = realpath($_SERVER['DOCUMENT_ROOT']);
    $dataDirReal = realpath(SIMPLEPHP_DATA_DIR);

    if ($docRootReal !== false && $dataDirReal !== false) {
        $docRootReal = rtrim(str_replace('\\', '/', $docRootReal), '/') . '/';
        $dataDirCheck = rtrim(str_replace('\\', '/', $dataDirReal), '/') . '/';

        if (stripos($dataDirCheck, $docRootReal) === 0) {
            http_response_code(500);
            die('Fatal misconfiguration: the application data directory resolves inside the web root. Refusing to continue.');
        }
    }
}

// Defense in depth: even though SIMPLEPHP_DATA_DIR is meant to live outside
// the web root, make sure that if it is ever reachable over HTTP anyway
// (misconfigured alias, changed document root, ...) it still denies access.
$dataHtaccess = SIMPLEPHP_DATA_DIR . '/.htaccess';
if (!file_exists($dataHtaccess)) {
    @file_put_contents($dataHtaccess, "Require all denied\nDeny from all\n");
}
$dataIndex = SIMPLEPHP_DATA_DIR . '/index.php';
if (!file_exists($dataIndex)) {
    @file_put_contents($dataIndex, "<?php\nhttp_response_code(403);\nexit('Forbidden');\n");
}
