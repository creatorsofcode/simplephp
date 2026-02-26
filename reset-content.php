<?php
/**
 * reset-content.php
 *
 * Helper script to reset the site back to a stored default snapshot.
 *
 * It handles:
 * - data/content.json      <-> data/content_default.json
 * - key PHP/JS files       <-> *.default snapshots (first run will create them)
 *
 * Intended to be called by a cron job, scheduled task or manually.
 *
 * Example cron (Linux, once per minute):
 * * * * * php /path/to/simplephp/reset-content.php >/dev/null 2>&1
 *
 * WARNING: This will overwrite content.json and selected core files when defaults exist.
 */

declare(strict_types=1);

error_reporting(E_ALL);

$baseDir      = __DIR__ . DIRECTORY_SEPARATOR;
$currentJson  = $baseDir . 'data' . DIRECTORY_SEPARATOR . 'content.json';
$defaultJson  = $baseDir . 'data' . DIRECTORY_SEPARATOR . 'content_default.json';

// Core files whose HTML/JS we want to be able to reset as well
$coreFiles = [
    'index.php',
    'admin/index.php',
    'admin/login.php',
    'contact-form.js',
    'send-email.php',
];

header('Content-Type: text/plain; charset=utf-8');

if (!file_exists($currentJson)) {
    http_response_code(500);
    echo "Error: data/content.json not found.\n";
    exit(1);
}

// First run? create JSON + core file snapshots and exit
$isFirstRun = !file_exists($defaultJson);

if ($isFirstRun) {
    if (!copy($currentJson, $defaultJson)) {
        http_response_code(500);
        echo "Error: failed to create default snapshot (content_default.json).\n";
        exit(1);
    }

    // Create *.default backups for core files (only if they don't already exist)
    foreach ($coreFiles as $relative) {
        $filePath   = $baseDir . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        $backupPath = $filePath . '.default';

        if (!file_exists($filePath)) {
            // Skip silently if file is missing – not fatal
            continue;
        }

        if (!file_exists($backupPath)) {
            @copy($filePath, $backupPath);
        }
    }

    echo "Default snapshots created from current content and core files.\n";
    exit(0);
}

// Overwrite current JSON content with the default snapshot
if (!copy($defaultJson, $currentJson)) {
    http_response_code(500);
    echo "Error: failed to reset content.json from content_default.json.\n";
    exit(1);
}

// Restore core files from their *.default backups (if present)
foreach ($coreFiles as $relative) {
    $filePath   = $baseDir . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
    $backupPath = $filePath . '.default';

    if (file_exists($backupPath)) {
        @copy($backupPath, $filePath);
    }
}

echo "Content and core files reset to default snapshots.\n";
exit(0);

