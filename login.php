<?php
require_once __DIR__ . '/db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: summary.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $statement = $pdo->prepare('SELECT id, username, password FROM christmas_users WHERE username = :username');
    $statement->execute([':username' => $username]);
    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: summary.php');
        exit;
    }
    $error = 'Username or password is incorrect.';
}

page_header('Log in');
?>
<section class="panel narrow">
    <h1>Welcome back</h1>
    <?php if (isset($_GET['registered'])): ?><p class="notice success">Account created. You can now log in.</p><?php endif; ?>
    <?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="username">Username</label>
        <input id="username" name="username" required autocomplete="username">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
        <button type="submit">Log in</button>
    </form>
    <p class="form-note">Don't have an account? <a href="register.php">Register here</a>.</p>
</section>
<?php page_footer(); ?>
