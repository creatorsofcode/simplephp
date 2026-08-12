<?php
/**
 * Central location for the external (outside web root) data directory.
 * Everything that used to live in web-accessible data/ - users.json,
 * content.json, backups, etc. - now lives here instead.
 */

declare(strict_types=1);

if (!defined('SIMPLEPHP_DATA_DIR')) {
    $externalDataDir = getenv('SIMPLEPHP_DATA_DIR');
    if ($externalDataDir === false || $externalDataDir === '') {
        $externalDataDir = 'C:\\wamp64\\simplephp-data';
    }
    define('SIMPLEPHP_DATA_DIR', rtrim($externalDataDir, '/\\'));
}

foreach (['', '/backups', '/module-backups', '/core-backups', '/security'] as $sub) {
    $dir = SIMPLEPHP_DATA_DIR . $sub;
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
}
