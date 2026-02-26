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

function rrmdir($dir) {
        if (!is_dir($dir)) {
                return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                        continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                if (is_dir($path)) {
                        rrmdir($path);
                } else {
                        @unlink($path);
                }
        }
        @rmdir($dir);
}

function safeZipEntryName($name) {
        $name = str_replace('\\', '/', $name);
        if ($name === '' || strpos($name, '../') !== false || str_starts_with($name, '/')) {
                return null;
        }
        return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $moduleId = $_POST['module_id'] ?? '';

        if ($action === 'upload_zip' && isset($_FILES['module_zip'])) {
                $file = $_FILES['module_zip'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Upload failed. Please try again.';
                } else {
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        if ($ext !== 'zip') {
                                $error = 'Only .zip files are allowed.';
                        } else {
                                $zip = new ZipArchive();
                                if ($zip->open($file['tmp_name']) === true) {
                                        $tmpRoot = sys_get_temp_dir() . '/simplephp_mod_' . bin2hex(random_bytes(4));
                                        mkdir($tmpRoot, 0755, true);

                                        $topLevel = null;
                                        for ($i = 0; $i < $zip->numFiles; $i++) {
                                                $entry = $zip->getNameIndex($i);
                                                $entry = safeZipEntryName($entry);
                                                if ($entry === null) {
                                                        $error = 'Invalid ZIP entry detected.';
                                                        break;
                                                }
                                                $parts = explode('/', trim($entry, '/'));
                                                if (empty($parts[0])) {
                                                        continue;
                                                }
                                                if ($topLevel === null) {
                                                        $topLevel = $parts[0];
                                                } elseif ($topLevel !== $parts[0]) {
                                                        $error = 'ZIP must contain a single top-level folder.';
                                                        break;
                                                }
                                        }

                                        if ($error === null && $topLevel !== null) {
                                                $zip->extractTo($tmpRoot);
                                                $zip->close();

                                                $modulePath = $tmpRoot . '/' . $topLevel;
                                                $modulesDir = __DIR__ . '/../modules';
                                                $targetPath = $modulesDir . '/' . $topLevel;

                                                if (!is_dir($modulePath)) {
                                                        $error = 'Module folder is missing in the ZIP.';
                                                } elseif (file_exists($targetPath)) {
                                                        $error = 'Module folder already exists.';
                                                } elseif (!file_exists($modulePath . '/module.json') || !file_exists($modulePath . '/module.php')) {
                                                        $error = 'module.json and module.php are required.';
                                                } else {
                                                        if (!is_dir($modulesDir)) {
                                                                mkdir($modulesDir, 0755, true);
                                                        }

                                                        if (@rename($modulePath, $targetPath)) {
                                                                $message = 'Module uploaded successfully.';
                                                        } else {
                                                                $error = 'Failed to move module into place.';
                                                        }
                                                }
                                        }

                                        $zip->close();
                                        rrmdir($tmpRoot);
                                } else {
                                        $error = 'Could not open ZIP file.';
                                }
                        }
                }
        } elseif ($action === 'install') {
                $result = $moduleManager->installModule($moduleId);
                $result['success'] ? $message = $result['message'] : $error = $result['message'];
        } elseif ($action === 'uninstall') {
                $result = $moduleManager->uninstallModule($moduleId);
                $result['success'] ? $message = $result['message'] : $error = $result['message'];
        } elseif ($action === 'activate') {
                $result = $moduleManager->activateModule($moduleId);
                $result['success'] ? $message = $result['message'] : $error = $result['message'];
        } elseif ($action === 'deactivate') {
                $result = $moduleManager->deactivateModule($moduleId);
                $result['success'] ? $message = $result['message'] : $error = $result['message'];
        }
}

$modules = $moduleManager->discoverModules();
uasort($modules, function($a, $b) {
        return strcasecmp($a['name'] ?? $a['id'], $b['name'] ?? $b['id']);
});

$manualPath = __DIR__ . '/../MODULES.md';
$manualText = file_exists($manualPath) ? trim(file_get_contents($manualPath)) : '';
?>
<?php
$page_title = 'Modules';
$page_header = [
    'title' => 'Module Manager',
    'subtitle' => 'Install, activate, and upload custom modules'
];

$breadcrumb = [
    ['text' => 'Admin', 'url' => '#', 'active' => false],
    ['text' => 'Modules', 'url' => '#', 'active' => true]
];

ob_start();
?>

<div class="container-fluid">
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

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Installed Modules</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($modules)): ?>
                        <p class="text-muted">No modules found.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($modules as $module): ?>
                                <div class="col-lg-4 col-md-6 mb-20">
                                    <div class="card" style="margin-bottom: 0;">
                                        <div class="card-body">
                                            <h6 style="font-weight: 600; margin-bottom: 6px;">
                                                <?php echo htmlspecialchars($module['name'] ?? $module['id']); ?>
                                            </h6>
                                            <p class="text-muted" style="min-height: 40px; font-size: 13px;">
                                                <?php echo htmlspecialchars($module['description'] ?? 'No description provided.'); ?>
                                            </p>
                                            <div class="d-flex gap-10 flex-wrap mb-15">
                                                <span class="badge badge-primary">v<?php echo htmlspecialchars($module['version'] ?? '0.0.0'); ?></span>
                                                <span class="badge badge-warning"><?php echo htmlspecialchars($module['author'] ?? 'Unknown author'); ?></span>
                                                <?php if (!empty($module['installed'])): ?>
                                                    <span class="badge badge-success">Installed</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Not installed</span>
                                                <?php endif; ?>
                                                <?php if (!empty($module['active'])): ?>
                                                    <span class="badge badge-primary">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Inactive</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex gap-10 flex-wrap">
                                                <?php if (empty($module['installed'])): ?>
                                                    <form method="post">
                                                        <input type="hidden" name="action" value="install">
                                                        <input type="hidden" name="module_id" value="<?php echo htmlspecialchars($module['id']); ?>">
                                                        <button class="btn btn-primary btn-sm" type="submit">Install</button>
                                                    </form>
                                                <?php else: ?>
                                                    <?php if (empty($module['active'])): ?>
                                                        <form method="post">
                                                            <input type="hidden" name="action" value="activate">
                                                            <input type="hidden" name="module_id" value="<?php echo htmlspecialchars($module['id']); ?>">
                                                            <button class="btn btn-primary btn-sm" type="submit">Activate</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="post">
                                                            <input type="hidden" name="action" value="deactivate">
                                                            <input type="hidden" name="module_id" value="<?php echo htmlspecialchars($module['id']); ?>">
                                                            <button class="btn btn-outline-secondary btn-sm" type="submit">Deactivate</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form method="post" onsubmit="return confirm('Uninstall this module?')">
                                                        <input type="hidden" name="action" value="uninstall">
                                                        <input type="hidden" name="module_id" value="<?php echo htmlspecialchars($module['id']); ?>">
                                                        <button class="btn btn-outline-danger btn-sm" type="submit">Uninstall</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-20" style="margin-top: 20px;">
        <div class="col-lg-6 mb-20">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Upload Module (ZIP)</h5>
                    <p class="text-muted mt-2">Upload a ZIP with a single top-level folder containing module.json and module.php.</p>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" class="form-default" data-validate>
                        <input type="hidden" name="action" value="upload_zip">
                        <div class="form-group">
                            <label class="form-label required">Module ZIP</label>
                            <input type="file" name="module_zip" accept=".zip" class="form-control" data-required>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="upload"></i> Upload Module
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-20">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Module Manual</h5>
                    <p class="text-muted mt-2">Quick reference from MODULES.md</p>
                </div>
                <div class="card-body">
                    <?php if ($manualText): ?>
                        <pre style="white-space: pre-wrap; margin: 0; font-size: 12px;"><?php echo htmlspecialchars($manualText); ?></pre>
                    <?php else: ?>
                        <p class="text-muted">No manual available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/theme/base-layout.php';
?>
