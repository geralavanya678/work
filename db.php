<?php
declare(strict_types=1);

/*
 * Update these four values using the database details shown in your
 * University Web Dashboard. Do not commit real credentials to a public repo.
 */
$host = 'localhost';
$dbname = 'YOUR_DATABASE_NAME';
$username = 'YOUR_DATABASE_USERNAME';
$password = 'YOUR_DATABASE_PASSWORD';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        $options
    );
} catch (PDOException $exception) {
    // Log details on the server; do not expose credentials or database errors.
    error_log($exception->getMessage());
    exit('Could not connect to the database. Check the settings in db.php.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function escape(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
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

function verify_csrf(): void
{
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!is_string($submittedToken)
        || !hash_equals($_SESSION['csrf_token'] ?? '', $submittedToken)) {
        http_response_code(400);
        exit('Invalid form submission. Please go back and try again.');
    }
}
