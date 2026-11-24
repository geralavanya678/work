<?php
declare(strict_types=1);

/*
 * Database configuration for the University of Manchester CS server.
 * Replace YOUR_DATABASE_PASSWORD with your actual database password.
 * Do not commit a real password to a public repository.
 */
$database_host = "dbhost.cs.man.ac.uk";
$database_user = "m52846lg";
$database_pass = "YOUR_DATABASE_PASSWORD";
$database_name = "m52846lg";

$dsn = "mysql:host={$database_host};dbname={$database_name};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $database_user, $database_pass, $options);
} catch (PDOException $exception) {
    error_log("Database connection failed: " . $exception->getMessage());
    http_response_code(500);
    exit("Unable to connect to the database. Check the database settings in db.php.");
}
