<?php
/**
 * ModuleManager - Core Module System
 * Manages module discovery, installation, activation, and execution
 */
require_once __DIR__ . '/paths.php';
require_once __DIR__ . '/Security.php';

class ModuleManager {
    private $modulesDir;
    private $dataFile;
    private $modulesData;
    private $loadedModules = [];

    public function __construct($modulesDir = null, $dataFile = null) {
        $this->modulesDir = $modulesDir ?? __DIR__ . '/../modules';
        $this->dataFile = $dataFile ?? SIMPLEPHP_DATA_DIR . '/modules.json';
        $this->loadModulesData();
    }

    /**
     * Load modules data from JSON file
     */
    private function loadModulesData() {
        $this->modulesData = simplephp_json_read($this->dataFile, ['installed' => [], 'active' => []]);
        if (!file_exists($this->dataFile)) {
            $this->saveModulesData();
        }
    }

    /**
     * Save modules data to JSON file
     */
    private function saveModulesData() {
        simplephp_json_write($this->dataFile, $this->modulesData);
    }

    /**
     * Atomically read-modify-write modules.json under an exclusive lock,
     * then sync the in-memory snapshot so this request sees the result.
     * Returns true on success, false if the write failed (storage error,
     * permissions, disk full, ...) - callers turn that into a user-facing
     * error instead of letting the exception surface as a fatal.
     */
    private function mutateModulesData(callable $mutator): bool {
        try {
            $this->modulesData = simplephp_json_update(
                $this->dataFile,
                $mutator,
                ['installed' => [], 'active' => []]
            );
            return true;
        } catch (RuntimeException $e) {
            return false;
        }
    }
    
    /**
     * Discover all available modules in the modules directory
     */
    public function discoverModules() {
        $modules = [];
        
        if (!is_dir($this->modulesDir)) {
            mkdir($this->modulesDir, 0755, true);
            return $modules;
        }
        
        $directories = glob($this->modulesDir . '/*', GLOB_ONLYDIR);
        
        foreach ($directories as $dir) {
            $moduleId = basename($dir);
            $infoFile = $dir . '/module.json';
            
            if (file_exists($infoFile)) {
                $info = json_decode(file_get_contents($infoFile), true);
                if ($info) {
                    $info['id'] = $moduleId;
                    $info['path'] = $dir;
                    $info['installed'] = in_array($moduleId, $this->modulesData['installed'] ?? []);
                    $info['active'] = in_array($moduleId, $this->modulesData['active'] ?? []);
                    $modules[$moduleId] = $info;
                }
            }
        }
        
        return $modules;
    }
    
    /**
     * Get module information
     */
    public function getModuleInfo($moduleId) {
        $infoFile = $this->modulesDir . '/' . $moduleId . '/module.json';
        
        if (file_exists($infoFile)) {
            $info = json_decode(file_get_contents($infoFile), true);
            if ($info) {
                $info['id'] = $moduleId;
                $info['path'] = $this->modulesDir . '/' . $moduleId;
                $info['installed'] = in_array($moduleId, $this->modulesData['installed'] ?? []);
                $info['active'] = in_array($moduleId, $this->modulesData['active'] ?? []);
                return $info;
            }
        }
        
        return null;
    }
    
    /**
     * Install a module
     */
    public function installModule($moduleId) {
        $info = $this->getModuleInfo($moduleId);
        
        if (!$info) {
            return ['success' => false, 'message' => 'Module not found'];
        }
        
        if ($info['installed']) {
            return ['success' => false, 'message' => 'Module already installed'];
        }
        
        // Run installation script if exists
        $installFile = $info['path'] . '/install.php';
        if (file_exists($installFile)) {
            try {
                include $installFile;
                if (function_exists('module_install_' . str_replace('-', '_', $moduleId))) {
                    $result = call_user_func('module_install_' . str_replace('-', '_', $moduleId));
                    if ($result === false) {
                        return ['success' => false, 'message' => 'Installation script failed'];
                    }
                }
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Installation error: ' . $e->getMessage()];
            }
        }
        
        // Mark as installed
        $ok = $this->mutateModulesData(function ($data) use ($moduleId) {
            $data['installed'] = $data['installed'] ?? [];
            if (!in_array($moduleId, $data['installed'], true)) {
                $data['installed'][] = $moduleId;
            }
            return $data;
        });
        if (!$ok) {
            return ['success' => false, 'message' => 'Module installed, but failed to save module state. Please retry.'];
        }

        return ['success' => true, 'message' => 'Module installed successfully'];
    }
    
    /**
     * Uninstall a module
     */
    public function uninstallModule($moduleId) {
        $info = $this->getModuleInfo($moduleId);
        
        if (!$info) {
            return ['success' => false, 'message' => 'Module not found'];
        }
        
        // Deactivate first if active
        if ($info['active']) {
            $this->deactivateModule($moduleId);
        }
        
        // Run uninstallation script if exists
        $uninstallFile = $info['path'] . '/uninstall.php';
        if (file_exists($uninstallFile)) {
            try {
                include $uninstallFile;
                if (function_exists('module_uninstall_' . str_replace('-', '_', $moduleId))) {
                    call_user_func('module_uninstall_' . str_replace('-', '_', $moduleId));
                }
            } catch (Exception $e) {
                // Continue even if uninstall script fails
            }
        }
        
        // Remove from installed
        $ok = $this->mutateModulesData(function ($data) use ($moduleId) {
            $data['installed'] = array_values(array_diff($data['installed'] ?? [], [$moduleId]));
            return $data;
        });
        if (!$ok) {
            return ['success' => false, 'message' => 'Failed to save module state. Please retry.'];
        }

        return ['success' => true, 'message' => 'Module uninstalled successfully'];
    }
    
    /**
     * Activate a module
     */
    public function activateModule($moduleId) {
        $info = $this->getModuleInfo($moduleId);
        
        if (!$info) {
            return ['success' => false, 'message' => 'Module not found'];
        }
        
        if (!$info['installed']) {
            return ['success' => false, 'message' => 'Module must be installed first'];
        }
        
        if ($info['active']) {
            return ['success' => false, 'message' => 'Module already active'];
        }
        
        // Add to active modules
        $ok = $this->mutateModulesData(function ($data) use ($moduleId) {
            $data['active'] = $data['active'] ?? [];
            if (!in_array($moduleId, $data['active'], true)) {
                $data['active'][] = $moduleId;
            }
            return $data;
        });
        if (!$ok) {
            return ['success' => false, 'message' => 'Failed to save module state. Please retry.'];
        }

        return ['success' => true, 'message' => 'Module activated successfully'];
    }
    
    /**
     * Deactivate a module
     */
    public function deactivateModule($moduleId) {
        $info = $this->getModuleInfo($moduleId);
        
        if (!$info) {
            return ['success' => false, 'message' => 'Module not found'];
        }
        
        if (!$info['active']) {
            return ['success' => false, 'message' => 'Module is not active'];
        }
        
        // Remove from active modules
        $ok = $this->mutateModulesData(function ($data) use ($moduleId) {
            $data['active'] = array_values(array_diff($data['active'] ?? [], [$moduleId]));
            return $data;
        });
        if (!$ok) {
            return ['success' => false, 'message' => 'Failed to save module state. Please retry.'];
        }

        return ['success' => true, 'message' => 'Module deactivated successfully'];
    }
    
    /**
     * Load and execute all active modules
     */
    public function loadActiveModules() {
        foreach ($this->modulesData['active'] ?? [] as $moduleId) {
            $this->loadModule($moduleId);
        }
    }
    
    /**
     * Load a specific module
     */
    public function loadModule($moduleId) {
        if (isset($this->loadedModules[$moduleId])) {
            return $this->loadedModules[$moduleId];
        }
        
        $info = $this->getModuleInfo($moduleId);
        
        if (!$info || !$info['active']) {
            return null;
        }
        
        $moduleFile = $info['path'] . '/module.php';
        
        if (file_exists($moduleFile)) {
            include_once $moduleFile;
            $this->loadedModules[$moduleId] = $info;
            
            // Call module init function if exists
            $initFunction = 'module_init_' . str_replace('-', '_', $moduleId);
            if (function_exists($initFunction)) {
                call_user_func($initFunction);
            }
            
            return $info;
        }
        
        return null;
    }
    
    /**
     * Execute a hook for all active modules
     */
    public function executeHook($hookName, $data = null) {
        $results = [];
        
        foreach ($this->modulesData['active'] ?? [] as $moduleId) {
            $functionName = 'module_hook_' . str_replace('-', '_', $moduleId) . '_' . $hookName;
            
            if (!isset($this->loadedModules[$moduleId])) {
                $this->loadModule($moduleId);
            }
            
            if (function_exists($functionName)) {
                $results[$moduleId] = call_user_func($functionName, $data);
            }
        }
        
        return $results;
    }
    
    /**
     * Get all installed modules
     */
    public function getInstalledModules() {
        $modules = $this->discoverModules();
        return array_filter($modules, function($module) {
            return $module['installed'];
        });
    }
    
    /**
     * Get all active modules
     */
    public function getActiveModules() {
        $modules = $this->discoverModules();
        return array_filter($modules, function($module) {
            return $module['active'];
        });
    }
    
    /**
     * Check if a module is active
     */
    public function isModuleActive($moduleId) {
        return in_array($moduleId, $this->modulesData['active'] ?? []);
    }
    
    /**
     * Get module configuration schema
     * Returns the configuration fields defined in config.json
     */
    public function getModuleConfigSchema($moduleId) {
        $configSchemaFile = $this->modulesDir . '/' . $moduleId . '/config.json';
        
        if (file_exists($configSchemaFile)) {
            return json_decode(file_get_contents($configSchemaFile), true);
        }
        
        return null;
    }
    
    /**
     * Get module configuration values
     */
    public function getModuleConfig($moduleId) {
        if (!isset($this->modulesData['config'])) {
            $this->modulesData['config'] = [];
        }
        
        return $this->modulesData['config'][$moduleId] ?? [];
    }
    
    /**
     * Save module configuration
     */
    public function saveModuleConfig($moduleId, $config) {
        $ok = $this->mutateModulesData(function ($data) use ($moduleId, $config) {
            $data['config'] = $data['config'] ?? [];
            $data['config'][$moduleId] = $config;
            return $data;
        });
        if (!$ok) {
            return ['success' => false, 'message' => 'Failed to save configuration. Please retry.'];
        }

        return ['success' => true, 'message' => 'Configuration saved successfully'];
    }
    
    /**
     * Check if module has configuration
     */
    public function hasConfiguration($moduleId) {
        $configSchemaFile = $this->modulesDir . '/' . $moduleId . '/config.json';
        return file_exists($configSchemaFile);
    }
    
    /**
     * Get configuration value for a specific module setting
     */
    public function getModuleConfigValue($moduleId, $key, $default = null) {
        $config = $this->getModuleConfig($moduleId);
        return $config[$key] ?? $default;
    }
}
