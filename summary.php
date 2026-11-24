<?php
require_once 'db.php';
require_login();

$statement = $pdo->prepare(
    'SELECT id, recipient, max_budget, actual_cost, purchase_date
     FROM christmas_gifts
     WHERE user_id = :user_id
     ORDER BY recipient ASC'
);
$statement->execute(['user_id' => $_SESSION['user_id']]);
$gifts = $statement->fetchAll();

$totalsStatement = $pdo->prepare(
    'SELECT
        COALESCE(SUM(max_budget), 0) AS total_budget,
        COALESCE(SUM(actual_cost), 0) AS total_actual_cost,
        COALESCE(SUM(CASE WHEN actual_cost IS NULL OR purchase_date IS NULL
                          THEN 1 ELSE 0 END), 0) AS gifts_left
     FROM christmas_gifts
     WHERE user_id = :user_id'
);
$totalsStatement->execute(['user_id' => $_SESSION['user_id']]);
$totals = $totalsStatement->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gift Summary | Christmas Gift Organiser</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
    <header class="page-header">
        <div>
            <h1>Christmas Gift Organiser</h1>
            <p class="muted">Welcome, <?= escape($_SESSION['username'] ?? 'user') ?>.</p>
        </div>
        <form method="post" action="logout.php">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <button class="secondary" type="submit">Log out</button>
        </form>
    </header>

    <nav class="actions">
        <a class="button-link" href="add_gift.php">+ Add a new gift</a>
        <a class="button-link secondary-link" href="search_amend_gift.php">Search / amend a gift</a>
    </nav>

    <h2>Your gifts</h2>

    <?php if (!$gifts): ?>
        <p>You haven't added any gifts yet. Select “Add a new gift” to get started.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Max budget</th>
                    <th>Actual cost</th>
                    <th>Purchase date</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($gifts as $gift): ?>
                    <tr>
                        <td><?= escape($gift['recipient']) ?></td>
                        <td>£<?= number_format((float) $gift['max_budget'], 2) ?></td>
                        <td>
                            <?= $gift['actual_cost'] === null
                                ? 'Not purchased'
                                : '£' . number_format((float) $gift['actual_cost'], 2) ?>
                        </td>
                        <td><?= $gift['purchase_date'] === null
                                ? 'Not purchased'
                                : escape($gift['purchase_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <section class="totals">
        <h2>Totals</h2>
        <p><strong>Total maximum budget:</strong>
            £<?= number_format((float) $totals['total_budget'], 2) ?></p>
        <p><strong>Total actual cost:</strong>
            £<?= number_format((float) $totals['total_actual_cost'], 2) ?></p>
        <p><strong>Gifts left to buy:</strong>
            <?= (int) $totals['gifts_left'] ?></p>
    </section>
</main>
</body>
</html>
