<?php
require_once 'db.php';
require_login();

$errors = [];
$recipient = '';
$maxBudget = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $recipient = trim($_POST['recipient'] ?? '');
    $maxBudget = trim($_POST['max_budget'] ?? '');

    if ($recipient === '' || strlen($recipient) > 255) {
        $errors[] = 'Enter a recipient name of no more than 255 characters.';
    }

    if (!is_numeric($maxBudget) || (float) $maxBudget < 0
        || (float) $maxBudget > 99999999.99) {
        $errors[] = 'Enter a valid non-negative budget.';
    }

    if (!$errors) {
        $statement = $pdo->prepare(
            'INSERT INTO christmas_gifts (user_id, recipient, max_budget)
             VALUES (:user_id, :recipient, :max_budget)'
        );
        $statement->execute([
            'user_id' => $_SESSION['user_id'],
            'recipient' => $recipient,
            'max_budget' => number_format((float) $maxBudget, 2, '.', ''),
        ]);

        header('Location: summary.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add a Gift | Christmas Gift Organiser</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container narrow">
    <nav><a href="summary.php">← Back to summary</a></nav>
    <h1>Add a gift recipient</h1>

    <?php foreach ($errors as $error): ?>
        <p class="message error"><?= escape($error) ?></p>
    <?php endforeach; ?>

    <form method="post" action="add_gift.php">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

        <label for="recipient">Recipient name</label>
        <input id="recipient" name="recipient" maxlength="255" required
               value="<?= escape($recipient) ?>">

        <label for="max_budget">Maximum budget (£)</label>
        <input id="max_budget" type="number" name="max_budget"
               min="0" max="99999999.99" step="0.01" required
               value="<?= escape($maxBudget) ?>">

        <button type="submit">Add gift</button>
    </form>
</main>
</body>
</html>
