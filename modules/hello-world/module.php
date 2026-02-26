<?php
/**
 * Hello World Module
 * A simple example module
 */

// Module initialization function
function module_init_hello_world() {
    // This runs when the module is loaded
    // You can register hooks, add filters, etc.
}

// Hook: Add custom CSS to head
function module_hook_hello_world_page_head($data) {
    global $moduleManager;
    $config = $moduleManager->getModuleConfig('hello-world');
    
    $color = $config['greeting_color'] ?? '#667eea';
    $position = $config['position'] ?? 'top';
    $customCss = $config['custom_css'] ?? '';
    
    if ($position === 'hidden') {
        return '';
    }
    
    $output = '<style>';
    $output .= '.hello-world-banner {';
    $output .= '  background: linear-gradient(135deg, ' . $color . ', ' . adjustColorBrightness($color, -20) . ');';
    $output .= '  color: white;';
    $output .= '  padding: 20px;';
    $output .= '  text-align: center;';
    $output .= '  margin: 20px 0;';
    $output .= '  border-radius: 10px;';
    $output .= '  box-shadow: 0 4px 15px rgba(0,0,0,0.2);';
    $output .= '  font-weight: 600;';
    $output .= '  font-size: 18px;';
    $output .= '}';
    $output .= '.hello-world-timestamp {';
    $output .= '  font-size: 14px;';
    $output .= '  opacity: 0.9;';
    $output .= '  margin-top: 8px;';
    $output .= '}';
    if (!empty($customCss)) {
        $output .= "\n" . $customCss;
    }
    $output .= '</style>';
    
    return $output;
}

// Hook: Add greeting banner to page
function module_hook_hello_world_page_content($data) {
    global $moduleManager;
    $config = $moduleManager->getModuleConfig('hello-world');
    
    $message = $config['greeting_message'] ?? 'Hello from the Hello World module!';
    $showTimestamp = $config['show_timestamp'] ?? true;
    $position = $config['position'] ?? 'top';
    
    if ($position === 'hidden') {
        return '';
    }
    
    $output = '<div class="hello-world-banner">';
    $output .= '  <div>👋 ' . htmlspecialchars($message) . '</div>';
    if ($showTimestamp) {
        $output .= '  <div class="hello-world-timestamp">Module loaded at: ' . date('Y-m-d H:i:s') . '</div>';
    }
    $output .= '</div>';
    
    return $output;
}

// Example function that other parts of the system can call
function hello_world_get_message() {
    return "👋 Hello! This module is working perfectly!";
}

// Helper function to adjust color brightness
function adjustColorBrightness($hex, $steps) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) 
           . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
           . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}
