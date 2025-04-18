<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: form.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Technical Resume Builder</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <img src="assets/images/banner2.jpeg" alt="Banner" class="banner-image">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Logo" class="logo-image">
            </div>
        </div>

        <div class="welcome-section">
            <h1>Welcome to Technical Resume Builder</h1>
            <p>Create professional and impressive technical resumes with ease.</p>
        </div>

        <div class="auth-container">
            <h2>Login to Continue</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <form action="process_login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="auth-button">Login</button>
                
                <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
            </form>
        </div>

        <footer class="page-footer">
            <img src="assets/images/banner.png" alt="Footer Banner" class="footer-banner">
        </footer>
    </div>
</body>
</html>