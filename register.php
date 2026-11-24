<?php
require_once __DIR__ . '/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        $error = 'Please complete every field.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Your password must be at least 8 characters long.';
    } else {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO christmas_users (username, email, password) VALUES (:username, :email, :password)'
            );
            $statement->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $error = 'That username or email address is already registered.';
            } else {
                error_log('Registration failed: ' . $exception->getMessage());
                $error = 'Registration could not be completed. Please try again later.';
            }
        }
    }
}

page_header('Register');
?>
<section class="panel narrow">
    <h1>Create your account</h1>
    <p>Register to keep your gift list private to you.</p>
    <?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="username">Username</label>
        <input id="username" name="username" maxlength="255" required value="<?= e($_POST['username'] ?? '') ?>">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" maxlength="255" required value="<?= e($_POST['email'] ?? '') ?>">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" minlength="8" required>
        <button type="submit">Register</button>
    </form>
    <p class="form-note">Already registered? <a href="login.php">Log in</a>.</p>
</section>
<?php page_footer(); ?>
