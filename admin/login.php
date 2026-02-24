<?php
require_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $settings = getSettings($pdo);

    if (password_verify($password, $settings['admin_password'] ?? '')) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Premium -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/fontawesome.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/solid.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/regular.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/light.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/brands.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/duotone.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/thin.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>

<body class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-terminal"></i>
            <h1>Portfolio Admin</h1>
            <p>Enter your password to continue</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required autofocus placeholder="Enter admin password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
    </div>
</body>

</html>