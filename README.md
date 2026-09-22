# Anime & Manga E-Commerce Store Database Design

## ✦ Project Overview
This project encompasses the end-to-end design and implementation of a relational database for an Anime & Manga E-Commerce Store. The workflow covers conceptual modeling via an Enhanced Entity-Relationship (EER) diagram, structural definition using Data Definition Language (DDL), transactional operations via Data Manipulation Language (DML), and integration with a dynamic PHP frontend.

## ✦ Problem Statement
An online retail platform requires a robust, normalized relational database capable of managing diverse entities—ranging from user roles and shipping addresses to complex product catalogs containing both physical and digital items. The challenge involves enforcing strict data integrity, handling transactional business logic safely, and connecting this backend architecture to a responsive web interface that allows customers to search and filter products efficiently.

## ✦ Goals
* **Conceptual Modeling:** Map out the overall database architecture using an EER diagram to define primary entities (users, customers, orders, shipments, payments) and their relationships.
* **Schema Definition (DDL):** Create a highly normalized database schema featuring advanced relational concepts, such as sub-typing (e.g., extending a base `products` table into distinct `physical_products` and `digital_products` tables).
* **Data Management (DML):** Execute secure SQL transactions to insert new users, customers, and products, update pricing dynamically, and run complex analytical queries joining multiple tables (e.g., retrieving customer reviews with associated usernames and product names).
* **Frontend Integration:** Develop a PHP-based web interface (`index.php`) that connects directly to the database, supporting dynamic category filtering, keyword searches, and product display.

## ✦ Key Insights & Technical Highlights
* **Advanced Relational Inheritance:** The DDL script efficiently isolates base user credentials (the `users` table) from role-specific attributes (the `admins` and `customers` tables). Similarly, products are split to seamlessly accommodate distinct attributes like weight/dimensions for physical goods and file size/URLs for digital goods.
* **Data Integrity & Safety:** The schema enforces strict referential integrity using constraints such as `ON DELETE CASCADE` and `ON UPDATE CASCADE`. Data entry is secured using `START TRANSACTION` and `COMMIT` statements, leveraging SQL variables (e.g., `@new_user_id`) to flawlessly maintain primary and foreign key relationships during sequential multi-table inserts.
* **Dynamic UI Rendering:** The PHP frontend dynamically builds SQL queries (`$products_query`) based on user-selected category filters and secure `real_escape_string` search inputs. It also intelligently assigns visual icons and badges to items on the fly based on product types or name string matches (e.g., identifying "Figure", "Nendoroid", or "Digital" items).

## ✦ Tools & Technologies
* **Database Management:** MySQL / MariaDB (DDL, DML, Transactions, Relational Constraints, Joins).
* **Data Architecture:** Enhanced Entity-Relationship (EER) Modeling.
* **Frontend/Backend Integration:** PHP, HTML, CSS.

## ✦ Conclusion
By implementing a carefully normalized relational schema, this project successfully establishes a highly scalable and secure foundation for an e-commerce platform. The strict enforcement of database constraints guarantees data integrity across complex operations, while the seamless integration with a PHP frontend demonstrates how a well-structured backend directly enables dynamic, user-friendly features like intelligent product filtering and secure search capabilities.
