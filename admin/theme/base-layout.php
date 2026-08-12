<?php
/**
 * Base Layout Template - Professional Admin Dashboard
 * Star Admin 2 Inspired Design
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Admin' : 'Admin Dashboard'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Feather Icons -->
    <link href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css" rel="stylesheet">
    <!-- Custom Theme CSS -->
    <?php
    if (!isset($base_url)) {
        $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $base_url = preg_replace('~/admin$~', '', $script_dir);
        if ($base_url === '/') {
            $base_url = '';
        }
    }
    ?>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/admin/theme/assets/css/admin-theme.css">
    
    <?php if (isset($custom_css)) { echo $custom_css; } ?>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo-wrap">
                    <span class="logo-text">Admin</span>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?php echo $base_url; ?>/admin/index.php" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/index') !== false ? 'active' : ''; ?>">
                        <i data-feather="home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_url; ?>/admin/modules.php" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'modules') !== false ? 'active' : ''; ?>">
                        <i data-feather="grid"></i>
                        <span>Modules</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_url; ?>/admin/themes.php" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'themes') !== false ? 'active' : ''; ?>">
                        <i data-feather="layout"></i>
                        <span>Themes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_url; ?>/admin/config-module.php" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'config-module') !== false ? 'active' : ''; ?>">
                        <i data-feather="settings"></i>
                        <span>Module Config</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="<?php echo $base_url; ?>/admin/index.php?logout=1" class="logout-btn">
                    <i data-feather="log-out"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="toggle-sidebar" id="toggleSidebar">
                        <i data-feather="menu"></i>
                    </button>
                </div>
                <div class="header-right">
                    <a href="<?php echo $base_url; ?>/index.php" class="btn btn-outline-secondary btn-sm" style="margin-right: 12px;">
                        <i data-feather="external-link"></i>
                        View Site
                    </a>
                    <div class="user-profile">
                        <span class="user-name"><?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin'; ?></span>
                        <div class="user-avatar">
                            <span><?php echo substr(isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'A', 0, 1); ?></span>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Breadcrumb -->
            <?php if (isset($breadcrumb) && is_array($breadcrumb)) { ?>
            <div class="breadcrumb-wrapper">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>/admin/">Home</a></li>
                        <?php foreach ($breadcrumb as $item) { ?>
                            <li class="breadcrumb-item <?php echo $item['active'] ? 'active' : ''; ?>">
                                <?php echo $item['active'] ? htmlspecialchars($item['text']) : '<a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['text']) . '</a>'; ?>
                            </li>
                        <?php } ?>
                    </ol>
                </nav>
            </div>
            <?php } ?>
            
            <!-- Page Content -->
            <div class="page-content">
                <?php
                if (isset($_SESSION['message'])) {
                    $class = isset($_SESSION['message_type']) ? 'alert-' . htmlspecialchars($_SESSION['message_type']) : 'alert-info';
                    echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">';
                    echo htmlspecialchars($_SESSION['message']);
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                }
                ?>
                
                <!-- Page Header -->
                <?php if (isset($page_header)) { ?>
                    <div class="page-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="page-title"><?php echo htmlspecialchars($page_header['title']); ?></h1>
                                <?php if (isset($page_header['subtitle'])) { ?>
                                    <p class="page-subtitle"><?php echo htmlspecialchars($page_header['subtitle']); ?></p>
                                <?php } ?>
                            </div>
                            <?php if (isset($page_header['action'])) { ?>
                                <a href="<?php echo htmlspecialchars($page_header['action']['url']); ?>" class="btn btn-primary">
                                    <i data-feather="<?php echo htmlspecialchars($page_header['action']['icon']); ?>"></i>
                                    <?php echo htmlspecialchars($page_header['action']['text']); ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
                
                <!-- Dynamic Content -->
                <?php
                if (isset($page_content)) {
                    echo $page_content;
                } elseif (isset($content_file) && file_exists($content_file)) {
                    include $content_file;
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Feather Icons JS -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <!-- Custom Theme JS -->
    <script src="<?php echo $base_url; ?>/admin/theme/assets/js/admin-theme.js"></script>
    
    <script>
        // Initialize Feather Icons
        feather.replace();
    </script>
    
    <?php if (isset($custom_js)) { echo $custom_js; } ?>
</body>
</html>
