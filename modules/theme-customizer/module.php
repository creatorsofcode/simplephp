<?php
/**
 * Theme Customizer Module
 * Customize website appearance
 */

// Module initialization
function module_init_theme_customizer() {
    // Nothing special needed on init
}

// Hook: Add custom CSS to head
function module_hook_theme_customizer_page_head($data) {
    global $moduleManager;
    
    if (!$moduleManager) {
        return '';
    }
    
    $config = $moduleManager->getModuleConfig('theme-customizer');
    
    // Default values
    $primaryColor = $config['primary_color'] ?? '#667eea';
    $secondaryColor = $config['secondary_color'] ?? '#764ba2';
    $textColor = $config['text_color'] ?? '#333333';
    $bgColor = $config['background_color'] ?? '#ffffff';
    $fontSize = $config['font_size'] ?? '16';
    $borderRadius = $config['border_radius'] ?? '8';
    $containerWidth = $config['container_width'] ?? '1140';
    $fontFamily = $config['font_family'] ?? 'system';
    $enableShadows = $config['enable_shadows'] ?? true;
    $enableAnimations = $config['enable_animations'] ?? true;
    $customCSS = $config['custom_css'] ?? '';
    $googleFontsUrl = $config['google_fonts_url'] ?? '';
    
    // Font family mapping
    $fontFamilies = [
        'system' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        'roboto' => '"Roboto", sans-serif',
        'open-sans' => '"Open Sans", sans-serif',
        'lato' => '"Lato", sans-serif',
        'montserrat' => '"Montserrat", sans-serif',
        'poppins' => '"Poppins", sans-serif'
    ];
    
    $selectedFont = $fontFamilies[$fontFamily] ?? $fontFamilies['system'];
    
    $output = '';
    
    // Load Google Fonts if specified
    if (!empty($googleFontsUrl)) {
        $output .= '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        $output .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        $output .= '<link href="' . htmlspecialchars($googleFontsUrl) . '" rel="stylesheet">' . "\n";
    } elseif ($fontFamily !== 'system') {
        // Load from Google Fonts automatically
        $fontName = str_replace('-', '+', ucwords($fontFamily, '-'));
        $output .= '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        $output .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        $output .= '<link href="https://fonts.googleapis.com/css2?family=' . $fontName . ':wght@300;400;600;700&display=swap" rel="stylesheet">' . "\n";
    }
    
    // Generate CSS
    $output .= '<style id="theme-customizer-styles">' . "\n";
    $output .= ':root {' . "\n";
    $output .= '  --primary-color: ' . $primaryColor . ';' . "\n";
    $output .= '  --secondary-color: ' . $secondaryColor . ';' . "\n";
    $output .= '  --text-color: ' . $textColor . ';' . "\n";
    $output .= '  --bg-color: ' . $bgColor . ';' . "\n";
    $output .= '  --font-size: ' . $fontSize . 'px;' . "\n";
    $output .= '  --border-radius: ' . $borderRadius . 'px;' . "\n";
    $output .= '  --container-width: ' . $containerWidth . ($containerWidth !== '100%' ? 'px' : '') . ';' . "\n";
    $output .= '}' . "\n\n";
    
    $output .= 'body {' . "\n";
    $output .= '  font-family: ' . $selectedFont . ';' . "\n";
    $output .= '  font-size: var(--font-size);' . "\n";
    $output .= '  color: var(--text-color);' . "\n";
    $output .= '  background-color: var(--bg-color);' . "\n";
    $output .= '}' . "\n\n";
    
    $output .= '.container, .wrap {' . "\n";
    $output .= '  max-width: var(--container-width);' . "\n";
    $output .= '}' . "\n\n";
    
    $output .= 'button, .btn, input[type="submit"] {' . "\n";
    $output .= '  background: var(--primary-color);' . "\n";
    $output .= '  border-radius: var(--border-radius);' . "\n";
    if ($enableShadows) {
        $output .= '  box-shadow: 0 2px 8px rgba(0,0,0,0.1);' . "\n";
    }
    if ($enableAnimations) {
        $output .= '  transition: all 0.3s ease;' . "\n";
    }
    $output .= '}' . "\n\n";
    
    $output .= 'a {' . "\n";
    $output .= '  color: var(--primary-color);' . "\n";
    if ($enableAnimations) {
        $output .= '  transition: color 0.2s ease;' . "\n";
    }
    $output .= '}' . "\n\n";
    
    $output .= '.card, .module-card {' . "\n";
    $output .= '  border-radius: var(--border-radius);' . "\n";
    if ($enableShadows) {
        $output .= '  box-shadow: 0 4px 12px rgba(0,0,0,0.1);' . "\n";
    }
    $output .= '}' . "\n\n";
    
    if ($enableAnimations) {
        $output .= 'button:hover, .btn:hover {' . "\n";
        $output .= '  transform: translateY(-2px);' . "\n";
        $output .= '  box-shadow: 0 4px 16px rgba(0,0,0,0.2);' . "\n";
        $output .= '}' . "\n\n";
    }
    
    // Add custom CSS if provided
    if (!empty($customCSS)) {
        $output .= "\n/* Custom CSS */\n";
        $output .= $customCSS . "\n";
    }
    
    $output .= '</style>' . "\n";
    
    return $output;
}

// Get current theme config
function theme_customizer_get_config() {
    global $moduleManager;
    
    if (!$moduleManager) {
        return [];
    }
    
    return $moduleManager->getModuleConfig('theme-customizer');
}
