<?php
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: summary.php');
    exit;
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $plainPassword = $_POST['password'] ?? '';

    if ($username === '' || strlen($username) > 255) {
        $errors[] = 'Enter a username of no more than 255 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
        $errors[] = 'Enter a valid email address.';
    }

    if (!is_string($plainPassword) || strlen($plainPassword) < 8) {
        $errors[] = 'Your password must contain at least 8 characters.';
    }

    if (!$errors) {
        $check = $pdo->prepare(
            'SELECT id FROM christmas_users WHERE username = :username OR email = :email'
        );
        $check->execute(['username' => $username, 'email' => $email]);

        if ($check->fetch()) {
            $errors[] = 'That username or email address is already registered. Please try another.';
        } else {
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
            $insert = $pdo->prepare(
                'INSERT INTO christmas_users (username, email, password)
                 VALUES (:username, :email, :password)'
            );

            try {
                $insert->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashedPassword,
                ]);
                header('Location: login.php?registered=1');
                exit;
            } catch (PDOException $exception) {
                error_log($exception->getMessage());
                $errors[] = 'Registration could not be completed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Christmas Gift Organiser</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container narrow">
    <h1>Create an account</h1>
    <p class="muted">Register to organise your Christmas gifts.</p>

    <?php foreach ($errors as $error): ?>
        <p class="message error"><?= escape($error) ?></p>
    <?php endforeach; ?>

    <form method="post" action="register.php">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

        <label for="username">Username</label>
        <input id="username" name="username" maxlength="255" required
               value="<?= escape($username) ?>">

        <label for="email">Email</label>
        <input id="email" type="email" name="email" maxlength="255" required
               value="<?= escape($email) ?>">

        <label for="password">Password (at least 8 characters)</label>
        <input id="password" type="password" name="password" minlength="8" required>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Log in</a>.</p>
</main>
</body>
</html>
