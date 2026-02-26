<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$themesDir = __DIR__ . '/../themes';
$themeConfigFile = __DIR__ . '/../data/theme-config.json';

function getDefaultThemeConfig(): array
{
    return [
        'active_theme' => 'light',
        'enabled_themes' => ['light']
    ];
}

function loadThemeConfig(string $themeConfigFile): array
{
    if (!file_exists($themeConfigFile)) {
        $defaultConfig = getDefaultThemeConfig();
        file_put_contents($themeConfigFile, json_encode($defaultConfig, JSON_PRETTY_PRINT));
        return $defaultConfig;
    }

    $config = json_decode(file_get_contents($themeConfigFile), true);
    if (!is_array($config)) {
        $config = getDefaultThemeConfig();
    }

    if (!isset($config['enabled_themes']) || !is_array($config['enabled_themes'])) {
        $config['enabled_themes'] = getDefaultThemeConfig()['enabled_themes'];
    }

    $config['enabled_themes'] = array_values(array_filter($config['enabled_themes'], 'is_string'));
    if (empty($config['enabled_themes'])) {
        $config['enabled_themes'] = getDefaultThemeConfig()['enabled_themes'];
    }

    if (!isset($config['active_theme']) || !is_string($config['active_theme'])) {
        $config['active_theme'] = $config['enabled_themes'][0];
    }

    if (!in_array($config['active_theme'], $config['enabled_themes'], true)) {
        $config['active_theme'] = $config['enabled_themes'][0];
    }

    return $config;
}

function saveThemeConfig(string $themeConfigFile, array $config): void
{
    file_put_contents($themeConfigFile, json_encode($config, JSON_PRETTY_PRINT));
}

$message = null;
$error = null;
$config = loadThemeConfig($themeConfigFile);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'set_theme') {
        $themeId = $_POST['theme_id'] ?? '';
        $themePath = $themesDir . '/' . $themeId;

        if (!is_dir($themePath) || !file_exists($themePath . '/theme.json')) {
            $error = 'Theme not found.';
        } elseif (!in_array($themeId, $config['enabled_themes'], true)) {
            $error = 'Theme is disabled.';
        } else {
            $config['active_theme'] = $themeId;
            saveThemeConfig($themeConfigFile, $config);
            $message = 'Theme activated successfully.';
        }
    }

    if ($action === 'toggle_theme') {
        $themeId = $_POST['theme_id'] ?? '';
        $enabled = ($_POST['enabled'] ?? '0') === '1';
        $themePath = $themesDir . '/' . $themeId;

        if (!is_dir($themePath) || !file_exists($themePath . '/theme.json')) {
            $error = 'Theme not found.';
        } else {
            $enabledThemes = $config['enabled_themes'];

            if ($enabled) {
                if (!in_array($themeId, $enabledThemes, true)) {
                    $enabledThemes[] = $themeId;
                }
                $message = 'Theme enabled.';
            } else {
                if (count($enabledThemes) <= 1 && in_array($themeId, $enabledThemes, true)) {
                    $error = 'At least one theme must remain enabled.';
                } else {
                    $enabledThemes = array_values(array_filter($enabledThemes, function ($id) use ($themeId) {
                        return $id !== $themeId;
                    }));

                    if ($config['active_theme'] === $themeId) {
                        $config['active_theme'] = $enabledThemes[0] ?? 'light';
                    }
                    $message = 'Theme disabled.';
                }
            }

            if ($error === null) {
                $config['enabled_themes'] = $enabledThemes;
                saveThemeConfig($themeConfigFile, $config);
            }
        }
    }
}

$themes = [];
if (is_dir($themesDir)) {
    $themeFolders = array_diff(scandir($themesDir), ['.', '..']);
    foreach ($themeFolders as $themeFolder) {
        $themePath = $themesDir . '/' . $themeFolder;
        $themeJsonFile = $themePath . '/theme.json';
        if (is_dir($themePath) && file_exists($themeJsonFile)) {
            $themeData = json_decode(file_get_contents($themeJsonFile), true);
            if (is_array($themeData)) {
                $themeData['id'] = $themeFolder;
                $themeData['enabled'] = in_array($themeFolder, $config['enabled_themes'], true);
                $themes[] = $themeData;
            }
        }
    }
}

usort($themes, function ($a, $b) use ($config) {
    if ($a['id'] === $config['active_theme']) {
        return -1;
    }
    if ($b['id'] === $config['active_theme']) {
        return 1;
    }
    if (($a['enabled'] ?? false) === ($b['enabled'] ?? false)) {
        return strcasecmp($a['name'] ?? $a['id'], $b['name'] ?? $b['id']);
    }
    return ($a['enabled'] ?? false) ? -1 : 1;
});

$page_title = 'Themes';
$page_header = [
    'title' => 'Theme Manager',
    'subtitle' => 'Choose the active website theme'
];

$breadcrumb = [
    ['text' => 'Admin', 'url' => '#', 'active' => false],
    ['text' => 'Themes', 'url' => '#', 'active' => true]
];

ob_start();
?>
<style>
    .theme-preview-wrap {
        height: 140px;
        border-radius: 12px;
        padding: 10px;
        position: relative;
        overflow: hidden;
        border: 2px solid var(--preview-border, #e3e6f0);
        background: var(--preview-bg, #ffffff);
    }

    .theme-preview-bar {
        height: 8px;
        background: var(--preview-primary, #3b82f6);
        border-radius: 6px;
        margin-bottom: 8px;
    }

    .theme-preview-body {
        display: grid;
        gap: 6px;
        font-size: 12px;
    }

    .theme-preview-chip {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 600;
        background: var(--preview-secondary, #f3f4f6);
        color: var(--preview-text, #111827);
        width: max-content;
    }

    .theme-preview-title {
        font-weight: 700;
        color: var(--preview-text, #111827);
        font-size: 12px;
    }

    .theme-preview-line {
        height: 3px;
        width: 60%;
        border-radius: 999px;
        background: var(--preview-accent, #111827);
        opacity: 0.7;
    }

    .theme-preview-actions {
        display: flex;
        gap: 6px;
    }

    .theme-preview-btn {
        font-size: 9px;
        font-weight: 700;
        padding: 3px 6px;
        border-radius: 6px;
        background: var(--preview-primary, #3b82f6);
        color: var(--preview-bg, #ffffff);
    }

    .theme-preview-btn.outline {
        background: transparent;
        border: 1px solid var(--preview-accent, #111827);
        color: var(--preview-accent, #111827);
    }

    .theme-preview-card {
        position: absolute;
        right: 10px;
        bottom: 10px;
        width: 80px;
        height: 44px;
        border-radius: 10px;
        background: var(--preview-secondary, #f3f4f6);
        border: 1px solid var(--preview-border, #e3e6f0);
        padding: 6px;
        display: grid;
        gap: 4px;
    }

    .theme-preview-card-line {
        height: 4px;
        border-radius: 999px;
        background: var(--preview-text, #111827);
        opacity: 0.7;
    }

    .theme-preview-card-line.short {
        width: 60%;
    }

    .preview-layout-b .theme-preview-bar {
        width: 8px;
        height: 100%;
        position: absolute;
        left: 10px;
        top: 10px;
        margin: 0;
    }

    .preview-layout-b .theme-preview-body {
        margin-left: 16px;
    }

    .preview-layout-b .theme-preview-card {
        right: auto;
        left: 18px;
    }

    .preview-layout-c::before {
        content: '';
        position: absolute;
        top: -20px;
        right: -20px;
        width: 90px;
        height: 90px;
        background: var(--preview-accent, #111827);
        opacity: 0.2;
        transform: rotate(20deg);
        border-radius: 16px;
    }

    .preview-layout-c .theme-preview-bar {
        background: var(--preview-accent, #111827);
    }
</style>
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

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Available Themes</h5>
            <p class="text-muted" style="margin-bottom: 0;">Active theme: <strong><?php echo htmlspecialchars($config['active_theme']); ?></strong></p>
        </div>
        <div class="card-body">
            <?php if (empty($themes)): ?>
                <p class="text-muted">No themes found.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($themes as $index => $theme): ?>
                        <?php
                        $colors = $theme['colors'] ?? [];
                        $background = $colors['background'] ?? '#ffffff';
                        $text = $colors['text'] ?? '#111827';
                        $primary = $colors['primary'] ?? '#3B82F6';
                        $secondary = $colors['secondary'] ?? '#F3F4F6';
                        $accent = $colors['accent'] ?? '#111827';
                        $isActive = ($theme['id'] ?? '') === $config['active_theme'];
                        $variantClass = ['preview-layout-a', 'preview-layout-b', 'preview-layout-c'][$index % 3];
                        $previewStyle = sprintf(
                            '--preview-bg: %s; --preview-text: %s; --preview-primary: %s; --preview-secondary: %s; --preview-accent: %s; --preview-border: %s;',
                            $background,
                            $text,
                            $primary,
                            $secondary,
                            $accent,
                            $colors['border'] ?? '#e3e6f0'
                        );
                        ?>
                        <div class="col-lg-4 col-md-6 mb-20">
                            <div class="card" style="height: 100%;">
                                <div class="theme-preview-wrap <?php echo htmlspecialchars($variantClass); ?>" style="<?php echo htmlspecialchars($previewStyle); ?>">
                                    <div class="theme-preview-bar"></div>
                                    <div class="theme-preview-body">
                                        <div class="theme-preview-chip">Menu</div>
                                        <div class="theme-preview-title">Hero Title</div>
                                        <div class="theme-preview-line"></div>
                                        <div class="theme-preview-actions">
                                            <span class="theme-preview-btn">Primary</span>
                                            <span class="theme-preview-btn outline">Ghost</span>
                                        </div>
                                    </div>
                                    <div class="theme-preview-card">
                                        <div class="theme-preview-card-line"></div>
                                        <div class="theme-preview-card-line short"></div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 style="font-weight: 600; margin-bottom: 6px;">
                                        <?php echo htmlspecialchars($theme['name'] ?? $theme['id']); ?>
                                    </h6>
                                    <p class="text-muted" style="min-height: 40px; font-size: 13px;">
                                        <?php echo htmlspecialchars($theme['description'] ?? ''); ?>
                                    </p>
                                    <div class="d-flex gap-10 flex-wrap mb-15">
                                        <span class="badge badge-primary"><?php echo $isActive ? 'Active' : 'Inactive'; ?></span>
                                        <span class="badge <?php echo !empty($theme['enabled']) ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo !empty($theme['enabled']) ? 'Enabled' : 'Disabled'; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex gap-10" style="margin-bottom: 12px;">
                                        <span title="Primary" style="width: 16px; height: 16px; border-radius: 50%; background: <?php echo htmlspecialchars($primary); ?>; border: 1px solid #e3e6f0;"></span>
                                        <span title="Secondary" style="width: 16px; height: 16px; border-radius: 50%; background: <?php echo htmlspecialchars($secondary); ?>; border: 1px solid #e3e6f0;"></span>
                                        <span title="Accent" style="width: 16px; height: 16px; border-radius: 50%; background: <?php echo htmlspecialchars($accent); ?>; border: 1px solid #e3e6f0;"></span>
                                    </div>
                                    <div class="d-flex gap-10 flex-wrap">
                                        <form method="post">
                                            <input type="hidden" name="action" value="set_theme">
                                            <input type="hidden" name="theme_id" value="<?php echo htmlspecialchars($theme['id']); ?>">
                                            <button class="btn btn-primary btn-sm" type="submit" <?php echo $isActive || empty($theme['enabled']) ? 'disabled' : ''; ?>>
                                                <?php echo $isActive ? 'Active' : 'Activate'; ?>
                                            </button>
                                        </form>
                                        <form method="post">
                                            <input type="hidden" name="action" value="toggle_theme">
                                            <input type="hidden" name="theme_id" value="<?php echo htmlspecialchars($theme['id']); ?>">
                                            <input type="hidden" name="enabled" value="<?php echo !empty($theme['enabled']) ? '0' : '1'; ?>">
                                            <button class="btn btn-outline-secondary btn-sm" type="submit">
                                                <?php echo !empty($theme['enabled']) ? 'Disable' : 'Enable'; ?>
                                            </button>
                                        </form>
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
<?php
$page_content = ob_get_clean();
include __DIR__ . '/theme/base-layout.php';
