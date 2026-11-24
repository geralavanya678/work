<?php
require_once __DIR__ . '/db.php';
require_login();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `christmas_users` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `username` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_christmas_users_username` (`username`),
                UNIQUE KEY `uq_christmas_users_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `christmas_gifts` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT UNSIGNED NOT NULL,
                `recipient` VARCHAR(255) NOT NULL,
                `max_budget` DECIMAL(10,2) NOT NULL,
                `actual_cost` DECIMAL(10,2) DEFAULT NULL,
                `purchase_date` DATE DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_christmas_gifts_user` (`user_id`),
                CONSTRAINT `fk_christmas_gifts_user`
                    FOREIGN KEY (`user_id`) REFERENCES `christmas_users` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $message = 'Database tables are ready. You can now use the organiser.';
    } catch (PDOException $exception) {
        error_log('Table creation failed: ' . $exception->getMessage());
        $error = 'The tables could not be created. Check that the database account has permission to create tables.';
    }
}

page_header('Set up database');
?>
<section class="panel narrow">
    <h1>Database setup</h1>
    <p>This creates the users and gifts tables required by the application.</p>
    <?php if ($message): ?><p class="notice success"><?= e($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <button type="submit">Create tables</button>
    </form>
    <p class="muted">Run this page once after configuring db.php, then return to registration.</p>
    <a href="register.php">Go to registration</a>
</section>
<?php page_footer(); ?>
