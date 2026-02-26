<?php
/**
 * Analytics Tracker Module - Installation
 */

function module_install_analytics_tracker() {
    // Create settings file
    $settingsFile = __DIR__ . '/analytics-settings.json';
    $defaultSettings = [
        'google_analytics_id' => '',
        'facebook_pixel_id' => '',
        'custom_head_code' => '',
        'custom_body_code' => '',
        'enable_visit_counter' => true
    ];
    file_put_contents($settingsFile, json_encode($defaultSettings, JSON_PRETTY_PRINT));
    
    // Create counter file
    $counterFile = __DIR__ . '/visit-counter.json';
    $counterData = [
        'total_visits' => 0,
        'unique_visits' => 0,
        'last_reset' => date('Y-m-d H:i:s')
    ];
    file_put_contents($counterFile, json_encode($counterData, JSON_PRETTY_PRINT));
    
    return true;
}
