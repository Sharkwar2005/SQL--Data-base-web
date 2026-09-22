START TRANSACTION;

-- 1) Insert a new user (Ahmad)
INSERT INTO users (username, password, email)
VALUES ('ahmad_m', 'sirr_ahmad#2025', 'ahmad.m@example.com');
SET @new_user_id = LAST_INSERT_ID();   -- users.id

-- 2) Insert Ahmad as a customer (uses the generated users.id)
INSERT INTO customers (customer_id, phone, loyalty_points)
VALUES (@new_user_id, '050-123-4567', 25);

-- 3) Insert a new product category (Home Appliances)
INSERT INTO categories (name, description)
VALUES ('Aghizat Manziliya', 'Kol Ma Yakhoss Al Manzil Min Aghiza Kahrabaeya.');
SET @new_category_id = LAST_INSERT_ID();  -- categories.category_id

-- 4) Insert a new product (Coffee Maker) linked to the new category
INSERT INTO products (name, description, price, stock, category_id, created_at, `type`)
VALUES ('Makinat Qahwa', 'Lissonaa Afdal Fingan Qahwa.', 950.50, 80, @new_category_id, NOW(), 'physical');
SET @new_product_id = LAST_INSERT_ID();  -- products.product_id

-- 5) Update the phone number for the customer we just inserted
UPDATE customers
SET phone = '050-987-6543'
WHERE customer_id = @new_user_id;

-- 6) Increase the price of 'Makinat Qahwa' by 50.00 (using the inserted product id)
UPDATE products
SET price = price + 50.00
WHERE product_id = @new_product_id;

-- 7) Retrieve all information about product categories
SELECT * FROM categories;

-- 8) Get the names and prices of products more expensive than 1000.00
SELECT name AS product_name, price AS product_price
FROM products
WHERE price > 1000.00
ORDER BY price DESC;

-- 9) Get the customer's username and the product name for all reviews
SELECT
    u.username,
    p.name AS product_name,
    r.rating,
    r.comment
FROM users u
JOIN customers c ON u.id = c.customer_id
JOIN reviews r ON c.customer_id = r.customer_id
JOIN products p ON r.product_id = p.product_id;

-- 10) Find the category name that has the largest count of products
SELECT name
FROM categories
WHERE category_id = (
    SELECT category_id
    FROM products
    GROUP BY category_id
    ORDER BY COUNT(product_id) DESC
    LIMIT 1
);

COMMIT;
