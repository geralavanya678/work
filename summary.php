<?php
require_once __DIR__ . '/db.php';
require_login();

$statement = $pdo->prepare(
    'SELECT id, recipient, max_budget, actual_cost, purchase_date
     FROM christmas_gifts WHERE user_id = :user_id ORDER BY recipient ASC'
);
$statement->execute([':user_id' => $_SESSION['user_id']]);
$gifts = $statement->fetchAll();

$totalBudget = 0.0;
$totalActual = 0.0;
$remaining = 0;
foreach ($gifts as $gift) {
    $totalBudget += (float)$gift['max_budget'];
    if ($gift['actual_cost'] === null) {
        $remaining++;
    } else {
        $totalActual += (float)$gift['actual_cost'];
    }
}

page_header('Gift summary');
?>
<section class="page-heading">
    <div><p class="eyebrow">Your Christmas plan</p><h1>Gift summary</h1></div>
    <a class="button-link" href="add_gift.php">＋ Add a gift</a>
</section>
<?php if (isset($_GET['added'])): ?><p class="notice success">Gift recipient added.</p><?php endif; ?>
<section class="panel">
    <?php if (!$gifts): ?>
        <p>You haven't added any recipients yet. <a href="add_gift.php">Add your first gift</a>.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Recipient</th><th>Max Budget</th><th>Actual Cost</th><th>Purchase Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($gifts as $gift): ?>
                    <tr>
                        <td><?= e($gift['recipient']) ?></td>
                        <td>£<?= number_format((float)$gift['max_budget'], 2) ?></td>
                        <td><?= $gift['actual_cost'] === null ? '<span class="pending">Not purchased</span>' : '£' . number_format((float)$gift['actual_cost'], 2) ?></td>
                        <td><?= $gift['purchase_date'] === null ? '—' : e($gift['purchase_date']) ?></td>
                        <td><a href="search_amend_gift.php?id=<?= (int)$gift['id'] ?>">Amend</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<section class="stats-grid">
    <article class="stat-card"><span>Total maximum budget</span><strong>£<?= number_format($totalBudget, 2) ?></strong></article>
    <article class="stat-card"><span>Total actual cost</span><strong>£<?= number_format($totalActual, 2) ?></strong></article>
    <article class="stat-card"><span>Gifts left to buy</span><strong><?= $remaining ?></strong></article>
</section>
<p class="muted">Only gifts belonging to your account are shown here.</p>
<?php page_footer(); ?>
