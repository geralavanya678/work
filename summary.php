<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_login();

$statement = $pdo->prepare(
    'SELECT id, recipient, max_budget, actual_cost, purchase_date
     FROM christmas_gifts WHERE user_id = :user_id ORDER BY recipient ASC'
);
$statement->execute(['user_id' => $_SESSION['user_id']]);
$gifts = $statement->fetchAll();

$totalBudget = 0.0;
$totalActual = 0.0;
$leftToBuy = 0;
foreach ($gifts as $gift) {
    $totalBudget += (float)$gift['max_budget'];
    if ($gift['actual_cost'] !== null) {
        $totalActual += (float)$gift['actual_cost'];
    }
    if ($gift['purchase_date'] === null) {
        $leftToBuy++;
    }
}

page_header('Gift summary');
?>
<div class="page-heading">
    <div><p class="eyebrow">Your Christmas plan</p><h1>Gift summary</h1>
    <p class="muted">Hello, <?= e($_SESSION['username'] ?? 'there') ?>. Here is your current list.</p></div>
    <a class="button" href="add_gift.php">+ Add a gift</a>
</div>
<?php if (isset($_GET['added'])): ?><div class="alert alert-success">Gift recipient added successfully.</div><?php endif; ?>
<div class="table-wrap">
<table>
    <thead><tr><th>Recipient Name</th><th>Max Budget</th><th>Actual Cost</th><th>Purchase Date</th></tr></thead>
    <tbody>
    <?php if (!$gifts): ?>
        <tr><td colspan="4" class="empty">You haven't added any gift recipients yet.</td></tr>
    <?php else: foreach ($gifts as $gift): ?>
        <tr>
            <td><?= e($gift['recipient']) ?></td>
            <td>£<?= number_format((float)$gift['max_budget'], 2) ?></td>
            <td><?= $gift['actual_cost'] === null ? 'Not purchased' : '£' . number_format((float)$gift['actual_cost'], 2) ?></td>
            <td><?= $gift['purchase_date'] === null ? 'Not purchased' : e($gift['purchase_date']) ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>
<section class="stats">
    <h2>Totals</h2>
    <div class="stat-grid">
        <article class="stat"><span>Total Max Budget</span><strong>£<?= number_format($totalBudget, 2) ?></strong></article>
        <article class="stat"><span>Total Actual Cost</span><strong>£<?= number_format($totalActual, 2) ?></strong></article>
        <article class="stat"><span>Gifts Left to Purchase</span><strong><?= $leftToBuy ?></strong></article>
    </div>
</section>
<p class="center"><a href="search_amend_gift.php">Search for and amend an existing gift</a></p>
<?php page_footer(); ?>
