<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function redirect_if_logged_in(): void
{
    if (is_logged_in()) {
        header('Location: summary.php');
        exit;
    }
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(400);
        exit('Invalid form submission. Please go back and try again.');
    }
}

function page_header(string $title): void
{
    $loggedIn = is_logged_in();
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . e($title) . ' | Christmas Gift Organiser</title>';
    echo '<link rel="stylesheet" href="style.css"></head><body>';
    echo '<header class="site-header"><a class="brand" href="index.php">🎄 Christmas Gift Organiser</a>';
    echo '<nav>';
    if ($loggedIn) {
        echo '<a href="summary.php">Gift summary</a><a href="add_gift.php">Add a gift</a>';
        echo '<a href="search_amend_gift.php">Amend a gift</a><a href="logout.php">Log out</a>';
    } else {
        echo '<a href="login.php">Log in</a><a href="register.php">Register</a>';
    }
    echo '</nav></header><main class="container">';
}

function page_footer(): void
{
    echo '</main><footer class="site-footer">Made for the COMP10120 PHP and MySQL lab</footer></body></html>';
}
