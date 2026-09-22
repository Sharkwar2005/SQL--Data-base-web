<?php
require_once 'config.php';

$conn = getDBConnection();

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id] = intval($quantity);
            }
        }
    } elseif (isset($_POST['remove_item'])) {
        $product_id = intval($_POST['product_id']);
        unset($_SESSION['cart'][$product_id]);
    }
}

// Get cart items details
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $query = "SELECT * FROM products WHERE product_id IN ($product_ids)";
    $result = $conn->query($query);
    
    while ($product = $result->fetch_assoc()) {
        $product['quantity'] = $_SESSION['cart'][$product['product_id']];
        $product['subtotal'] = $product['price'] * $product['quantity'];
        $total += $product['subtotal'];
        $cart_items[] = $product;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Anime & Manga Store</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🎌 Anime & Manga Store</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="cart.php">Cart (<?php echo count($_SESSION['cart']); ?>)</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="products">
            <h2>Shopping Cart</h2>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <p>Your cart is empty!</p>
                    <a href="index.php" class="btn">Continue Shopping</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="cart-product-info">
                                            <span class="cart-icon">
                                                <?php 
                                                if ($item['type'] == 'digital') echo '💾';
                                                elseif (strpos($item['name'], 'Figure') !== false) echo '🎭';
                                                elseif (strpos($item['name'], 'T-Shirt') !== false || strpos($item['name'], 'Hoodie') !== false) echo '👕';
                                                else echo '📚';
                                                ?>
                                            </span>
                                            <div>
                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                <br>
                                                <small><?php echo ucfirst($item['type']); ?> Product</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($item['price'], 2); ?> EGP</td>
                                    <td>
                                        <input type="number" name="quantity[<?php echo $item['product_id']; ?>]" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['type'] == 'digital' ? $item['quantity'] : $item['stock']; ?>"
                                               class="quantity-input">
                                    </td>
                                    <td><strong><?php echo number_format($item['subtotal'], 2); ?> EGP</strong></td>
                                    <td>
                                        <button type="submit" name="remove_item" value="1" 
                                                onclick="this.form.product_id.value='<?php echo $item['product_id']; ?>'"
                                                class="btn-remove">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <input type="hidden" name="product_id" value="">
                    
                    <div class="cart-actions">
                        <button type="submit" name="update_cart" class="btn">Update Cart</button>
                        <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Cart Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <strong><?php echo number_format($total, 2); ?> EGP</strong>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <strong><?php echo number_format($total, 2); ?> EGP</strong>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button type="button" onclick="alert('Checkout functionality coming soon!')" class="btn btn-large">
                                Proceed to Checkout
                            </button>
                        <?php else: ?>
                            <p class="checkout-notice">Please <a href="login.php">login</a> to checkout</p>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Anime & Manga Store - All Rights Reserved</p>
        </div>
    </footer>

    <?php $conn->close(); ?>
</body>
</html>