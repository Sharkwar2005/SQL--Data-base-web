<?php
require_once 'config.php';

$conn = getDBConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = intval($_GET['id']);

// Get product details
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          WHERE p.product_id = $product_id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$product = $result->fetch_assoc();

// Get product specific details (physical or digital)
if ($product['type'] == 'physical') {
    $details_query = "SELECT * FROM physical_products WHERE product_id = $product_id";
    $details_result = $conn->query($details_query);
    $physical_details = $details_result->fetch_assoc();
} elseif ($product['type'] == 'digital') {
    $details_query = "SELECT * FROM digital_products WHERE product_id = $product_id";
    $details_result = $conn->query($details_query);
    $digital_details = $details_result->fetch_assoc();
}

// Handle add to cart
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);
    
    if ($quantity > 0 && $quantity <= $product['stock']) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        $message = 'Product added to cart successfully!';
    } else {
        $message = 'Invalid quantity!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Anime & Manga Store</title>
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
            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="product-detail">
                <div class="product-main-image">
                    <span class="product-icon-large">
                        <?php 
                        if ($product['type'] == 'digital') echo '💾';
                        elseif (strpos($product['name'], 'Figure') !== false || strpos($product['name'], 'Nendoroid') !== false) echo '🎭';
                        elseif (strpos($product['name'], 'T-Shirt') !== false || strpos($product['name'], 'Hoodie') !== false) echo '👕';
                        else echo '📚';
                        ?>
                    </span>
                    <?php if ($product['type'] == 'digital'): ?>
                        <span class="badge-large digital">Digital Product</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-meta">
                        <div>
                            <strong>Category:</strong> 
                            <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                        </div>
                        <div>
                            <strong>Type:</strong> 
                            <?php echo ucfirst($product['type']); ?>
                        </div>
                    </div>
                    
                    <div class="product-price-large">
                        <?php echo number_format($product['price'], 2); ?> EGP
                    </div>
                    
                    <div class="product-description-full">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                    
                    <?php if (isset($physical_details)): ?>
                        <div class="product-specs">
                            <h3>Product Specifications</h3>
                            <ul>
                                <li><strong>Weight:</strong> <?php echo $physical_details['weight']; ?> kg</li>
                                <li><strong>Dimensions:</strong> <?php echo htmlspecialchars($physical_details['dimensions']); ?></li>
                                <li><strong>Shipping:</strong> <?php echo $physical_details['shipping_required'] ? 'Required' : 'Not Required'; ?></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($digital_details)): ?>
                        <div class="product-specs">
                            <h3>Digital Product Details</h3>
                            <ul>
                                <li><strong>File Size:</strong> <?php echo $digital_details['file_size_mb']; ?> MB</li>
                                <li><strong>Instant Download:</strong> Available after purchase</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="stock-info">
                        <strong>Availability:</strong> 
                        <span class="stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php 
                            if ($product['type'] == 'digital') {
                                echo "Always Available";
                            } else {
                                echo $product['stock'] > 0 ? "In Stock ({$product['stock']} available)" : 'Out of Stock'; 
                            }
                            ?>
                        </span>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <form method="POST" class="add-to-cart-form">
                            <div class="quantity-selector">
                                <label for="quantity">Quantity:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                       max="<?php echo $product['type'] == 'digital' ? 1 : $product['stock']; ?>">
                            </div>
                            <button type="submit" name="add_to_cart" class="btn btn-large">
                                Add to Cart
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <div class="product-actions">
                        <a href="index.php" class="btn btn-secondary">Back to Products</a>
                    </div>
                </div>
            </div>
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