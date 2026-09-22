
CREATE DATABASE IF NOT EXISTS ecommerce CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
USE ecommerce;

-- Users (base table for admins and customers)
CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` ENUM('customer','admin','guest') NOT NULL DEFAULT 'customer',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admins 
CREATE TABLE admins (
  admin_id INT UNSIGNED NOT NULL,                -- FK to users.id
  role VARCHAR(100) NOT NULL,
  hire_date DATE NULL,
  PRIMARY KEY (admin_id),
  CONSTRAINT fk_admin_user FOREIGN KEY (admin_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers 
CREATE TABLE customers (
  customer_id INT UNSIGNED NOT NULL,             -- FK to users.id
  phone VARCHAR(30),
  loyalty_points INT UNSIGNED DEFAULT 0,
  PRIMARY KEY (customer_id),
  CONSTRAINT fk_customer_user FOREIGN KEY (customer_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Addresses 
CREATE TABLE addresses (
  address_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id INT UNSIGNED NOT NULL,
  street VARCHAR(255) NOT NULL,
  building_number VARCHAR(50),
  apartment_number VARCHAR(50),
  city VARCHAR(100) NOT NULL,
  postal_code VARCHAR(30),
  country VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (address_id),
  INDEX idx_addresses_customer (customer_id),
  CONSTRAINT fk_address_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories
CREATE TABLE categories (
  category_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (category_id),
  UNIQUE KEY uq_category_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products
CREATE TABLE products (
  product_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  stock INT NOT NULL DEFAULT 0,
  category_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` ENUM('physical','digital','service') NOT NULL DEFAULT 'physical',
  PRIMARY KEY (product_id),
  INDEX idx_products_category (category_id),
  CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Physical products
CREATE TABLE physical_products (
  product_id INT UNSIGNED NOT NULL,  -- FK to products.product_id
  weight DECIMAL(8,3) NULL,          -- kg
  dimensions VARCHAR(100) NULL,      -- "30x20x10 cm"
  shipping_required TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (product_id),
  CONSTRAINT fk_physical_product FOREIGN KEY (product_id) REFERENCES products(product_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Digital products 
CREATE TABLE digital_products (
  product_id INT UNSIGNED NOT NULL,  -- FK to products.product_id
  file_size_mb DECIMAL(10,2) NULL,
  download_url VARCHAR(2048) NULL,
  license_key VARCHAR(255) NULL,
  PRIMARY KEY (product_id),
  CONSTRAINT fk_digital_product FOREIGN KEY (product_id) REFERENCES products(product_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders
CREATE TABLE orders (
  order_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id INT UNSIGNED NOT NULL,
  order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  shipping_address_id INT UNSIGNED NULL,
  billing_address_id INT UNSIGNED NULL,
  PRIMARY KEY (order_id),
  INDEX idx_orders_customer (customer_id),
  INDEX idx_orders_ship_addr (shipping_address_id),
  INDEX idx_orders_bill_addr (billing_address_id),
  CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_order_shipping_address FOREIGN KEY (shipping_address_id) REFERENCES addresses(address_id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_order_billing_address FOREIGN KEY (billing_address_id) REFERENCES addresses(address_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments
CREATE TABLE payments (
  payment_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  payment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  amount DECIMAL(12,2) NOT NULL,
  method ENUM('credit_card','paypal','bank_transfer','cash_on_delivery','other') NOT NULL DEFAULT 'credit_card',
  transaction_id VARCHAR(255) NULL,
  PRIMARY KEY (payment_id),
  INDEX idx_payments_order (order_id),
  CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shipments
CREATE TABLE shipments (
  shipment_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  tracking_number VARCHAR(255) NULL,
  carrier VARCHAR(150) NULL,
  status ENUM('preparing','in_transit','out_for_delivery','delivered','exception') NOT NULL DEFAULT 'preparing',
  shipped_date DATE NULL,
  delivery_date DATE NULL,
  PRIMARY KEY (shipment_id),
  INDEX idx_shipments_order (order_id),
  CONSTRAINT fk_shipment_order FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items (line items) — composite PK
CREATE TABLE order_items (
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  price_at_purchase DECIMAL(12,2) NOT NULL,  -- price snapshot
  PRIMARY KEY (order_id, product_id),
  INDEX idx_orderitems_product (product_id),
  CONSTRAINT fk_orderitems_order FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_orderitems_product FOREIGN KEY (product_id) REFERENCES products(product_id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wishlists
CREATE TABLE wishlists (
  wishlist_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (wishlist_id),
  INDEX idx_wishlist_customer (customer_id),
  INDEX idx_wishlist_product (product_id),
  CONSTRAINT fk_wishlist_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(product_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews
CREATE TABLE reviews (
  review_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  review_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (review_id),
  INDEX idx_reviews_customer (customer_id),
  INDEX idx_reviews_product (product_id),
  CONSTRAINT fk_review_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES products(product_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
