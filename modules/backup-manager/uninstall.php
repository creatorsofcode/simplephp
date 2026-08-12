<?php
/**
 * Backup Manager - Uninstallation
 */

function module_uninstall_backup_manager() {
    // Note: We DON'T delete backups on uninstall for safety
    // Users should manually delete backups if they want to
    
    // Remove settings file
    $settingsFile = __DIR__ . '/settings.json';
    if (file_exists($settingsFile)) {
        unlink($settingsFile);
    }
    
    return true;
}
