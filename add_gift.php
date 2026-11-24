<?php
require_once __DIR__ . '/db.php';
require_login();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $recipient = trim((string)($_POST['recipient'] ?? ''));
    $budget = filter_var($_POST['max_budget'] ?? null, FILTER_VALIDATE_FLOAT);

    if ($recipient === '') {
        $error = 'Enter a recipient name.';
    } elseif ($budget === false || $budget < 0 || $budget > 99999999.99) {
        $error = 'Enter a valid, non-negative maximum budget.';
    } else {
        $statement = $pdo->prepare(
            'INSERT INTO christmas_gifts (user_id, recipient, max_budget) VALUES (:user_id, :recipient, :budget)'
        );
        $statement->execute([
            ':user_id' => $_SESSION['user_id'],
            ':recipient' => $recipient,
            ':budget' => number_format((float)$budget, 2, '.', ''),
        ]);
        header('Location: summary.php?added=1');
        exit;
    }
}

page_header('Add a gift');
?>
<section class="panel narrow">
    <h1>Add a gift recipient</h1>
    <p>Add the person and the maximum amount you plan to spend.</p>
    <?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="recipient">Recipient name</label>
        <input id="recipient" name="recipient" maxlength="255" required value="<?= e($_POST['recipient'] ?? '') ?>">
        <label for="max_budget">Maximum budget (£)</label>
        <input id="max_budget" name="max_budget" type="number" min="0" max="99999999.99" step="0.01" required value="<?= e($_POST['max_budget'] ?? '') ?>">
        <button type="submit">Add gift</button>
    </form>
    <p class="form-note"><a href="summary.php">Back to gift summary</a></p>
</section>
<?php page_footer(); ?>
