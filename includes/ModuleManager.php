<?php
/**
 * ModuleManager - Core Module System
 * Manages module discovery, installation, activation, and execution
 */
class ModuleManager {
    private $modulesDir;
    private $dataFile;
    private $modulesData;
    private $loadedModules = [];
    
    public function __construct($modulesDir = null, $dataFile = null) {
        $this->modulesDir = $modulesDir ?? __DIR__ . '/../modules';
        $this->dataFile = $dataFile ?? __DIR__ . '/../data/modules.json';
        $this->loadModulesData();
    }
    
    /**
     * Load modules data from JSON file
     */
    private function loadModulesData() {
        if (file_exists($this->dataFile)) {
            $this->modulesData = json_decode(file_get_contents($this->dataFile), true);
        } else {
            $this->modulesData = ['installed' => [], 'active' => []];
            $this->saveModulesData();
        }
    }
    
    /**
     * Save modules data to JSON file
     */
    private function saveModulesData() {
        $dir = dirname($this->dataFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->dataFile, json_encode($this->modulesData, JSON_PRETTY_PRINT));
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
        if (!in_array($moduleId, $this->modulesData['installed'])) {
            $this->modulesData['installed'][] = $moduleId;
        }
        
        $this->saveModulesData();
        
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
        $this->modulesData['installed'] = array_values(array_diff($this->modulesData['installed'], [$moduleId]));
        $this->saveModulesData();
        
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
        if (!in_array($moduleId, $this->modulesData['active'])) {
            $this->modulesData['active'][] = $moduleId;
        }
        
        $this->saveModulesData();
        
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
        $this->modulesData['active'] = array_values(array_diff($this->modulesData['active'], [$moduleId]));
        $this->saveModulesData();
        
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
        if (!isset($this->modulesData['config'])) {
            $this->modulesData['config'] = [];
        }
        
        $this->modulesData['config'][$moduleId] = $config;
        $this->saveModulesData();
        
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
