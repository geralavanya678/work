<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
redirect_if_logged_in();

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || strlen($username) > 255) {
        $errors[] = 'Enter a username (maximum 255 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
        $errors[] = 'Enter a valid email address (maximum 255 characters).';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Your password must be at least 8 characters long.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM christmas_users WHERE username = :username OR email = :email LIMIT 1');
        $check->execute(['username' => $username, 'email' => $email]);

        if ($check->fetch()) {
            $errors[] = 'That username or email address is already registered. Please try another.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO christmas_users (username, email, password) VALUES (:username, :email, :password)');
            try {
                $insert->execute(['username' => $username, 'email' => $email, 'password' => $hash]);
                header('Location: login.php?registered=1');
                exit;
            } catch (PDOException $exception) {
                // A duplicate-key race is handled without exposing database details.
                if ($exception->getCode() === '23000') {
                    $errors[] = 'That username or email address is already registered.';
                } else {
                    error_log('Registration failed: ' . $exception->getMessage());
                    $errors[] = 'Registration could not be completed. Please try again later.';
                }
            }
        }
    }
}

page_header('Register');
?>
<section class="form-card">
    <h1>Create your account</h1>
    <p class="muted">Register to start organising your Christmas gifts.</p>
    <?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
    <form method="post" action="register.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="username">Username</label>
        <input id="username" name="username" maxlength="255" required autocomplete="username" value="<?= e($username) ?>">
        <label for="email">Email address</label>
        <input id="email" type="email" name="email" maxlength="255" required autocomplete="email" value="<?= e($email) ?>">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" minlength="8" required autocomplete="new-password">
        <button type="submit">Register</button>
    </form>
    <p class="form-foot">Already have an account? <a href="login.php">Log in</a>.</p>
</section>
<?php page_footer(); ?>
