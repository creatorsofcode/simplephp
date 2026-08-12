<?php
/**
 * SEO Manager Module - Installation
 */

require_once __DIR__ . '/../../includes/Security.php';

function module_install_seo_manager() {
    // Create SEO settings file
    $seoFile = __DIR__ . '/seo-settings.json';

    $defaultSettings = [
        'default_meta_description' => 'Welcome to our website',
        'default_meta_keywords' => 'cms, php, website',
        'og_image' => '',
        'twitter_handle' => '@yoursite',
        'enable_sitemap' => true
    ];

    simplephp_json_write($seoFile, $defaultSettings);

    return true;
}
