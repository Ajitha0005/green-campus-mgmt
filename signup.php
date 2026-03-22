<?php
require_once 'config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'] ?? 'student';
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password, $role])) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = 'Something went wrong. Please try again.';
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
    <title>Create Account - IGCMS</title>
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
            <p>Join our mission for a sustainable campus.</p>
        </div>
    </div>
    
    <div class="auth-right-panel">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Create Account</h2>
                <p>Join the IGCMS community today</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">person</span>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="Enter your full name">
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
                    <label for="role">Joined As</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon" style="left: 1.2rem; color: #999;">badge</span>
                        <select id="role" name="role" class="form-control" required style="padding-left: 3.5rem;">
                            <option value="student">Student</option>
                            <option value="staff">Staff Member</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input type="password" id="password" name="password" class="form-control" required placeholder="Create a password">
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">key</span>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Confirm your password">
                    </div>
                </div>
                <button type="submit" class="btn-auth">Create Account</button>
            </form>
            
            <div class="auth-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
