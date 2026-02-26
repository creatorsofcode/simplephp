<?php
/**
 * Frontend Admin - Theme Management
 * Handles theme selection and configuration
 */

session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$themesDir = __DIR__ . '/themes';
$themeConfigFile = __DIR__ . '/data/theme-config.json';

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

function normalizeThemeId(string $rawId): string
{
    $normalized = strtolower(trim($rawId));
    $normalized = preg_replace('/[^a-z0-9\-]+/', '-', $normalized) ?? '';
    $normalized = trim($normalized, '-');
    return $normalized;
}

function sanitizeColor(string $color, string $fallback): string
{
    $color = trim($color);
    if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color)) {
        return strtoupper($color);
    }
    return $fallback;
}

// GET - Retrieve available themes
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_themes') {
    try {
        $themes = [];
        $config = loadThemeConfig($themeConfigFile);
        $enabledThemes = $config['enabled_themes'];
        
        if (is_dir($themesDir)) {
            $themeFolders = array_diff(scandir($themesDir), ['.', '..']);
            
            foreach ($themeFolders as $themeFolder) {
                $themePath = $themesDir . '/' . $themeFolder;
                $themeJsonFile = $themePath . '/theme.json';
                
                if (is_dir($themePath) && file_exists($themeJsonFile)) {
                    $themeData = json_decode(file_get_contents($themeJsonFile), true);
                    if ($themeData) {
                        $themeData['id'] = $themeFolder;
                        $themeData['enabled'] = in_array($themeFolder, $enabledThemes, true);
                        $themes[] = $themeData;
                    }
                }
            }
        }
        
        // Get active theme
        $activeTheme = $config['active_theme'] ?? 'light';
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'themes' => $themes,
            'active_theme' => $activeTheme
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// POST - Set active theme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'set_theme') {
        $themeId = $_POST['theme_id'] ?? '';
        $config = loadThemeConfig($themeConfigFile);
        $enabledThemes = $config['enabled_themes'];
        
        // Validate theme exists
        $themePath = $themesDir . '/' . $themeId;
        if (!is_dir($themePath) || !file_exists($themePath . '/theme.json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Theme not found']);
            exit;
        }

        if (!in_array($themeId, $enabledThemes, true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Theme is disabled']);
            exit;
        }
        
        try {
            // Update theme config
            $config['active_theme'] = $themeId;
            
            if (file_put_contents($themeConfigFile, json_encode($config, JSON_PRETTY_PRINT)) === false) {
                throw new Exception('Failed to save theme configuration');
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Theme activated successfully',
                'active_theme' => $themeId
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    if ($action === 'toggle_theme') {
        $themeId = $_POST['theme_id'] ?? '';
        $enabled = ($_POST['enabled'] ?? '0') === '1';

        $themePath = $themesDir . '/' . $themeId;
        if (!is_dir($themePath) || !file_exists($themePath . '/theme.json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Theme not found']);
            exit;
        }

        try {
            $config = loadThemeConfig($themeConfigFile);
            $enabledThemes = $config['enabled_themes'];

            if ($enabled) {
                if (!in_array($themeId, $enabledThemes, true)) {
                    $enabledThemes[] = $themeId;
                }
            } else {
                if (count($enabledThemes) <= 1 && in_array($themeId, $enabledThemes, true)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'At least one theme must remain enabled']);
                    exit;
                }
                $enabledThemes = array_values(array_filter($enabledThemes, function ($id) use ($themeId) {
                    return $id !== $themeId;
                }));

                if ($config['active_theme'] === $themeId) {
                    $config['active_theme'] = $enabledThemes[0] ?? 'light';
                }
            }

            $config['enabled_themes'] = $enabledThemes;
            saveThemeConfig($themeConfigFile, $config);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => $enabled ? 'Theme enabled' : 'Theme disabled',
                'active_theme' => $config['active_theme']
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'create_theme') {
        $themeName = trim($_POST['theme_name'] ?? '');
        $themeId = trim($_POST['theme_id'] ?? '');
        $themeDescription = trim($_POST['theme_description'] ?? '');
        $enabled = ($_POST['enabled'] ?? '1') === '1';

        if ($themeName === '') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Theme name is required']);
            exit;
        }

        if ($themeId === '') {
            $themeId = $themeName;
        }

        $themeId = normalizeThemeId($themeId);
        if ($themeId === '') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid theme ID']);
            exit;
        }

        $themePath = $themesDir . '/' . $themeId;
        if (is_dir($themePath)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Theme ID already exists']);
            exit;
        }

        $defaultColors = [
            'background' => '#FFFFFF',
            'text' => '#1F2937',
            'primary' => '#3B82F6',
            'secondary' => '#F3F4F6',
            'accent' => '#111827'
        ];

        $colors = [
            'background' => sanitizeColor($_POST['color_background'] ?? '', $defaultColors['background']),
            'text' => sanitizeColor($_POST['color_text'] ?? '', $defaultColors['text']),
            'primary' => sanitizeColor($_POST['color_primary'] ?? '', $defaultColors['primary']),
            'secondary' => sanitizeColor($_POST['color_secondary'] ?? '', $defaultColors['secondary']),
            'accent' => sanitizeColor($_POST['color_accent'] ?? '', $defaultColors['accent'])
        ];

        $themeData = [
            'name' => $themeName,
            'description' => $themeDescription !== '' ? $themeDescription : 'Custom theme',
            'version' => '1.0.0',
            'author' => 'Custom',
            'thumbnail' => 'thumbnail.png',
            'colors' => $colors,
            'active' => false
        ];

        $themeCss = ":root {\n";
        $themeCss .= "  --theme-bg: {$colors['background']};\n";
        $themeCss .= "  --theme-text: {$colors['text']};\n";
        $themeCss .= "  --theme-primary: {$colors['primary']};\n";
        $themeCss .= "  --theme-secondary: {$colors['secondary']};\n";
        $themeCss .= "  --theme-accent: {$colors['accent']};\n";
        $themeCss .= "}\n\n";
        $themeCss .= "body.theme-{$themeId} {\n";
        $themeCss .= "  background-color: var(--theme-bg);\n";
        $themeCss .= "  color: var(--theme-text);\n";
        $themeCss .= "}\n\n";
        $themeCss .= "a {\n";
        $themeCss .= "  color: var(--theme-primary);\n";
        $themeCss .= "}\n\n";
        $themeCss .= ".btn.primary {\n";
        $themeCss .= "  background-color: var(--theme-primary);\n";
        $themeCss .= "}\n";

        try {
            if (!mkdir($themePath, 0755, true) && !is_dir($themePath)) {
                throw new Exception('Failed to create theme directory');
            }

            if (file_put_contents($themePath . '/theme.json', json_encode($themeData, JSON_PRETTY_PRINT)) === false) {
                throw new Exception('Failed to write theme.json');
            }

            if (file_put_contents($themePath . '/theme.css', $themeCss) === false) {
                throw new Exception('Failed to write theme.css');
            }

            if ($enabled) {
                $config = loadThemeConfig($themeConfigFile);
                if (!in_array($themeId, $config['enabled_themes'], true)) {
                    $config['enabled_themes'][] = $themeId;
                    saveThemeConfig($themeConfigFile, $config);
                }
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Theme created successfully',
                'theme_id' => $themeId
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    exit;
}

// Default error response
header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
