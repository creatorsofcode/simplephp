<?php
/**
 * Analytics Tracker Module - Uninstallation
 */

function module_uninstall_analytics_tracker() {
    // Remove settings file
    $settingsFile = __DIR__ . '/analytics-settings.json';
    if (file_exists($settingsFile)) {
        unlink($settingsFile);
    }
    
    // Remove counter file
    $counterFile = __DIR__ . '/visit-counter.json';
    if (file_exists($counterFile)) {
        unlink($counterFile);
    }
    
    return true;
}
