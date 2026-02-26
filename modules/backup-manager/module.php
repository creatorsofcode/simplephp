<?php
/**
 * Backup Manager Module
 * Manage content backups
 */

// Module initialization
function module_init_backup_manager() {
    // Create backups directory
    $backupDir = __DIR__ . '/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    // Create settings file
    $settingsFile = __DIR__ . '/settings.json';
    if (!file_exists($settingsFile)) {
        $settings = [
            'auto_backup' => false,
            'backup_interval' => 'daily',
            'max_backups' => 10,
            'last_backup' => null
        ];
        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    }
}

// Create a backup
function backup_manager_create_backup($description = 'Manual backup') {
    $backupDir = __DIR__ . '/backups';
    
    // Create backup data
    $backupData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'description' => $description,
        'data' => []
    ];
    
    // Backup content.json
    $contentFile = __DIR__ . '/../../data/content.json';
    if (file_exists($contentFile)) {
        $backupData['data']['content'] = json_decode(file_get_contents($contentFile), true);
    }
    
    // Backup users.json
    $usersFile = __DIR__ . '/../../data/users.json';
    if (file_exists($usersFile)) {
        $backupData['data']['users'] = json_decode(file_get_contents($usersFile), true);
    }
    
    // Backup modules.json
    $modulesFile = __DIR__ . '/../../data/modules.json';
    if (file_exists($modulesFile)) {
        $backupData['data']['modules'] = json_decode(file_get_contents($modulesFile), true);
    }
    
    // Create backup file
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . '/backup_' . $timestamp . '.json';
    file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
    
    // Update last backup time
    $settingsFile = __DIR__ . '/settings.json';
    $settings = json_decode(file_get_contents($settingsFile), true);
    $settings['last_backup'] = date('Y-m-d H:i:s');
    file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    
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
    $backupPath = __DIR__ . '/backups/' . basename($backupFile);
    
    if (!file_exists($backupPath)) {
        return ['success' => false, 'message' => 'Backup file not found'];
    }
    
    $backupData = json_decode(file_get_contents($backupPath), true);
    
    if (!$backupData || !isset($backupData['data'])) {
        return ['success' => false, 'message' => 'Invalid backup file'];
    }
    
    // Restore content.json
    if (isset($backupData['data']['content'])) {
        $contentFile = __DIR__ . '/../../data/content.json';
        file_put_contents($contentFile, json_encode($backupData['data']['content'], JSON_PRETTY_PRINT));
    }
    
    // Restore users.json
    if (isset($backupData['data']['users'])) {
        $usersFile = __DIR__ . '/../../data/users.json';
        file_put_contents($usersFile, json_encode($backupData['data']['users'], JSON_PRETTY_PRINT));
    }
    
    // Restore modules.json
    if (isset($backupData['data']['modules'])) {
        $modulesFile = __DIR__ . '/../../data/modules.json';
        file_put_contents($modulesFile, json_encode($backupData['data']['modules'], JSON_PRETTY_PRINT));
    }
    
    return ['success' => true, 'message' => 'Backup restored successfully'];
}

// Get list of backups
function backup_manager_get_backups() {
    $backupDir = __DIR__ . '/backups';
    $backups = [];
    
    if (!is_dir($backupDir)) {
        return $backups;
    }
    
    $files = glob($backupDir . '/backup_*.json');
    rsort($files); // Sort newest first
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
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
    $settingsFile = __DIR__ . '/settings.json';
    $settings = json_decode(file_get_contents($settingsFile), true);
    $maxBackups = $settings['max_backups'] ?? 10;
    
    $backupDir = __DIR__ . '/backups';
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
    $backupPath = __DIR__ . '/backups/' . basename($backupFile);
    
    if (!file_exists($backupPath)) {
        return ['success' => false, 'message' => 'Backup file not found'];
    }
    
    unlink($backupPath);
    return ['success' => true, 'message' => 'Backup deleted successfully'];
}

// Get settings
function backup_manager_get_settings() {
    $settingsFile = __DIR__ . '/settings.json';
    return json_decode(file_get_contents($settingsFile), true);
}

// Save settings
function backup_manager_save_settings($settings) {
    $settingsFile = __DIR__ . '/settings.json';
    return file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
}

// Hook: Auto backup on content save
function module_hook_backup_manager_content_saved($data) {
    $settings = backup_manager_get_settings();
    
    if ($settings['auto_backup']) {
        backup_manager_create_backup('Auto backup on content save');
    }
}
