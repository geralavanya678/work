<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_login();

$errors = [];
$recipient = '';
$budget = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $recipient = trim($_POST['recipient'] ?? '');
    $budget = trim($_POST['max_budget'] ?? '');

    if ($recipient === '' || strlen($recipient) > 255) {
        $errors[] = 'Enter a recipient name (maximum 255 characters).';
    }
    if (!is_numeric($budget) || (float)$budget < 0 || (float)$budget > 99999999.99) {
        $errors[] = 'Enter a valid non-negative budget.';
    }

    if (!$errors) {
        $statement = $pdo->prepare('INSERT INTO christmas_gifts (user_id, recipient, max_budget) VALUES (:user_id, :recipient, :max_budget)');
        $statement->execute([
            'user_id' => $_SESSION['user_id'],
            'recipient' => $recipient,
            'max_budget' => number_format((float)$budget, 2, '.', '')
        ]);
        header('Location: summary.php?added=1');
        exit;
    }
}

page_header('Add a gift');
?>
<section class="form-card">
    <h1>Add a gift recipient 🎁</h1>
    <?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
    <form method="post" action="add_gift.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="recipient">Recipient name</label>
        <input id="recipient" name="recipient" maxlength="255" required value="<?= e($recipient) ?>" placeholder="e.g. Alex">
        <label for="max_budget">Maximum budget (£)</label>
        <input id="max_budget" type="number" name="max_budget" min="0" max="99999999.99" step="0.01" required value="<?= e($budget) ?>" placeholder="e.g. 25.00">
        <button type="submit">Add gift</button>
    </form>
    <p class="form-foot"><a href="summary.php">Back to gift summary</a></p>
</section>
<?php page_footer(); ?>
