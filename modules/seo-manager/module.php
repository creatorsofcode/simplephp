<?php
/**
 * SEO Manager Module
 * Advanced SEO optimization tools
 */

require_once __DIR__ . '/../../includes/Security.php';

// Module initialization
function module_init_seo_manager() {
    // Initialize SEO settings
    $seoFile = __DIR__ . '/seo-settings.json';
    if (!file_exists($seoFile)) {
        $defaultSettings = [
            'default_meta_description' => 'Welcome to our website',
            'default_meta_keywords' => 'cms, php, website',
            'og_image' => '',
            'twitter_handle' => '@yoursite',
            'enable_sitemap' => true
        ];
        simplephp_json_write($seoFile, $defaultSettings);
    }
}

// Hook: Add meta tags to pages
function module_hook_seo_manager_page_meta($pageData) {
    $seoFile = __DIR__ . '/seo-settings.json';
    $settings = simplephp_json_read($seoFile, []);
    
    $meta = [];
    
    // Basic meta tags
    $meta['description'] = $pageData['meta_description'] ?? $settings['default_meta_description'];
    $meta['keywords'] = $pageData['meta_keywords'] ?? $settings['default_meta_keywords'];
    
    // Open Graph tags
    $meta['og:title'] = $pageData['title'] ?? 'SimplePHP CMS';
    $meta['og:description'] = $meta['description'];
    $meta['og:type'] = 'website';
    
    if (!empty($settings['og_image'])) {
        $meta['og:image'] = $settings['og_image'];
    }
    
    // Twitter Card
    $meta['twitter:card'] = 'summary_large_image';
    $meta['twitter:site'] = $settings['twitter_handle'];
    
    return $meta;
}

// Function to generate sitemap
function seo_manager_generate_sitemap($pages) {
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    
    foreach ($pages as $pageId => $page) {
        $sitemap .= "  <url>\n";
        $sitemap .= "    <loc>" . htmlspecialchars($baseUrl . "/?page=" . $pageId) . "</loc>\n";
        $sitemap .= "    <changefreq>weekly</changefreq>\n";
        $sitemap .= "    <priority>0.8</priority>\n";
        $sitemap .= "  </url>\n";
    }
    
    $sitemap .= '</urlset>';
    
    return $sitemap;
}

// Get SEO settings
function seo_manager_get_settings() {
    $seoFile = __DIR__ . '/seo-settings.json';
    return simplephp_json_read($seoFile, []);
}

// Save SEO settings
function seo_manager_save_settings($settings) {
    $seoFile = __DIR__ . '/seo-settings.json';
    return simplephp_json_write($seoFile, $settings);
}
