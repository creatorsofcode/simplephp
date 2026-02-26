<?php
/**
 * Admin Login Page - Professional Design
 * Star Admin 2 Inspired
 */

// Start session
if (!isset($_SESSION)) {
    session_start();
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = null;
$success = null;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // TODO: Replace with actual authentication logic
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['user_name'] = 'Administrator';
        $_SESSION['user_id'] = 1;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SimplePHP</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Feather Icons -->
    <link href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css" rel="stylesheet">
    
    <style>
        :root {
            --color-primary: #4680ff;
            --color-primary-dark: #3565dd;
            --color-danger: #ff5370;
            --color-border: #e3e6f0;
            --text-primary: #2c3e50;
            --text-secondary: #6c757d;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
        }
        
        * {
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-box {
            width: 100%;
            max-width: 420px;
            background: var(--bg-white);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .login-logo {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }
        
        .login-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 14px;
        }
        
        .form-control {
            border: 1px solid var(--color-border);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            color: var(--text-primary);
            background-color: var(--bg-white);
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(70, 128, 255, 0.1);
            outline: none;
        }
        
        .form-control::placeholder {
            color: var(--text-secondary);
        }
        
        .form-check {
            margin-bottom: 20px;
        }
        
        .form-check-input {
            accent-color: var(--color-primary);
            cursor: pointer;
            width: 18px;
            height: 18px;
            border-radius: 4px;
        }
        
        .form-check-label {
            margin-left: 8px;
            cursor: pointer;
            font-size: 14px;
            color: var(--text-primary);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px 20px;
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-login:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(70, 128, 255, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            border: none;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-danger {
            background-color: rgba(255, 83, 112, 0.1);
            color: var(--color-danger);
            border-left-color: var(--color-danger);
            padding: 12px 16px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: rgba(46, 216, 182, 0.1);
            color: #2ed8b6;
            border-left-color: #2ed8b6;
            padding: 12px 16px;
            font-size: 14px;
        }
        
        .login-footer {
            background-color: var(--bg-light);
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid var(--color-border);
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .login-help {
            margin-top: 20px;
            font-size: 13px;
        }
        
        .login-help a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-help a:hover {
            text-decoration: underline;
        }
        
        .form-icon {
            position: relative;
        }
        
        .form-icon i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
            width: 18px;
            height: 18px;
        }
        
        .form-icon input {
            padding-right: 40px;
        }
        
        @media (max-width: 576px) {
            .login-box {
                max-width: 100%;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .login-logo {
                font-size: 36px;
            }
            
            .login-footer {
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">ADMIN</div>
                <p class="login-subtitle">Professional Admin Dashboard</p>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i data-feather="alert-circle" style="width: 16px; height: 16px; margin-right: 8px; vertical-align: -2px;"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i data-feather="check-circle" style="width: 16px; height: 16px; margin-right: 8px; vertical-align: -2px;"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <div class="form-icon">
                            <input 
                                type="text" 
                                class="form-control" 
                                id="username" 
                                name="username" 
                                placeholder="Enter your username"
                                required
                                autofocus
                            >
                            <i data-feather="user"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="form-icon">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password"
                                required
                            >
                            <i data-feather="lock"></i>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i data-feather="arrow-right"></i>
                        Sign In
                    </button>
                </form>
                
                <div class="login-help">
                    <p style="margin: 0;">
                        Demo credentials: <strong>admin / admin</strong>
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="login-footer">
                © 2026 SimplePHP Admin. All rights reserved.
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        feather.replace();
    </script>
</body>
</html>
