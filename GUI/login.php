<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT u.*, c.customer_id 
              FROM users u 
              LEFT JOIN customers c ON u.id = c.customer_id 
              WHERE u.username = '$username' AND u.type = 'customer'";
    
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Simple password check (in production, use password_hash and password_verify)
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['customer_id'] = $user['customer_id'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Invalid username or password';
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Anime & Manga Store</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🎌 Anime & Manga Store</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="register.php">Register</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="auth-container">
            <h2>Login to Your Account</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-submit">Login</button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Anime & Manga Store - All Rights Reserved</p>
        </div>
    </footer>
</body>
</html>