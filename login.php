<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
redirect_if_logged_in();

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $statement = $pdo->prepare('SELECT id, username, password FROM christmas_users WHERE username = :username LIMIT 1');
    $statement->execute(['username' => $username]);
    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: summary.php');
        exit;
    }
    $error = 'The username or password was incorrect.';
}

page_header('Log in');
?>
<section class="form-card">
    <h1>Welcome back</h1>
    <?php if (isset($_GET['registered'])): ?><div class="alert alert-success">Registration successful. You can now log in.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="username">Username</label>
        <input id="username" name="username" required autocomplete="username" value="<?= e($username) ?>">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        <button type="submit">Log in</button>
    </form>
    <p class="form-foot">Don't have an account? <a href="register.php">Register</a>.</p>
</section>
<?php page_footer(); ?>
