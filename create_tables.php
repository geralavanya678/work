<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS christmas_users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS christmas_gifts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            recipient VARCHAR(255) NOT NULL,
            max_budget DECIMAL(10,2) NOT NULL,
            actual_cost DECIMAL(10,2) NULL DEFAULT NULL,
            purchase_date DATE NULL DEFAULT NULL,
            CONSTRAINT fk_christmas_gifts_user
                FOREIGN KEY (user_id) REFERENCES christmas_users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $message = 'Database tables created successfully.';
} catch (PDOException $exception) {
    error_log('Table creation failed: ' . $exception->getMessage());
    $message = 'The tables could not be created. Check your database permissions and connection settings.';
}

page_header('Database setup');
?>
<section class="form-card">
    <h1>Database setup</h1>
    <div class="alert alert-success"><?= e($message) ?></div>
    <p>For security, remove or rename <code>create_tables.php</code> after setup.</p>
    <a class="button" href="summary.php">Go to gift summary</a>
</section>
<?php page_footer(); ?>
