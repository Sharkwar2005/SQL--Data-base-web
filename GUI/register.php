<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $phone = $conn->real_escape_string($_POST['phone']);
    
    // Check if username or email already exists
    $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $error = 'Username or email already exists';
    } else {
        // Insert user
        $insert_user = "INSERT INTO users (username, password, email, type) 
                        VALUES ('$username', '$password', '$email', 'customer')";
        
        if ($conn->query($insert_user)) {
            $user_id = $conn->insert_id;
            
            // Insert customer
            $insert_customer = "INSERT INTO customers (customer_id, phone, loyalty_points) 
                                VALUES ($user_id, '$phone', 0)";
            
            if ($conn->query($insert_customer)) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = 'Error creating account';
            }
        } else {
            $error = 'Error creating account';
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Anime & Manga Store</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🎌 Anime & Manga Store</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="auth-container">
            <h2>Create New Account</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <button type="submit" class="btn-submit">Create Account</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
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