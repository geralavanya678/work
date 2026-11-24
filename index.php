<?php
require_once __DIR__ . '/functions.php';

if (is_logged_in()) {
    header('Location: summary.php');
    exit;
}

page_header('Welcome');
?>
<section class="hero">
    <p class="eyebrow">Make the season organised</p>
    <h1>Plan your Christmas gifting 🎁</h1>
    <p>Keep track of who you are buying for, how much you plan to spend, and which presents you have purchased.</p>
    <div class="actions">
        <a class="button" href="register.php">Create an account</a>
        <a class="button button-secondary" href="login.php">Log in</a>
    </div>
</section>
<?php page_footer(); ?>
