<?php
/**
 * Analytics Tracker Module - Installation
 */

require_once __DIR__ . '/../../includes/Security.php';

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
    simplephp_json_write($settingsFile, $defaultSettings);

    // Create counter file
    $counterFile = __DIR__ . '/visit-counter.json';
    $counterData = [
        'total_visits' => 0,
        'unique_visits' => 0,
        'last_reset' => date('Y-m-d H:i:s')
    ];
    simplephp_json_write($counterFile, $counterData);

    return true;
}
