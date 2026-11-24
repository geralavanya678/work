<?php
declare(strict_types=1);

/*
 * COMP10120 Christmas Gift Organiser
 * Database settings supplied from the University Web Dashboard.
 *
 * IMPORTANT: replace CHANGE_THIS_WITH_YOUR_NEW_DATABASE_PASSWORD with the
 * NEW password from the Web Dashboard. Do not use the password previously
 * pasted into chat; treat it as compromised.
 */
$database_host = 'dbhost.cs.man.ac.uk';
$database_user = 'm52846lg';
$database_pass = 'CHANGE_THIS_WITH_YOUR_NEW_DATABASE_PASSWORD';
$database_name = 'm52846lg';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $dsn = "mysql:host={$database_host};dbname={$database_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $database_user, $database_pass, $options);
} catch (PDOException $exception) {
    // Log technical details on the server; do not expose credentials to visitors.
    error_log('Christmas Gift Organiser database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('The database connection could not be established. Check the database settings in db.php.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(400);
        exit('Invalid form submission. Please go back and try again.');
    }
}

function page_header(string $title): void
{
    ?><!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | Christmas Gift Organiser</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
    <header class="site-header">
        <a class="brand" href="<?= !empty($_SESSION['user_id']) ? 'summary.php' : 'login.php' ?>">Christmas Gift Organiser</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <nav>
                <a href="summary.php">Summary</a>
                <a href="add_gift.php">Add gift</a>
                <a href="search_amend_gift.php">Amend gift</a>
                <a href="logout.php">Log out</a>
            </nav>
        <?php endif; ?>
    </header>
    <main class="container">
    <?php
}

function page_footer(): void
{
    ?>
    </main>
    <footer class="site-footer">Plan thoughtful gifts, without losing track of your budget.</footer>
    </body>
    </html>
    <?php
}
