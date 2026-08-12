<?php
/**
 * Backup Manager Module
 * Manage content backups
 */

require_once __DIR__ . '/../../includes/paths.php';
require_once __DIR__ . '/../../includes/Security.php';

// Module initialization
function module_init_backup_manager() {
    // Create backups directory
    $backupDir = SIMPLEPHP_DATA_DIR . '/module-backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0750, true);
    }

    // Create settings file
    $settingsFile = SIMPLEPHP_DATA_DIR . '/settings.json';
    if (!file_exists($settingsFile)) {
        $settings = [
            'auto_backup' => false,
            'backup_interval' => 'daily',
            'max_backups' => 10,
            'last_backup' => null
        ];
        simplephp_json_write($settingsFile, $settings);
    }
}

// Create a backup
function backup_manager_create_backup($description = 'Manual backup') {
    $backupDir = SIMPLEPHP_DATA_DIR . '/module-backups';

    // Create backup data
    $backupData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'description' => $description,
        'data' => []
    ];

    // Backup content.json
    $contentFile = SIMPLEPHP_DATA_DIR . '/content.json';
    if (file_exists($contentFile)) {
        $backupData['data']['content'] = simplephp_json_read($contentFile);
    }

    // Backup users.json
    $usersFile = SIMPLEPHP_DATA_DIR . '/users.json';
    if (file_exists($usersFile)) {
        $backupData['data']['users'] = simplephp_json_read($usersFile);
    }

    // Backup modules.json
    $modulesFile = SIMPLEPHP_DATA_DIR . '/modules.json';
    if (file_exists($modulesFile)) {
        $backupData['data']['modules'] = simplephp_json_read($modulesFile);
    }

    // Create backup file
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . '/backup_' . $timestamp . '.json';
    simplephp_json_write($backupFile, $backupData);

    // Update last backup time
    $settingsFile = SIMPLEPHP_DATA_DIR . '/settings.json';
    simplephp_json_update($settingsFile, function ($settings) {
        $settings['last_backup'] = date('Y-m-d H:i:s');
        return $settings;
    }, ['auto_backup' => false, 'backup_interval' => 'daily', 'max_backups' => 10, 'last_backup' => null]);

    // Clean old backups
    backup_manager_clean_old_backups();
    
    return [
        'success' => true,
        'file' => basename($backupFile),
        'message' => 'Backup created successfully'
    ];
}

// Restore from backup
function backup_manager_restore_backup($backupFile) {
    $backupPath = SIMPLEPHP_DATA_DIR . '/module-backups/' . basename($backupFile);

    if (!file_exists($backupPath)) {
        return ['success' => false, 'message' => 'Backup file not found'];
    }

    $backupData = simplephp_json_read($backupPath);

    if (!$backupData || !isset($backupData['data'])) {
        return ['success' => false, 'message' => 'Invalid backup file'];
    }

    // Restore content.json
    if (isset($backupData['data']['content'])) {
        simplephp_json_write(SIMPLEPHP_DATA_DIR . '/content.json', $backupData['data']['content']);
    }

    // Restore users.json
    if (isset($backupData['data']['users'])) {
        simplephp_json_write(SIMPLEPHP_DATA_DIR . '/users.json', $backupData['data']['users']);
    }

    // Restore modules.json
    if (isset($backupData['data']['modules'])) {
        simplephp_json_write(SIMPLEPHP_DATA_DIR . '/modules.json', $backupData['data']['modules']);
    }

    return ['success' => true, 'message' => 'Backup restored successfully'];
}

// Get list of backups
function backup_manager_get_backups() {
    $backupDir = SIMPLEPHP_DATA_DIR . '/module-backups';
    $backups = [];

    if (!is_dir($backupDir)) {
        return $backups;
    }

    $files = glob($backupDir . '/backup_*.json');
    rsort($files); // Sort newest first

    foreach ($files as $file) {
        $data = simplephp_json_read($file, []);
        $backups[] = [
            'file' => basename($file),
            'timestamp' => $data['timestamp'] ?? 'Unknown',
            'description' => $data['description'] ?? 'No description',
            'size' => filesize($file)
        ];
    }

    return $backups;
}

// Clean old backups based on max_backups setting
function backup_manager_clean_old_backups() {
    $settingsFile = SIMPLEPHP_DATA_DIR . '/settings.json';
    $settings = simplephp_json_read($settingsFile, []);
    $maxBackups = $settings['max_backups'] ?? 10;

    $backupDir = SIMPLEPHP_DATA_DIR . '/module-backups';
    $files = glob($backupDir . '/backup_*.json');
    rsort($files); // Sort newest first

    // Remove backups beyond max limit
    $filesToDelete = array_slice($files, $maxBackups);
    foreach ($filesToDelete as $file) {
        unlink($file);
    }
}

// Delete a specific backup
function backup_manager_delete_backup($backupFile) {
    $backupPath = SIMPLEPHP_DATA_DIR . '/module-backups/' . basename($backupFile);

    if (!file_exists($backupPath)) {
        return ['success' => false, 'message' => 'Backup file not found'];
    }

    unlink($backupPath);
    return ['success' => true, 'message' => 'Backup deleted successfully'];
}

// Get settings
function backup_manager_get_settings() {
    return simplephp_json_read(SIMPLEPHP_DATA_DIR . '/settings.json', [
        'auto_backup' => false, 'backup_interval' => 'daily', 'max_backups' => 10, 'last_backup' => null
    ]);
}

// Save settings
function backup_manager_save_settings($settings) {
    return simplephp_json_write(SIMPLEPHP_DATA_DIR . '/settings.json', $settings);
}

// Hook: Auto backup on content save
function module_hook_backup_manager_content_saved($data) {
    $settings = backup_manager_get_settings();
    
    if ($settings['auto_backup']) {
        backup_manager_create_backup('Auto backup on content save');
    }
}
