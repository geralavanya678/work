<?php
require_once 'db.php';

/*
 * The lab's diagram on page 4 shows christmas_users and christmas_gifts.
 * The written requirements on page 5 also require an email address, so
 * this version includes an email column in christmas_users.
 */
$userTableSql = "
CREATE TABLE IF NOT EXISTS christmas_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

$giftTableSql = "
CREATE TABLE IF NOT EXISTS christmas_gifts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    max_budget DECIMAL(10,2) NOT NULL,
    actual_cost DECIMAL(10,2) DEFAULT NULL,
    purchase_date DATE DEFAULT NULL,
    CONSTRAINT fk_gift_user
        FOREIGN KEY (user_id) REFERENCES christmas_users(id)
        ON DELETE CASCADE,
    INDEX idx_gift_user_recipient (user_id, recipient)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

try {
    // Execute separately for compatibility with PDO configurations that
    // disable multiple SQL statements in one call.
    $pdo->exec($userTableSql);
    $pdo->exec($giftTableSql);
    echo 'Database tables created successfully. Delete or rename this setup file when finished.';
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo 'Could not create the tables. Check your database permissions and settings.';
}
