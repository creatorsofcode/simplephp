<?php
// Start session for module functionality
session_start();

// Initialize Module System
require_once __DIR__ . '/includes/ModuleManager.php';
$moduleManager = new ModuleManager();
$GLOBALS['moduleManager'] = $moduleManager; // Make globally accessible for modules
$moduleManager->loadActiveModules();

// Load active theme
$themeConfigFile = __DIR__ . '/data/theme-config.json';
$activeTheme = 'light'; // Default theme
$enabledThemes = ['light'];
if (file_exists($themeConfigFile)) {
  $themeConfig = json_decode(file_get_contents($themeConfigFile), true);
  if (is_array($themeConfig)) {
    $activeTheme = $themeConfig['active_theme'] ?? $activeTheme;
    if (isset($themeConfig['enabled_themes']) && is_array($themeConfig['enabled_themes'])) {
      $enabledThemes = array_values(array_filter($themeConfig['enabled_themes'], 'is_string'));
    }
  }
}

if (empty($enabledThemes)) {
  $enabledThemes = ['light'];
}

if (!in_array($activeTheme, $enabledThemes, true)) {
  $activeTheme = $enabledThemes[0];
}
$themeFile = __DIR__ . '/themes/' . $activeTheme . '/theme.json';
$themeData = [];
if (file_exists($themeFile)) {
    $themeData = json_decode(file_get_contents($themeFile), true);
}


$data = json_decode(file_get_contents(__DIR__.'/data/content.json'), true);
$page = $_GET['page'] ?? 'home';
$siteData = $data['site'];
$menu = $data['menu'] ?? [];
// Sort menu by order
usort($menu, function($a, $b) { return ($a['order'] ?? 999) <=> ($b['order'] ?? 999); });
$pageData = $data['pages'][$page] ?? ($data['pages']['home'] ?? []);

// Fix image paths - replace ../images/ with images/
function fixImagePaths($content) {
    if(is_string($content)){
        return str_replace('../images/', 'images/', $content);
    }
    return $content;
}

// Fix image paths in page data
if(is_array($pageData)){
    foreach($pageData as $key => $value){
        if(is_string($value)){
            $pageData[$key] = fixImagePaths($value);
        } elseif(is_array($value)){
            foreach($value as $subKey => $subValue){
                if(is_string($subValue)){
                    $pageData[$key][$subKey] = fixImagePaths($subValue);
                } elseif(is_array($subValue)){
                    foreach($subValue as $subSubKey => $subSubValue){
                        if(is_string($subSubValue)){
                            $pageData[$key][$subKey][$subSubKey] = fixImagePaths($subSubValue);
                        }
                    }
                }
            }
        }
    }
}

$design = $data['design'] ?? [];
$templateHtml = trim((string)($design['template_html'] ?? ''));
$customCss = (string)($design['custom_css'] ?? '');
$customJs = (string)($design['custom_js'] ?? '');

function renderNavLinks(array $menu): string {
    ob_start();
    foreach($menu as $item){
        if(($item['type'] ?? 'page') === 'page'){
            ?>
            <a href="?page=<?= htmlspecialchars($item['id'] ?? '') ?>"><?= htmlspecialchars($item['label'] ?? '') ?></a>
            <?php
        } elseif(($item['type'] ?? '') === 'link'){
            $url = $item['url'] ?? '#';
            $isExternal = is_string($url) && strpos($url, 'http') === 0;
            ?>
            <a href="<?= htmlspecialchars($url) ?>" <?= $isExternal ? 'target="_blank" rel="noopener"' : '' ?>><?= htmlspecialchars($item['label'] ?? '') ?></a>
            <?php
        }
    }
    return trim(ob_get_clean());
}

function renderMainHtml(string $page, array $pageData, array $siteData, array $menu): string {
    ob_start();
    ?>
<?php if($page === 'home'): ?>
    <div class="content-item">
      <h3 data-editable="pages.home.hero_title"><?= htmlspecialchars($pageData['hero_title'] ?? 'Welcome!') ?></h3>
      <div data-editable="pages.home.hero_text"><?= $pageData['hero_text'] ?? '' ?></div>
<?php 
// Find contact page link
$contactPage = null;
foreach($menu as $item){
    if(($item['type'] ?? 'page') === 'page' && (strpos(strtolower($item['label'] ?? ''), 'contact') !== false)){
        $contactPage = $item['id'];
        break;
    }
}
if(!$contactPage){
    foreach($menu as $item){
        if(($item['id'] ?? '') === 'contact'){
            $contactPage = 'contact';
            break;
        }
    }
}
?>
      <a href="?page=<?= htmlspecialchars($contactPage ?? 'contact') ?>" class="btn primary" data-editable="pages.home.cta_button"><?= htmlspecialchars($pageData['cta_button'] ?? 'Contact') ?></a>
    </div>

<?php if(isset($pageData['features_title']) && !empty($pageData['features'])): ?>
    <h3><?= htmlspecialchars($pageData['features_title']) ?></h3>
    <div class="grid-3" data-editable-container="features">
<?php foreach($pageData['features'] as $index => $feature): ?>
<div class="card">
<h4 data-editable="pages.home.features.<?= $index ?>.title"><?= htmlspecialchars($feature['title'] ?? '') ?></h4>
<div data-editable="pages.home.features.<?= $index ?>.text"><?= $feature['text'] ?? '' ?></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php else: ?>
    <?php 
    // Dynamic page rendering for all other pages
    $isContactPage = false;
    if(isset($pageData['form_title']) || isset($pageData['phone']) || isset($pageData['email'])){
        $isContactPage = true;
    }
    ?>
    <?php if($isContactPage): ?>
    <div class="content-item">
      <h3 data-editable="pages.<?= $page ?>.title"><?= htmlspecialchars($pageData['title'] ?? 'Contact') ?></h3>
      <div data-editable="pages.<?= $page ?>.intro"><?= $pageData['intro'] ?? '' ?></div>
    </div>
    <div class="contact-wrap">
      <div class="contact-grid">
        <div>
          <?php if(isset($pageData['phone'])): ?>
          <div class="box">
            <h4>Phone</h4>
            <p><a href="tel:<?= htmlspecialchars($pageData['phone']) ?>" data-editable="pages.<?= $page ?>.phone"><?= htmlspecialchars($pageData['phone']) ?></a></p>
          </div>
          <?php endif; ?>
          <?php if(isset($pageData['email'])): ?>
          <div class="box">
            <h4>Email</h4>
            <p><a href="mailto:<?= htmlspecialchars($pageData['email']) ?>" data-editable="pages.<?= $page ?>.email"><?= htmlspecialchars($pageData['email']) ?></a></p>
          </div>
          <?php endif; ?>
          <?php if(isset($pageData['address'])): ?>
          <div class="box">
            <h4>Address</h4>
            <p data-editable="pages.<?= $page ?>.address"><?= htmlspecialchars($pageData['address']) ?></p>
          </div>
          <?php endif; ?>
        </div>
        <?php if(isset($pageData['form_title'])): ?>
        <div class="contact-form">
          <h4 data-editable="pages.<?= $page ?>.form_title"><?= htmlspecialchars($pageData['form_title']) ?></h4>
          <div id="form-message" style="display: none; margin-bottom: 15px;"></div>
          <form id="contact-form" method="post" novalidate>
            <div class="form-group">
              <input type="text" id="form-name" name="name" class="form-control" placeholder="<?= htmlspecialchars($pageData['form_name'] ?? 'Name') ?>">
              <span class="error-message" id="error-name"></span>
            </div>
            <div class="form-group">
              <input type="email" id="form-email" name="email" class="form-control" placeholder="<?= htmlspecialchars($pageData['form_email'] ?? 'Email') ?>">
              <span class="error-message" id="error-email"></span>
            </div>
            <div class="form-group">
              <textarea id="form-message-text" name="message" class="form-control" placeholder="<?= htmlspecialchars($pageData['form_message'] ?? 'Message') ?>"></textarea>
              <span class="error-message" id="error-message"></span>
            </div>
            <button type="submit" class="btn primary" id="submit-btn" data-original-text="<?= htmlspecialchars($pageData['form_submit'] ?? 'Send') ?>"><?= htmlspecialchars($pageData['form_submit'] ?? 'Send') ?></button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="content-item">
      <h3 data-editable="pages.<?= $page ?>.title"><?= htmlspecialchars($pageData['title'] ?? '') ?></h3>

      <?php if(isset($pageData['intro']) && !empty($pageData['intro'])): ?>
      <div data-editable="pages.<?= $page ?>.intro"><?= $pageData['intro'] ?? '' ?></div>
      <?php endif; ?>
    </div>

    <?php 
    // Check if page has array data (services, steps, features, etc.)
    $hasArrayData = false;
    foreach($pageData as $key => $value){
        if(is_array($value) && !empty($value)){
            $hasArrayData = true;
            break;
        }
    }
    ?>

    <?php if($hasArrayData): ?>
      <?php foreach($pageData as $key => $value): ?>
        <?php if(is_array($value) && !empty($value) && !in_array($key, ['features', 'services', 'steps', 'title', 'intro', 'content', 'phone', 'email', 'address', 'form_title', 'form_name', 'form_email', 'form_message', 'form_submit'])): ?>
        <h3><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?></h3>
        <div class="grid-3" data-editable-container="<?= $key ?>">
          <?php foreach($value as $index => $item): ?>
          <div class="card">
            <?php if(isset($item['title'])): ?>
            <h4 data-editable="pages.<?= $page ?>.<?= $key ?>.<?= $index ?>.title"><?= htmlspecialchars($item['title']) ?></h4>
            <?php endif; ?>
            <?php if(isset($item['text'])): ?>
            <div data-editable="pages.<?= $page ?>.<?= $key ?>.<?= $index ?>.text"><?= $item['text'] ?? '' ?></div>
            <?php elseif(isset($item['content'])): ?>
            <div data-editable="pages.<?= $page ?>.<?= $key ?>.<?= $index ?>.content"><?= $item['content'] ?? '' ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php elseif(is_array($value) && !empty($value) && in_array($key, ['features', 'services', 'steps'])): ?>
        <h3><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?></h3>
        <div class="grid-3" data-editable-container="<?= $key ?>">
          <?php foreach($value as $index => $item): ?>
          <div class="card">
            <?php if(isset($item['title'])): ?>
            <h4 data-editable="pages.<?= $page ?>.<?= $key ?>.<?= $index ?>.title"><?= htmlspecialchars($item['title']) ?></h4>
            <?php endif; ?>
            <?php if(isset($item['text'])): ?>
            <div data-editable="pages.<?= $page ?>.<?= $key ?>.<?= $index ?>.text"><?= $item['text'] ?? '' ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if(isset($pageData['content']) && !empty($pageData['content'])): ?>
    <div class="content-item">
      <div data-editable="pages.<?= $page ?>.content"><?= $pageData['content'] ?? '' ?></div>
    </div>
    <?php endif; ?>

    <?php if(empty($pageData) || (!$hasArrayData && !isset($pageData['content']))): ?>
    <div class="content-item" style="text-align: center; color: var(--color-text-light);">
      <p>No content. Add content from admin panel.</p>
    </div>
    <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
    <?php
    return ob_get_clean();
}

$navHtml = renderNavLinks($menu);
$mainHtml = renderMainHtml($page, $pageData, $siteData, $menu);
$defaultContactFormScriptTag = '<script src="contact-form.js" defer></script>';

if($templateHtml !== ''){
    $out = $templateHtml;

    // 1) Replace placeholders when they exist (recommended)
    $out = str_replace('{{SITE_TITLE}}', htmlspecialchars($siteData['title'] ?? ''), $out);
    $out = str_replace('{{PAGE_TITLE}}', htmlspecialchars($pageData['title'] ?? ($siteData['title'] ?? '')), $out);
    $out = str_replace('{{DESCRIPTION}}', htmlspecialchars($siteData['description'] ?? ''), $out);
    $out = str_replace('{{NAV}}', $navHtml, $out);
    $out = str_replace('{{CONTENT}}', $mainHtml, $out);
    $out = str_replace('{{CUSTOM_CSS}}', $customCss, $out);
    $out = str_replace('{{CUSTOM_JS}}', $customJs, $out);
    $out = str_replace('{{DEFAULT_CONTACT_FORM_SCRIPT}}', $defaultContactFormScriptTag, $out);

    // 2) If user pasted full HTML without placeholders, inject menu/content into <nav>/<main> if possible
    if(strpos($out, '{{NAV}}') === false && $navHtml !== ''){
        // Insert after first <nav ...> tag
        $out = preg_replace('/<nav\b([^>]*)>/i', '<nav$1>' . $navHtml, $out, 1) ?? $out;
    }

    if(strpos($out, '{{CONTENT}}') === false && $mainHtml !== ''){
        if(preg_match('/<main\b[^>]*>/i', $out)){
            $out = preg_replace('/<main\b([^>]*)>/i', '<main$1>' . $mainHtml, $out, 1) ?? $out;
        } elseif(preg_match('/<\/body>/i', $out)) {
            $out = preg_replace('/<\/body>/i', $mainHtml . "\n</body>", $out, 1) ?? $out;
        } else {
            $out .= "\n" . $mainHtml;
        }
    }

    // 3) Inject CSS/JS if placeholders are not used (so pasting normal HTML works)
    if(strpos($out, '{{CUSTOM_CSS}}') === false && trim($customCss) !== ''){
        $styleTag = "<style>\n/* Custom CSS from Admin Panel */\n" . $customCss . "\n</style>\n";
        if(preg_match('/<\/head>/i', $out)){
            $out = preg_replace('/<\/head>/i', $styleTag . "</head>", $out, 1) ?? $out;
        } else {
            $out = $styleTag . $out;
        }
    }

    // Always keep contact form JS available (it only runs if #contact-form exists)
    if(strpos($out, 'contact-form.js') === false){
        if(preg_match('/<\/body>/i', $out)){
            $out = preg_replace('/<\/body>/i', $defaultContactFormScriptTag . "\n</body>", $out, 1) ?? $out;
        } else {
            $out .= "\n" . $defaultContactFormScriptTag;
        }
    }

    if(strpos($out, '{{CUSTOM_JS}}') === false && trim($customJs) !== ''){
        $jsTag = "<script>\n" . $customJs . "\n</script>\n";
        if(preg_match('/<\/body>/i', $out)){
            $out = preg_replace('/<\/body>/i', $jsTag . "</body>", $out, 1) ?? $out;
        } else {
            $out .= "\n" . $jsTag;
        }
    }

    echo $out;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Load Active Theme CSS -->
<?php if (file_exists(__DIR__ . '/themes/' . $activeTheme . '/theme.css')): ?>
<link rel="stylesheet" href="themes/<?= htmlspecialchars($activeTheme) ?>/theme.css">
<?php endif; ?>
<title><?= htmlspecialchars($pageData['title'] ?? $siteData['title']) ?> – <?= htmlspecialchars($siteData['title']) ?></title>
<meta name="description" content="<?= htmlspecialchars($siteData['description']) ?>">
<?php
// Module Hook: page_meta - Add meta tags
$metaHooks = $moduleManager->executeHook('page_meta', $pageData);
foreach ($metaHooks as $moduleId => $metaTags) {
    if (is_array($metaTags)) {
        foreach ($metaTags as $name => $content) {
            echo '<meta property="' . htmlspecialchars($name) . '" content="' . htmlspecialchars($content) . '">' . "\n";
        }
    }
}
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
      --color-primary: #4680ff;
      --color-primary-dark: #3565dd;
      --color-secondary: #6c757d;
      --color-success: #2ed8b6;
      --color-danger: #ff5370;
      --color-warning: #ffa502;
      --color-text: #2c3e50;
      --color-text-light: #6c757d;
      --color-bg: #f8f9fa;
      --color-white: #ffffff;
      --color-border: #e3e6f0;
      --shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      font-family: 'Inter', 'Segoe UI', sans-serif;
      background-color: var(--color-bg);
      color: var(--color-text);
      line-height: 1.6;
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Top Navigation Bar */
    .top-bar {
      background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
      color: white;
      padding: 12px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: var(--shadow);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .top-bar-left {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .top-bar-logo {
      font-weight: 700;
      font-size: 16px;
    }

    .top-bar-menus {
      display: flex;
      gap: 20px;
      list-style: none;
    }

    .top-bar-menus a {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: color 0.2s;
      padding: 5px 0;
    }

    .top-bar-menus a:hover {
      color: white;
    }

    .top-bar-right {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .user-greeting {
      font-size: 13px;
      color: rgba(255, 255, 255, 0.9);
    }

    .logout-link {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      font-size: 13px;
      transition: color 0.2s;
    }

    .logout-link:hover {
      color: white;
    }

    /* Main Header */
    .main-header {
      background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
      color: white;
      padding: 40px 30px;
      margin-bottom: 30px;
    }

    .main-header h1 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .main-header-subtitle {
      font-size: 14px;
      opacity: 0.9;
    }

    /* Container Layout */
    .container-wrapper {
      display: flex;
      flex: 1;
      padding: 0 30px 30px;
      gap: 30px;
      max-width: 1400px;
      margin: 0 auto;
      width: 100%;
    }

    /* Sidebar */
    .sidebar {
      width: 280px;
      flex-shrink: 0;
    }

    .sidebar-section {
      background: white;
      border-radius: 8px;
      margin-bottom: 20px;
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    .sidebar-title {
      padding: 16px;
      background: #f8f9fa;
      font-weight: 600;
      font-size: 15px;
      color: var(--color-text);
      border-bottom: 1px solid var(--color-border);
    }

    .sidebar-content {
      padding: 12px 0;
    }

    .search-box {
      padding: 12px 12px;
      border: 1px solid var(--color-border);
      border-radius: 6px;
      width: 100%;
      font-size: 14px;
      font-family: inherit;
      transition: all 0.2s;
    }

    .search-box:focus {
      outline: none;
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px rgba(70, 128, 255, 0.1);
    }

    .sidebar-nav {
      list-style: none;
    }

    .sidebar-nav li {
      border-bottom: 1px solid var(--color-border);
    }

    .sidebar-nav li:last-child {
      border-bottom: none;
    }

    .sidebar-nav a {
      display: block;
      padding: 12px 16px;
      color: var(--color-text);
      text-decoration: none;
      font-size: 14px;
      transition: all 0.2s;
    }

    .sidebar-nav a:hover {
      background: #f0f4ff;
      color: var(--color-primary);
      padding-left: 20px;
    }

    /* Main Content */
    .main-content {
      flex: 1;
      min-width: 0;
    }

    .content-item {
      background: white;
      border-radius: 8px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: var(--shadow);
      border: 1px solid var(--color-border);
      transition: all 0.2s;
    }

    .content-item:hover {
      box-shadow: var(--shadow-lg);
    }

    .content-item h3 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 8px;
      color: var(--color-text);
    }

    .content-meta {
      font-size: 12px;
      color: var(--color-text-light);
      margin-bottom: 12px;
    }

    .content-excerpt {
      color: var(--color-text-light);
      font-size: 14px;
      line-height: 1.6;
      margin-bottom: 12px;
    }

    .read-more {
      display: inline-block;
      color: var(--color-primary);
      text-decoration: none;
      font-weight: 500;
      font-size: 14px;
      transition: color 0.2s;
    }

    .read-more:hover {
      color: var(--color-primary-dark);
      text-decoration: underline;
    }

    main {
      flex: 1;
    }

    main h2 {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
      color: var(--color-text);
    }

    main h3,
    main h4 {
      color: var(--color-text);
      margin-bottom: 12px;
    }

    main p {
      color: var(--color-text-light);
      margin-bottom: 12px;
      line-height: 1.7;
    }

    .card {
      background: white;
      border-radius: 8px;
      padding: 20px;
      border: 1px solid var(--color-border);
      box-shadow: var(--shadow);
      margin-bottom: 20px;
    }

    .card h4 {
      margin-bottom: 8px;
    }

    .grid-3 {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }

    .btn {
      display: inline-block;
      padding: 10px 20px;
      background: var(--color-primary);
      color: white;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn:hover {
      background: var(--color-primary-dark);
      transform: translateY(-1px);
    }

    .btn.primary {
      background: var(--color-primary);
    }

    /* Contact Form */
    .contact-wrap {
      background: white;
      padding: 24px;
      border-radius: 8px;
      border: 1px solid var(--color-border);
      box-shadow: var(--shadow);
    }

    .contact-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .box {
      background: #f8f9fa;
      padding: 16px;
      border-radius: 8px;
      border: 1px solid var(--color-border);
    }

    .box h4 {
      margin-bottom: 10px;
      font-size: 14px;
      font-weight: 600;
    }

    .box p {
      font-size: 14px;
      color: var(--color-text-light);
      margin: 0;
    }

    .box a {
      color: var(--color-primary);
      text-decoration: none;
    }

    .box a:hover {
      text-decoration: underline;
    }

    .contact-form {
      background: white;
      padding: 20px;
      border-radius: 8px;
      border: 1px solid var(--color-border);
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      font-weight: 500;
      font-size: 14px;
      margin-bottom: 6px;
      color: var(--color-text);
    }

    .form-control {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid var(--color-border);
      border-radius: 6px;
      font-size: 14px;
      font-family: inherit;
      transition: all 0.2s;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px rgba(70, 128, 255, 0.1);
    }

    textarea.form-control {
      min-height: 120px;
      resize: vertical;
    }

    .error-message {
      display: none;
      color: var(--color-danger);
      font-size: 12px;
      margin-top: 4px;
    }

    .form-field input.error,
    .form-field textarea.error {
      border-color: var(--color-danger) !important;
    }

    #form-message {
      padding: 12px;
      border-radius: 6px;
      margin-bottom: 15px;
      display: none;
    }

    #form-message.success {
      background: rgba(46, 216, 182, 0.1);
      border: 1px solid var(--color-success);
      color: #0f5d44;
    }

    #form-message.error {
      background: rgba(255, 83, 112, 0.1);
      border: 1px solid var(--color-danger);
      color: #7d1f2c;
    }

    /* Footer */
    footer {
      background: #f8f9fa;
      border-top: 1px solid var(--color-border);
      padding: 24px 30px;
      margin-top: 40px;
      text-align: center;
      color: var(--color-text-light);
      font-size: 13px;
    }

    .foot {
      max-width: 1400px;
      margin: 0 auto;
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 20px;
    }

    .foot a {
      color: var(--color-primary);
      text-decoration: none;
    }

    .foot a:hover {
      text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 992px) {
      .container-wrapper {
        flex-direction: column;
        padding: 0 20px 20px;
      }

      .sidebar {
        width: 100%;
      }

      .top-bar {
        padding: 10px 20px;
      }

      .main-header {
        padding: 30px 20px;
        margin-bottom: 20px;
      }

      .main-header h1 {
        font-size: 24px;
      }
    }

    @media (max-width: 768px) {
      .top-bar {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
      }

      .top-bar-right {
        width: 100%;
        justify-content: space-between;
      }

      .top-bar-menus {
        flex-wrap: wrap;
        gap: 10px;
      }

      .main-header {
        padding: 20px;
      }

      .main-header h1 {
        font-size: 20px;
      }

      .container-wrapper {
        padding: 0 15px 15px;
        gap: 15px;
      }

      .sidebar {
        width: 100%;
      }

      .content-item {
        padding: 16px;
      }

      .grid-3 {
        grid-template-columns: 1fr;
      }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
</style>
<?php if(isset($data['design']['custom_css']) && !empty($data['design']['custom_css'])): ?>
<style>
/* Custom CSS from Admin Panel */
<?= $data['design']['custom_css'] ?>
</style>
<?php endif; ?>
<?php
// Module Hook: page_head - Add custom head content (CSS, scripts, etc.)
$headHooks = $moduleManager->executeHook('page_head', $pageData);
foreach ($headHooks as $moduleId => $headContent) {
    if (is_string($headContent)) {
        echo $headContent . "\n";
    }
}
?>
<?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
<link rel="stylesheet" href="inline-editor.css">
<link rel="stylesheet" href="frontend-admin.css">
<script>
  window.isAdminLoggedIn = true;
</script>
<?php endif; ?>
</head>
<body class="theme-<?= htmlspecialchars($activeTheme) ?>">
<?php
// Module Hook: page_body_start - Add content at start of body (banners, etc.)
$bodyStartHooks = $moduleManager->executeHook('page_body_start', $pageData);
foreach ($bodyStartHooks as $moduleId => $bodyContent) {
    if (is_string($bodyContent)) {
        echo $bodyContent . "\n";
    }
}
?>

  <!-- Top Navigation Bar -->
  <div class="top-bar">
    <div class="top-bar-left">
      <div class="top-bar-logo">Simplephp.org</div>
      <ul class="top-bar-menus" data-editable-container="menu">
        <?php foreach($menu as $item): ?>
          <?php if($item['type'] === 'page'): ?>
            <li><a href="?page=<?= htmlspecialchars($item['id']) ?>"><?= htmlspecialchars($item['label']) ?></a></li>
          <?php elseif($item['type'] === 'link'): ?>
            <li><a href="<?= htmlspecialchars($item['url'] ?? '#') ?>" <?= isset($item['url']) && strpos($item['url'], 'http') === 0 ? 'target="_blank" rel="noopener"' : '' ?>><?= htmlspecialchars($item['label']) ?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="top-bar-right">
      <div class="user-greeting">Hello admin</div>
      <a href="admin/login.php" class="logout-link">Log out</a>
    </div>
  </div>

  <!-- Main Header -->
  <div class="main-header">
    <h1 data-editable="site.title"><?= htmlspecialchars($siteData['title']) ?></h1>
    <div class="main-header-subtitle" data-editable="site.description"><?= htmlspecialchars($siteData['description']) ?></div>
  </div>

  <!-- Main Container with Sidebar -->
  <div class="container-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
      <!-- Search Box -->
      <?php $search_enabled = ($data['design']['search_enabled'] ?? true); if($search_enabled): ?>
      <div class="sidebar-section">
        <div class="sidebar-title">Search</div>
        <div class="sidebar-content" style="padding: 12px;">
          <input type="search" class="search-box" id="search-box" placeholder="Search content...">
        </div>
      </div>
      <?php endif; ?>

      <!-- Navigation -->
      <div class="sidebar-section">
        <div class="sidebar-title">Navigation</div>
        <ul class="sidebar-nav">
          <?php foreach($menu as $item): ?>
            <?php if($item['type'] === 'page'): ?>
              <li><a href="?page=<?= htmlspecialchars($item['id']) ?>"><?= htmlspecialchars($item['label']) ?></a></li>
            <?php elseif($item['type'] === 'link'): ?>
              <li><a href="<?= htmlspecialchars($item['url'] ?? '#') ?>" <?= isset($item['url']) && strpos($item['url'], 'http') === 0 ? 'target="_blank" rel="noopener"' : '' ?>><?= htmlspecialchars($item['label']) ?></a></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Help -->
      <div class="sidebar-section">
        <div class="sidebar-title">Admin</div>
        <div class="sidebar-content" style="padding: 12px; font-size: 13px; color: var(--color-text-light);">
          <p style="margin-bottom: 8px;">Logged in as <strong>admin</strong></p>
          <a href="admin/login.php" style="color: var(--color-primary); text-decoration: none; font-weight: 500;">Go to Admin Panel</a>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
<?= $mainHtml ?>
    </main>
  </div>

<?php
// Module Hook: page_content - Module content displayed after main content
$contentHooks = $moduleManager->executeHook('page_content', $pageData);
foreach ($contentHooks as $moduleId => $content) {
    if (is_array($content)) {
        // If module returns array, render each item
        foreach ($content as $key => $value) {
            if (is_string($value)) {
                echo $value . "\n";
            }
        }
    } elseif (is_string($content)) {
        echo $content . "\n";
    }
}
?>

  <footer>
    <div class="foot">
      <p>&copy; <?= date('Y') ?> <span data-editable="site.title"><?= htmlspecialchars($siteData['title']) ?></span></p>
      <p>
        <span data-editable="site.phone"><?= htmlspecialchars($siteData['phone']) ?></span> | <span data-editable="site.email"><?= htmlspecialchars($siteData['email']) ?></span>
        | <a href="admin/login.php">Admin Login</a>
        | <a href="https://github.com/creatorsofcode/simplephp" target="_blank" rel="noopener">GitHub</a>
      </p>
      <p style="font-size:12px;color:#777;" data-editable="site.demo_note">
        Note: This is a demo. Any changed data will be automatically reset back to the default state after 10 minutes.
      </p>
	  <p data-editable="site.login_info" style="font-size: 12px;">User: admin Password: admin123</p>
    </div>
  </footer>
  <script src="contact-form.js" defer></script>
  <script>
    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
      const searchBox = document.getElementById('search-box');
      if (searchBox) {
        searchBox.addEventListener('input', function() {
          const query = this.value.toLowerCase();
          const contentItems = document.querySelectorAll('.content-item, .card');
          
          contentItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(query)) {
              item.style.display = '';
              item.style.animation = 'fadeIn 0.3s ease';
            } else {
              item.style.display = 'none';
            }
          });
        });
      }
    });
  </script>
  <?php if(isset($data['design']['custom_js']) && !empty($data['design']['custom_js'])): ?>
  <script>
<?= $data['design']['custom_js'] ?>
  </script>
  <?php endif; ?>
<?php
// Module Hook: page_body_end - Add content at end of body (analytics, tracking, etc.)
$bodyEndHooks = $moduleManager->executeHook('page_body_end', $pageData);
foreach ($bodyEndHooks as $moduleId => $bodyEndContent) {
    if (is_string($bodyEndContent)) {
        echo $bodyEndContent . "\n";
    }
}
?>
<?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
<script src="inline-editor.js"></script>
<script src="frontend-admin.js"></script>
<?php endif; ?>

</body>
</html>
