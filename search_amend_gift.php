<?php
require_once 'db.php';
require_login();

$searchTerm = '';
$gift = null;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'search') {
        $searchTerm = trim($_POST['recipient'] ?? '');

        if ($searchTerm === '') {
            $error = 'Enter a recipient name to search for.';
        } else {
            $statement = $pdo->prepare(
                'SELECT id, recipient, max_budget, actual_cost, purchase_date
                 FROM christmas_gifts
                 WHERE user_id = :user_id AND recipient LIKE :recipient
                 ORDER BY recipient ASC
                 LIMIT 1'
            );
            $statement->execute([
                'user_id' => $_SESSION['user_id'],
                'recipient' => '%' . $searchTerm . '%',
            ]);
            $gift = $statement->fetch();

            if (!$gift) {
                $error = 'No matching recipient was found in your gift list.';
            }
        }
    } elseif ($action === 'update') {
        $giftId = filter_input(INPUT_POST, 'gift_id', FILTER_VALIDATE_INT);
        $actualCost = trim($_POST['actual_cost'] ?? '');
        $purchaseDate = trim($_POST['purchase_date'] ?? '');

        if (!$giftId) {
            $error = 'Invalid gift selected.';
        } elseif (!is_numeric($actualCost) || (float) $actualCost < 0
            || (float) $actualCost > 99999999.99) {
            $error = 'Enter a valid non-negative actual cost.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $purchaseDate)
            || !checkdate(
                (int) substr($purchaseDate, 5, 2),
                (int) substr($purchaseDate, 8, 2),
                (int) substr($purchaseDate, 0, 4)
            )) {
            $error = 'Enter a valid purchase date.';
        } else {
            // user_id in the WHERE clause prevents editing another user's gift.
            $update = $pdo->prepare(
                'UPDATE christmas_gifts
                 SET actual_cost = :actual_cost, purchase_date = :purchase_date
                 WHERE id = :id AND user_id = :user_id'
            );
            $update->execute([
                'actual_cost' => number_format((float) $actualCost, 2, '.', ''),
                'purchase_date' => $purchaseDate,
                'id' => $giftId,
                'user_id' => $_SESSION['user_id'],
            ]);

            if ($update->rowCount() > 0) {
                $message = 'Gift details updated successfully.';
            } else {
                $message = 'No changes were made, or the gift was not found.';
            }
        }

        // Re-fetch only a gift belonging to the logged-in user.
        if ($giftId) {
            $statement = $pdo->prepare(
                'SELECT id, recipient, max_budget, actual_cost, purchase_date
                 FROM christmas_gifts
                 WHERE id = :id AND user_id = :user_id'
            );
            $statement->execute([
                'id' => $giftId,
                'user_id' => $_SESSION['user_id'],
            ]);
            $gift = $statement->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search and Amend | Christmas Gift Organiser</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container narrow">
    <nav><a href="summary.php">← Back to summary</a></nav>
    <h1>Search and amend a gift</h1>

    <?php if ($message !== ''): ?>
        <p class="message success"><?= escape($message) ?></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="message error"><?= escape($error) ?></p>
    <?php endif; ?>

    <form method="post" action="search_amend_gift.php">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        <input type="hidden" name="action" value="search">

        <label for="recipient">Recipient name</label>
        <input id="recipient" name="recipient" required
               value="<?= escape($searchTerm) ?>">
        <button type="submit">Search</button>
    </form>

    <?php if ($gift): ?>
        <section class="edit-panel">
            <h2>Recipient found: <?= escape($gift['recipient']) ?></h2>
            <p>Maximum budget: £<?= number_format((float) $gift['max_budget'], 2) ?></p>

            <form method="post" action="search_amend_gift.php">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="gift_id" value="<?= (int) $gift['id'] ?>">

                <label for="actual_cost">Actual cost (£)</label>
                <input id="actual_cost" type="number" name="actual_cost"
                       min="0" max="99999999.99" step="0.01" required
                       value="<?= $gift['actual_cost'] === null
                           ? '' : escape($gift['actual_cost']) ?>">

                <label for="purchase_date">Purchase date</label>
                <input id="purchase_date" type="date" name="purchase_date" required
                       value="<?= escape($gift['purchase_date'] ?? '') ?>">

                <button type="submit">Update gift</button>
            </form>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
