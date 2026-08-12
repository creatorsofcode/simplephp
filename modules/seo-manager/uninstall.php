<?php
/**
 * SEO Manager Module - Uninstallation
 */

function module_uninstall_seo_manager() {
    // Remove SEO settings file
    $seoFile = __DIR__ . '/seo-settings.json';
    
    if (file_exists($seoFile)) {
        unlink($seoFile);
    }
    
    return true;
}
