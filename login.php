<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password']; // Plain text password
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();
    
    // FIX: Support BOTH plain text AND hashed passwords
    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        redirect('index.php');
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tripify</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <i class="fas fa-compass"></i>
                <span>Tripify</span>
            </a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="trips.php" class="nav-link">All Trips</a>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-link">Register</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">
                <i class="fas fa-sign-in-alt"></i> Welcome Back
            </h2>
            <form method="POST">
                <div class="form-group">
                    <label>Username or Email</label>
                    <input type="text" name="login" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <!-- FIX: removed name="login" from button -->
                <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>

                <p style="text-align: center; margin-top: 1rem;">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>© 2025 Tripify — Share your adventures ✈️ | College Project</p>
    </footer>
</body>
</html>