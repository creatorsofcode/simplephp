<?php
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
  <main>
<?php if($page === 'home'): ?>
    <h2><?= htmlspecialchars($pageData['hero_title'] ?? 'Welcome!') ?></h2>
<div><?= $pageData['hero_text'] ?? '' ?></div>
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
    <a href="?page=<?= htmlspecialchars($contactPage ?? 'contact') ?>" class="btn primary"><?= htmlspecialchars($pageData['cta_button'] ?? 'Contact') ?></a>

<?php if(isset($pageData['features_title']) && !empty($pageData['features'])): ?>
    <section style="margin-top: 40px; text-align: left;">
<h3><?= htmlspecialchars($pageData['features_title']) ?></h3>
<div class="grid-3">
<?php foreach($pageData['features'] as $feature): ?>
<div class="card">
<h4><?= htmlspecialchars($feature['title'] ?? '') ?></h4>
<div><?= $feature['text'] ?? '' ?></div>
</div>
<?php endforeach; ?>
</div>
</section>
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
    <h2><?= htmlspecialchars($pageData['title'] ?? 'Contact') ?></h2>
    <div><?= $pageData['intro'] ?? '' ?></div>
    <div class="contact-wrap">
      <div class="contact-grid">
        <div>
          <?php if(isset($pageData['phone'])): ?>
          <div class="box">
            <h4>Phone</h4>
            <p><a href="tel:<?= htmlspecialchars($pageData['phone']) ?>"><?= htmlspecialchars($pageData['phone']) ?></a></p>
          </div>
          <?php endif; ?>
          <?php if(isset($pageData['email'])): ?>
          <div class="box">
            <h4>Email</h4>
            <p><a href="mailto:<?= htmlspecialchars($pageData['email']) ?>"><?= htmlspecialchars($pageData['email']) ?></a></p>
          </div>
          <?php endif; ?>
          <?php if(isset($pageData['address'])): ?>
          <div class="box">
            <h4>Address</h4>
            <p><?= htmlspecialchars($pageData['address']) ?></p>
          </div>
          <?php endif; ?>
        </div>
        <?php if(isset($pageData['form_title'])): ?>
        <div class="contact-form">
          <h4><?= htmlspecialchars($pageData['form_title']) ?></h4>
          <div id="form-message" style="display: none; padding: 12px; border-radius: 8px; margin-bottom: 15px;"></div>
          <form id="contact-form" method="post" novalidate>
            <div class="form-field">
              <input type="text" id="form-name" name="name" placeholder="<?= htmlspecialchars($pageData['form_name'] ?? 'Name') ?>">
              <span class="error-message" id="error-name"></span>
            </div>
            <div class="form-field">
              <input type="email" id="form-email" name="email" placeholder="<?= htmlspecialchars($pageData['form_email'] ?? 'Email') ?>">
              <span class="error-message" id="error-email"></span>
            </div>
            <div class="form-field">
              <textarea id="form-message-text" name="message" placeholder="<?= htmlspecialchars($pageData['form_message'] ?? 'Message') ?>"></textarea>
              <span class="error-message" id="error-message"></span>
            </div>
            <button type="submit" id="submit-btn" data-original-text="<?= htmlspecialchars($pageData['form_submit'] ?? 'Send') ?>"><?= htmlspecialchars($pageData['form_submit'] ?? 'Send') ?></button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <h2><?= htmlspecialchars($pageData['title'] ?? '') ?></h2>

    <?php if(isset($pageData['intro']) && !empty($pageData['intro'])): ?>
    <div class="card" style="text-align: left; margin-bottom: 20px;">
      <h4 style="color: #2563eb; margin-bottom: 10px;">Intro</h4>
      <div><?= $pageData['intro'] ?? '' ?></div>
    </div>
    <?php endif; ?>

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
    <section style="text-align: left; margin-top: 30px;">
      <?php foreach($pageData as $key => $value): ?>
        <?php if(is_array($value) && !empty($value) && !in_array($key, ['features', 'services', 'steps', 'title', 'intro', 'content', 'phone', 'email', 'address', 'form_title', 'form_name', 'form_email', 'form_message', 'form_submit'])): ?>
        <div class="grid-3" style="margin-top: 20px;">
          <?php foreach($value as $item): ?>
          <div class="card">
            <?php if(isset($item['title'])): ?>
            <h4><?= htmlspecialchars($item['title']) ?></h4>
            <?php endif; ?>
            <?php if(isset($item['text'])): ?>
            <div><?= $item['text'] ?? '' ?></div>
            <?php elseif(isset($item['content'])): ?>
            <div><?= $item['content'] ?? '' ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php elseif(is_array($value) && !empty($value) && in_array($key, ['features', 'services', 'steps'])): ?>
        <div class="grid-3" style="margin-top: 20px;">
          <?php foreach($value as $item): ?>
          <div class="card">
            <?php if(isset($item['title'])): ?>
            <h4><?= htmlspecialchars($item['title']) ?></h4>
            <?php endif; ?>
            <?php if(isset($item['text'])): ?>
            <div><?= $item['text'] ?? '' ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if(isset($pageData['content']) && !empty($pageData['content'])): ?>
    <div class="card" style="text-align: left; margin-top: 20px;">
      <div><?= $pageData['content'] ?? '' ?></div>
    </div>
    <?php endif; ?>

    <?php if(empty($pageData) || (!$hasArrayData && !isset($pageData['content']))): ?>
    <div class="card" style="text-align: center; color: #666;">
      <p>No content. Add content from admin panel.</p>
    </div>
    <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
  </main>
    <?php
    return ob_get_clean();
}

$navHtml = renderNavLinks($menu);
$mainHtml = renderMainHtml($page, $pageData, $siteData, $menu);
$defaultContactFormScriptTag = '<script src="contact-form.js" defer></script>';

if($templateHtml !== ''){
    $replacements = [
        '{{SITE_TITLE}}' => htmlspecialchars($siteData['title'] ?? ''),
        '{{PAGE_TITLE}}' => htmlspecialchars($pageData['title'] ?? ($siteData['title'] ?? '')),
        '{{DESCRIPTION}}' => htmlspecialchars($siteData['description'] ?? ''),
        '{{NAV}}' => $navHtml,
        '{{CONTENT}}' => $mainHtml,
        '{{CUSTOM_CSS}}' => $customCss,
        '{{CUSTOM_JS}}' => $customJs,
        '{{DEFAULT_CONTACT_FORM_SCRIPT}}' => $defaultContactFormScriptTag,
    ];
    echo str_replace(array_keys($replacements), array_values($replacements), $templateHtml);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageData['title'] ?? $siteData['title']) ?> – <?= htmlspecialchars($siteData['title']) ?></title>
<meta name="description" content="<?= htmlspecialchars($siteData['description']) ?>">
<style>
    /* --- Base Styles --- */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: linear-gradient(120deg, #f6f8fa, #dbeafe);
      color: #333;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      padding: 20px;
    }

    header {
      width: 100%;
      max-width: 1200px;
      padding: 20px;
      background-color: #2563eb;
      color: #fff;
      text-align: center;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      margin-bottom: 40px;
    }

    header h1 {
      margin-bottom: 10px;
    }

    nav {
      margin-top: 10px;
    }

    nav a {
      color: #fff;
      text-decoration: none;
      margin: 0 15px;
      font-weight: bold;
      transition: color 0.3s;
    }

    nav a:hover {
      color: #93c5fd;
    }

    main {
      max-width: 1200px;
      width: 100%;
      background: #fff;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      text-align: center;
      margin-bottom: 40px;
    }

    main h2, main h3 {
      margin-bottom: 20px;
      color: #2563eb;
    }

    main p {
      margin-bottom: 15px;
      line-height: 1.6;
    }

    button, .btn {
      padding: 12px 25px;
      margin-top: 20px;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      background-color: #2563eb;
      color: #fff;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.2s;
      text-decoration: none;
      display: inline-block;
    }

    button:hover, .btn:hover {
      background-color: #1e40af;
      transform: scale(1.05);
    }

    .btn.primary {
      background-color: #2563eb;
    }

    .container {
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
      padding: 0 20px;
    }

    section {
      margin-bottom: 40px;
    }

    .hero-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
      margin-top: 20px;
    }

    .hero-card {
      background: #f8f9fa;
      padding: 30px;
      border-radius: 10px;
      text-align: left;
    }

    .hero-card h1 {
      color: #2563eb;
      margin-bottom: 15px;
    }

    .side-card {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
      text-align: left;
    }

    .side-card h2 {
      color: #2563eb;
      margin-bottom: 15px;
      font-size: 18px;
    }

    .kv {
      display: grid;
      gap: 10px;
    }

    .kv .item {
      padding: 10px;
      background: #fff;
      border-radius: 8px;
      border: 1px solid #e5e7eb;
    }

    .kv .label {
      font-size: 12px;
      color: #666;
      margin-bottom: 5px;
    }

    .kv .value {
      font-weight: 700;
      color: #333;
    }

    .kv a {
      color: #2563eb;
      text-decoration: none;
    }

    .kv a:hover {
      text-decoration: underline;
    }

    .grid-3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-top: 20px;
    }

    .card {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
      text-align: left;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .card h4 {
      color: #2563eb;
      margin-bottom: 10px;
    }

    .card p {
      color: #666;
    }

.card img,
section img,
    div img {
      max-width: 100%;
      height: auto;
      border-radius: 10px;
      margin: 15px 0;
      display: block;
    }

    .contact-wrap {
      background: #f8f9fa;
      padding: 30px;
      border-radius: 10px;
      margin-top: 20px;
    }

    .contact-grid {
      display: grid;
  grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-top: 20px;
    }

    .contact .box {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      border: 1px solid #e5e7eb;
    }

    .contact .box h4 {
      color: #2563eb;
      margin-bottom: 10px;
    }

    .contact-form {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      border: 1px solid #e5e7eb;
  margin-top: 20px;
}

.contact-form input,
    .contact-form textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 14px;
      font-family: inherit;
    }

    .contact-form textarea {
      min-height: 120px;
      resize: vertical;
    }

    .contact-form button {
      width: 100%;
    }

    .form-field {
      margin-bottom: 15px;
    }

    .error-message {
      display: none;
      color: #f44336;
      font-size: 12px;
      margin-top: 5px;
      min-height: 18px;
      font-weight: 500;
      animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-5px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .form-field input.error,
    .form-field textarea.error {
      border-color: #f44336 !important;
      border-width: 2px !important;
      animation: shake 0.3s ease;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    .form-field input.success,
    .form-field textarea.success {
      border-color: #4caf50 !important;
      border-width: 2px !important;
    }

    #form-message.success {
      background-color: rgba(76,175,80,0.1);
      border: 1px solid #4caf50;
      color: #2e7d32;
      display: block;
    }

    #form-message.error {
      background-color: rgba(244,67,54,0.1);
      border: 1px solid #f44336;
      color: #c62828;
      display: block;
    }

    #submit-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    footer {
      margin-top: 50px;
      color: #555;
      font-size: 14px;
      text-align: center;
      width: 100%;
      max-width: 1200px;
    }

    .foot {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
      align-items: center;
    }

    @media (max-width: 980px) {
      .hero-grid {
        grid-template-columns: 1fr;
      }
      .grid-3 {
        grid-template-columns: 1fr;
      }
      .contact-grid {
        grid-template-columns: 1fr;
      }
      nav a {
        display: block;
        margin: 5px 0;
      }
}
</style>
<?php if(isset($data['design']['custom_css']) && !empty($data['design']['custom_css'])): ?>
<style>
/* Custom CSS from Admin Panel */
<?= $data['design']['custom_css'] ?>
</style>
<?php endif; ?>
</head>
<body>

  <header>
    <h1><?= htmlspecialchars($siteData['title']) ?></h1>
    <nav>
      <?php foreach($menu as $item): ?>
        <?php if($item['type'] === 'page'): ?>
          <a href="?page=<?= htmlspecialchars($item['id']) ?>"><?= htmlspecialchars($item['label']) ?></a>
        <?php elseif($item['type'] === 'link'): ?>
          <a href="<?= htmlspecialchars($item['url'] ?? '#') ?>" <?= isset($item['url']) && strpos($item['url'], 'http') === 0 ? 'target="_blank" rel="noopener"' : '' ?>><?= htmlspecialchars($item['label']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
  </header>

<?= $mainHtml ?>

  <footer>
    <div class="foot">
      <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteData['title']) ?></p>
      <p>
        <?= htmlspecialchars($siteData['phone']) ?> | <?= htmlspecialchars($siteData['email']) ?>
        | <a href="admin/login.php" style="color:inherit">Login</a>
      </p>
    </div>
  </footer>
  <script src="contact-form.js" defer></script>
  <?php if(isset($data['design']['custom_js']) && !empty($data['design']['custom_js'])): ?>
  <script>
<?= $data['design']['custom_js'] ?>
  </script>
  <?php endif; ?>

</body>
</html>
