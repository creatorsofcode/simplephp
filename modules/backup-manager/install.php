<?php
/**
 * Backup Manager - Installation
 */

require_once __DIR__ . '/../../includes/paths.php';
require_once __DIR__ . '/../../includes/Security.php';

function module_install_backup_manager() {
    // Create backups directory (outside the web root)
    $backupDir = SIMPLEPHP_DATA_DIR . '/module-backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0750, true);
    }

    // Create settings file
    $settingsFile = SIMPLEPHP_DATA_DIR . '/settings.json';
    $settings = [
        'auto_backup' => false,
        'backup_interval' => 'daily',
        'max_backups' => 10,
        'last_backup' => null
    ];
    simplephp_json_write($settingsFile, $settings);

    // Create initial backup
    include __DIR__ . '/module.php';
    backup_manager_create_backup('Initial backup on module installation');

    return true;
}
