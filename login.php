<?php
require_once 'config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            // Prioritize the name entered on the login page if provided
            $_SESSION['user_name'] = !empty($_POST['display_name']) ? trim($_POST['display_name']) : $user['name'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IGCMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css?v=12.0">
</head>
<body class="auth-body">

<div class="auth-split-wrapper">
    <div class="auth-left-panel">
        <div class="auth-left-content">
            <span class="material-symbols-outlined auth-logo" style="font-size: 5rem; color: #fff; margin-bottom: 2rem;">eco</span>
            <h1>Green Campus<br>Management System</h1>
            <p>Smart sustainability for a greener future.</p>
        </div>
    </div>
    
    <div class="auth-right-panel">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Welcome Back</h2>
                <p>Please enter your credentials to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="display_name">Your Name (for Welcome)</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">person</span>
                        <input type="text" id="display_name" name="display_name" class="form-control" placeholder="Optional: Enter your name">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">mail</span>
                        <input type="email" id="email" name="email" class="form-control" required placeholder="name@example.com">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                </div>
                <button type="submit" class="btn-auth">Login Securely</button>
            </form>
            
            <div class="auth-footer">
                Don't have an account? <a href="signup.php">Create Account</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
