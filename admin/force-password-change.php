<?php
/**
 * Forced password change.
 * Reached when $_SESSION['must_change_password'] is set - e.g. an account
 * flagged after a known-exposed password, or a freshly created account.
 * No other admin page is reachable until this is cleared (enforced by
 * simplephp_require_admin_login() in includes/Security.php).
 */

require_once __DIR__ . '/../includes/Security.php';
simplephp_secure_session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Nothing to do here if the account isn't flagged - send them on.
if (empty($_SESSION['must_change_password'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$username = $_SESSION['admin_username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!simplephp_csrf_valid()) {
        $error = 'Your session expired. Please reload the page and try again.';
    } else {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            $usersFile = SIMPLEPHP_DATA_DIR . '/users.json';
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

            try {
                simplephp_json_update($usersFile, function ($data) use ($username, $newHash) {
                    // Store as a plain hash string - clears must_change_password.
                    $data[$username] = $newHash;
                    return $data;
                }, []);

                $_SESSION['must_change_password'] = false;
                header('Location: index.php');
                exit;
            } catch (RuntimeException $e) {
                $error = simplephp_safe_error($e, 'force-password-change');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Set a New Password - SimplePHP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    * { box-sizing: border-box; }
    html, body {
      height: 100%;
      background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-container { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
    .login-box { width: 100%; max-width: 440px; background: var(--bg-white); border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden; }
    .login-header { background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); padding: 36px 30px; text-align: center; color: white; }
    .login-logo { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
    .login-subtitle { font-size: 14px; opacity: 0.9; margin: 0; }
    .login-body { padding: 36px 30px; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-primary); font-size: 14px; }
    .form-control { border: 1px solid var(--color-border); border-radius: 8px; padding: 12px 16px; font-size: 14px; width: 100%; }
    .form-control:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(70,128,255,0.1); outline: none; }
    .btn-login { width: 100%; padding: 12px 20px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
    .btn-login:hover { background: var(--color-primary-dark); }
    .alert { border: none; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid; padding: 12px 16px; font-size: 14px; }
    .alert-danger { background-color: rgba(255,83,112,0.1); color: var(--color-danger); border-left-color: var(--color-danger); }
    .alert-info { background-color: rgba(70,128,255,0.08); color: var(--text-primary); border-left-color: var(--color-primary); }
    .help-text { font-size: 12px; color: var(--text-secondary); margin-top: 6px; }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <div class="login-header">
        <div class="login-logo">Set a New Password</div>
        <p class="login-subtitle">Required before you can continue</p>
      </div>
      <div class="login-body">
        <div class="alert alert-info">
          For security, this account must set a new password before using the admin area.
        </div>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
          <?php echo simplephp_csrf_field(); ?>
          <div class="form-group">
            <label for="new_password" class="form-label">New password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required autofocus>
            <div class="help-text">At least 8 characters.</div>
          </div>
          <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm new password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
          </div>
          <button type="submit" class="btn-login">Set Password</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
