<?php
session_start();

// Redirect if already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true){
    header('Location: index.php');
    exit;
}

$error = '';

if($_POST && isset($_POST['username']) && isset($_POST['password'])){
    $usersFile = __DIR__.'/../data/users.json';
    
    if(file_exists($usersFile)){
        $users = json_decode(file_get_contents($usersFile), true);
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        if(isset($users[$username]) && password_verify($password, $users[$username])){
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Users file is missing';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Login – SimplePHP Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
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
    align-items: center;
    justify-content: center;
    padding: 20px;
  }

  .login-container {
    max-width: 400px;
    width: 100%;
  }

  .login-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }

  .login-card h1 {
    font-size: 28px;
    margin-bottom: 8px;
    font-weight: 800;
    text-align: center;
    color: #2563eb;
  }

  .login-card p {
    color: #666;
    font-size: 14px;
    text-align: center;
    margin-bottom: 30px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
  }

  .form-group input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #333;
    font-size: 14px;
    font-family: inherit;
  }

  .form-group input:focus {
    outline: none;
    border-color: #2563eb;
    background: #f8f9fa;
  }

  .button {
    width: 100%;
    background-color: #2563eb;
    color: #fff;
    padding: 14px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s, transform 0.2s;
    margin-top: 10px;
  }

  .button:hover {
    background-color: #1e40af;
    transform: scale(1.05);
  }

  .error {
    background: rgba(244,67,54,0.2);
    border: 1px solid rgba(244,67,54,0.4);
    color: #f44336;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
    text-align: center;
  }
</style>
</head>
<body>
<div class="login-container">
<div class="login-card">
<h1>SimplePHP Admin</h1>
<p>Sign in to the admin panel</p>

<?php if($error): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
<div class="form-group">
<label>Username</label>
<input type="text" name="username" required autofocus>
</div>
<div class="form-group">
<label>Password</label>
<input type="password" name="password" required>
</div>
<button type="submit" class="button">Login</button>
</form>
</div>
</div>
</body>
</html>
