<?php
/**
 * Backup Manager - Installation
 */

function module_install_backup_manager() {
    // Create backups directory
    $backupDir = __DIR__ . '/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    // Create settings file
    $settingsFile = __DIR__ . '/settings.json';
    $settings = [
        'auto_backup' => false,
        'backup_interval' => 'daily',
        'max_backups' => 10,
        'last_backup' => null
    ];
    file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    
    // Create initial backup
    include __DIR__ . '/module.php';
    backup_manager_create_backup('Initial backup on module installation');
    
    return true;
}
