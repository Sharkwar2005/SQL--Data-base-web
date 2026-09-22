<?php
require_once 'config.php';

$conn = getDBConnection();

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Get filter parameters
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build products query
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.category_id 
                   WHERE 1=1";

if ($category_filter > 0) {
    $products_query .= " AND p.category_id = " . $category_filter;
}

if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $products_query .= " AND (p.name LIKE '%$search_safe%' OR p.description LIKE '%$search_safe%')";
}

$products_query .= " ORDER BY p.created_at DESC";
$products_result = $conn->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime & Manga Store</title>
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
        <aside class="sidebar">
            <h2>Categories</h2>
            <ul class="category-list">
                <li><a href="index.php" class="<?php echo $category_filter == 0 ? 'active' : ''; ?>">All Products</a></li>
                <?php 
                $categories_result->data_seek(0);
                while ($cat = $categories_result->fetch_assoc()): 
                ?>
                    <li>
                        <a href="index.php?category=<?php echo $cat['category_id']; ?>" 
                           class="<?php echo $category_filter == $cat['category_id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>

            <h2>Search</h2>
            <form method="GET" action="index.php">
                <?php if ($category_filter > 0): ?>
                    <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                <?php endif; ?>
                <input type="text" name="search" placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </aside>

        <section class="products">
            <h2>Products</h2>
            <div class="product-grid">
                <?php if ($products_result->num_rows > 0): ?>
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <span class="product-icon">
                                    <?php 
                                    if ($product['type'] == 'digital') echo '💾';
                                    elseif (strpos($product['name'], 'Figure') !== false || strpos($product['name'], 'Nendoroid') !== false) echo '🎭';
                                    elseif (strpos($product['name'], 'T-Shirt') !== false || strpos($product['name'], 'Hoodie') !== false) echo '👕';
                                    else echo '📚';
                                    ?>
                                </span>
                                <?php if ($product['type'] == 'digital'): ?>
                                    <span class="badge digital">Digital</span>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...
                            </p>
                            <p class="product-category">
                                <small><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></small>
                            </p>
                            <div class="product-footer">
                                <span class="price"><?php echo number_format($product['price'], 2); ?> EGP</span>
                                <span class="stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                    <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                </span>
                            </div>
                            <a href="product.php?id=<?php echo $product['product_id']; ?>" class="btn">View Details</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-products">No products available</p>
                <?php endif; ?>
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