<?php
/**
 * Analytics Tracker Module
 * Track visitor analytics with various platforms
 */

// Module initialization
function module_init_analytics_tracker() {
    // Initialize visit counter
    $counterFile = __DIR__ . '/visit-counter.json';
    if (!file_exists($counterFile)) {
        $counterData = [
            'total_visits' => 0,
            'unique_visits' => 0,
            'last_reset' => date('Y-m-d H:i:s')
        ];
        file_put_contents($counterFile, json_encode($counterData, JSON_PRETTY_PRINT));
    }
}

// Hook: Add tracking codes to head
function module_hook_analytics_tracker_page_head($data) {
    global $moduleManager;
    
    if (!$moduleManager) {
        return '';
    }
    
    $config = $moduleManager->getModuleConfig('analytics-tracker');
    $codes = [];
    
    // Google Analytics
    if (!empty($config['google_analytics_id'])) {
        $gaId = htmlspecialchars($config['google_analytics_id']);
        $codes[] = "
<!-- Google Analytics -->
<script async src='https://www.googletagmanager.com/gtag/js?id={$gaId}'></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$gaId}');
</script>
";
    }
    
    // Facebook Pixel
    if (!empty($config['facebook_pixel_id'])) {
        $fbId = htmlspecialchars($config['facebook_pixel_id']);
        $codes[] = "
<!-- Facebook Pixel -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$fbId}');
fbq('track', 'PageView');
</script>
<noscript><img height='1' width='1' style='display:none'
src='https://www.facebook.com/tr?id={$fbId}&ev=PageView&noscript=1'
/></noscript>
";
    }
    
    // Custom head code
    if (!empty($config['custom_head_code'])) {
        $codes[] = $config['custom_head_code'];
    }
    
    return implode("\n", $codes);
}

// Hook: Add tracking codes to body end
function module_hook_analytics_tracker_page_body_end($data) {
    global $moduleManager;
    
    if (!$moduleManager) {
        return '';
    }
    
    $config = $moduleManager->getModuleConfig('analytics-tracker');
    
    // Increment visit counter
    if ($config['enable_visit_counter'] ?? true) {
        analytics_tracker_increment_visits();
    }
    
    $codes = [];
    
    // Add custom body code
    if (!empty($config['custom_body_code'])) {
        $codes[] = $config['custom_body_code'];
    }
    
    // Add outbound link tracking if enabled
    if ($config['track_outbound_links'] ?? false) {
        $codes[] = "
<script>
// Track outbound link clicks
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a').forEach(function(link) {
        if (link.hostname !== window.location.hostname && window.gtag) {
            link.addEventListener('click', function() {
                gtag('event', 'click', {
                    'event_category': 'outbound',
                    'event_label': link.href
                });
            });
        }
    });
});
</script>
";
    }
    
    return implode("\n", $codes);
}

// Increment visit counter
function analytics_tracker_increment_visits() {
    $counterFile = __DIR__ . '/visit-counter.json';
    
    if (!file_exists($counterFile)) {
        return;
    }
    
    $counter = json_decode(file_get_contents($counterFile), true);
    
    $counter['total_visits']++;
    
    // Track unique visits based on session
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['analytics_tracker_visited'])) {
        $counter['unique_visits']++;
        $_SESSION['analytics_tracker_visited'] = true;
    }
    
    file_put_contents($counterFile, json_encode($counter, JSON_PRETTY_PRINT));
}

// Get analytics data
function analytics_tracker_get_stats() {
    $counterFile = __DIR__ . '/visit-counter.json';
    if (!file_exists($counterFile)) {
        return [
            'total_visits' => 0,
            'unique_visits' => 0,
            'last_reset' => date('Y-m-d H:i:s')
        ];
    }
    return json_decode(file_get_contents($counterFile), true);
}

// Get settings
function analytics_tracker_get_settings() {
    global $moduleManager;
    if (!$moduleManager) {
        return [];
    }
    return $moduleManager->getModuleConfig('analytics-tracker');
}

// Save settings
function analytics_tracker_save_settings($settings) {
    global $moduleManager;
    if (!$moduleManager) {
        return false;
    }
    return $moduleManager->saveModuleConfig('analytics-tracker', $settings);
}
