<?php
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: summary.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $plainPassword = $_POST['password'] ?? '';

    $statement = $pdo->prepare(
        'SELECT id, username, password
         FROM christmas_users
         WHERE username = :username'
    );
    $statement->execute(['username' => $username]);
    $user = $statement->fetch();

    if ($user && is_string($plainPassword)
        && password_verify($plainPassword, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: summary.php');
        exit;
    }

    $error = 'Incorrect username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Christmas Gift Organiser</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container narrow">
    <h1>Welcome back</h1>
    <p class="muted">Log in to view your gift list.</p>

    <?php if (isset($_GET['registered'])): ?>
        <p class="message success">Registration successful. You can now log in.</p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="message error"><?= escape($error) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

        <label for="username">Username</label>
        <input id="username" name="username" required
               value="<?= escape($username) ?>">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Log in</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register</a>.</p>
</main>
</body>
</html>
