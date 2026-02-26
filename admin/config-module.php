<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/ModuleManager.php';

$moduleManager = new ModuleManager();
$message = null;
$error = null;
$moduleId = $_GET['module'] ?? '';

// Get all installed and active modules
$allModules = $moduleManager->discoverModules();
$modules = array_filter($allModules, function($m) {
    return $m['installed'] ?? false;
});

uasort($modules, function($a, $b) {
    return strcasecmp($a['name'] ?? $a['id'], $b['name'] ?? $b['id']);
});

// Handle configuration updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $moduleId) {
    $module = $modules[$moduleId] ?? null;
    
    if ($module) {
        $configSchema = $moduleManager->getModuleConfigSchema($moduleId);
        
        if ($configSchema) {
            $config = [];
            
            foreach ($configSchema['fields'] ?? [] as $field) {
                $fieldName = $field['name'] ?? '';
                
                if ($field['type'] === 'checkbox') {
                    $config[$fieldName] = isset($_POST[$fieldName]);
                } else if (!empty($_POST[$fieldName])) {
                    $config[$fieldName] = $_POST[$fieldName];
                } else if (isset($field['default'])) {
                    $config[$fieldName] = $field['default'];
                }
            }
            
            $result = $moduleManager->saveModuleConfig($moduleId, $config);
            
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get current module config values
$currentConfig = [];
$configSchema = null;

if ($moduleId && isset($modules[$moduleId])) {
    $configSchema = $moduleManager->getModuleConfigSchema($moduleId);
    $currentConfig = $moduleManager->getModuleConfig($moduleId);
}
?>
<?php
$page_title = 'Module Configuration';
$page_header = [
    'title' => 'Module Configuration',
    'subtitle' => 'Configure installed and active modules'
];

$breadcrumb = [
    ['text' => 'Admin', 'url' => '#', 'active' => false],
    ['text' => 'Module Config', 'url' => '#', 'active' => true]
];

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 mb-20">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Modules</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($modules)): ?>
                        <p class="text-muted">No modules installed.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($modules as $mod): ?>
                                <?php $hasConfig = $moduleManager->hasConfiguration($mod['id']); ?>
                                <a
                                    href="?module=<?php echo urlencode($mod['id']); ?>"
                                    class="list-group-item list-group-item-action <?php echo $moduleId === $mod['id'] ? 'active' : ''; ?>"
                                    style="opacity: <?php echo $hasConfig ? '1' : '0.6'; ?>;"
                                >
                                    <div style="font-weight: 600;">
                                        <?php echo htmlspecialchars($mod['name'] ?? $mod['id']); ?>
                                    </div>
                                    <?php if (!$hasConfig): ?>
                                        <small class="text-muted">No config</small>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-9 mb-20">
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!$moduleId): ?>
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted">Select a module from the list to configure it.</p>
                    </div>
                </div>
            <?php elseif (!$configSchema): ?>
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted">This module has no configuration options.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <?php $module = $modules[$moduleId] ?? []; ?>
                        <h5 class="card-title"><?php echo htmlspecialchars($module['name'] ?? $moduleId); ?></h5>
                        <?php if (!empty($configSchema['description'])): ?>
                            <p class="text-muted mt-2"><?php echo htmlspecialchars($configSchema['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="post" class="form-default" data-validate>
                            <?php foreach ($configSchema['fields'] ?? [] as $field): ?>
                                <?php
                                    $fieldName = $field['name'] ?? '';
                                    $fieldType = $field['type'] ?? 'text';
                                    $label = $field['label'] ?? '';
                                    $help = $field['help'] ?? '';
                                    $placeholder = $field['placeholder'] ?? '';
                                    $value = $currentConfig[$fieldName] ?? ($field['default'] ?? '');
                                ?>

                                <div class="form-group">
                                    <?php if ($fieldType === 'checkbox'): ?>
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                class="form-check-input"
                                                name="<?php echo htmlspecialchars($fieldName); ?>"
                                                id="<?php echo htmlspecialchars($fieldName); ?>"
                                                <?php echo $value ? 'checked' : ''; ?>
                                            >
                                            <label class="form-check-label" for="<?php echo htmlspecialchars($fieldName); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </label>
                                        </div>
                                    <?php else: ?>
                                        <label class="form-label" for="<?php echo htmlspecialchars($fieldName); ?>">
                                            <?php echo htmlspecialchars($label); ?>
                                        </label>

                                        <?php if ($fieldType === 'textarea'): ?>
                                            <textarea
                                                class="form-control"
                                                name="<?php echo htmlspecialchars($fieldName); ?>"
                                                id="<?php echo htmlspecialchars($fieldName); ?>"
                                                placeholder="<?php echo htmlspecialchars($placeholder); ?>"
                                            ><?php echo htmlspecialchars($value); ?></textarea>
                                        <?php elseif ($fieldType === 'select'): ?>
                                            <select
                                                class="form-select"
                                                name="<?php echo htmlspecialchars($fieldName); ?>"
                                                id="<?php echo htmlspecialchars($fieldName); ?>"
                                            >
                                                <?php foreach ($field['options'] ?? [] as $optValue => $optLabel): ?>
                                                    <option value="<?php echo htmlspecialchars($optValue); ?>" <?php echo $value === $optValue ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($optLabel); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <input
                                                type="<?php echo htmlspecialchars($fieldType); ?>"
                                                class="form-control"
                                                name="<?php echo htmlspecialchars($fieldName); ?>"
                                                id="<?php echo htmlspecialchars($fieldName); ?>"
                                                placeholder="<?php echo htmlspecialchars($placeholder); ?>"
                                                value="<?php echo htmlspecialchars($value); ?>"
                                            >
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($help): ?>
                                        <small class="text-muted d-block mt-5"><?php echo htmlspecialchars($help); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="save"></i> Save Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/theme/base-layout.php';
?>
