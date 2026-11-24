
<?php
declare(strict_types=1);

// University database connection details
$database_host = "dbhost.cs.man.ac.uk";
$database_user = "m52846lg";
$database_pass = "hfkgiYg4aN5GDG+iVvk6wJSlowk5rRPh6QpEDHrQMLE";
$database_name = "m52846lg";

// Connect to the database using PDO
$dsn = "mysql:host={$database_host};dbname={$database_name};charset=utf8mb4";

try {
    $pdo = new PDO(
        $dsn,
        $database_user,
        $database_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $exception) {
    error_log("Database connection failed: " . $exception->getMessage());
    http_response_code(500);
    exit("Database connection failed. Please check your database settings.");
}

// Start the session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Escape output safely
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Require a logged-in user
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Generate CSRF token
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if (!is_string($submitted) || !is_string($stored) ||
        $stored === '' || !hash_equals($stored, $submitted)) {
        http_response_code(400);
        exit('Invalid form submission. Please try again.');
    }
}

