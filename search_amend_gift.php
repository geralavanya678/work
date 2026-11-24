<?php
require_once __DIR__ . '/db.php';
require_login();

$search = trim((string)($_GET['recipient'] ?? $_POST['recipient_search'] ?? ''));
$selectedId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$gift = null;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_gift'])) {
    verify_csrf_token();
    $giftId = filter_var($_POST['gift_id'] ?? null, FILTER_VALIDATE_INT);
    $actualCostRaw = trim((string)($_POST['actual_cost'] ?? ''));
    $purchaseDate = trim((string)($_POST['purchase_date'] ?? ''));

    $actualCost = $actualCostRaw === '' ? null : filter_var($actualCostRaw, FILTER_VALIDATE_FLOAT);
    $validDate = $purchaseDate === '' || (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $purchaseDate);

    if (!$giftId || ($actualCostRaw !== '' && ($actualCost === false || $actualCost < 0)) || !$validDate) {
        $error = 'Enter a valid cost and purchase date.';
    } elseif (($actualCost === null) !== ($purchaseDate === '')) {
        $error = 'Enter both the actual cost and purchase date, or leave both blank if the gift has not been purchased.';
    } else {
        $statement = $pdo->prepare(
            'UPDATE christmas_gifts
             SET actual_cost = :actual_cost, purchase_date = :purchase_date
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            ':actual_cost' => $actualCost === null ? null : number_format((float)$actualCost, 2, '.', ''),
            ':purchase_date' => $purchaseDate === '' ? null : $purchaseDate,
            ':id' => $giftId,
            ':user_id' => $_SESSION['user_id'],
        ]);
        $success = 'Gift details updated.';
        $selectedId = $giftId;
    }
}

if ($selectedId) {
    $statement = $pdo->prepare('SELECT * FROM christmas_gifts WHERE id = :id AND user_id = :user_id');
    $statement->execute([':id' => $selectedId, ':user_id' => $_SESSION['user_id']]);
    $gift = $statement->fetch() ?: null;
} elseif ($search !== '') {
    $statement = $pdo->prepare(
        'SELECT * FROM christmas_gifts WHERE user_id = :user_id AND recipient LIKE :recipient ORDER BY recipient'
    );
    $statement->execute([':user_id' => $_SESSION['user_id'], ':recipient' => '%' . $search . '%']);
    $matches = $statement->fetchAll();
} else {
    $matches = [];
}

page_header('Search and amend gift');
?>
<section class="panel narrow">
    <h1>Search and amend a gift</h1>
    <form method="get">
        <label for="recipient">Recipient name</label>
        <input id="recipient" name="recipient" value="<?= e($search) ?>" placeholder="Enter all or part of a name">
        <button type="submit">Search</button>
    </form>
    <?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="notice success"><?= e($success) ?></p><?php endif; ?>

    <?php if ($gift): ?>
        <hr>
        <h2><?= e($gift['recipient']) ?></h2>
        <p class="muted">Maximum budget: £<?= number_format((float)$gift['max_budget'], 2) ?></p>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="gift_id" value="<?= (int)$gift['id'] ?>">
            <label for="actual_cost">Actual cost (£)</label>
            <input id="actual_cost" name="actual_cost" type="number" min="0" step="0.01" value="<?= e($gift['actual_cost']) ?>">
            <label for="purchase_date">Purchase date</label>
            <input id="purchase_date" name="purchase_date" type="date" value="<?= e($gift['purchase_date']) ?>">
            <button type="submit" name="update_gift" value="1">Update gift</button>
        </form>
    <?php elseif (isset($matches) && $search !== ''): ?>
        <hr><h2>Search results</h2>
        <?php if (!$matches): ?>
            <p>No matching recipients found.</p>
        <?php else: ?>
            <ul class="results">
                <?php foreach ($matches as $match): ?>
                    <li><a href="search_amend_gift.php?id=<?= (int)$match['id'] ?>"><?= e($match['recipient']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php elseif ($selectedId): ?>
        <p>No gift found for your account with that ID.</p>
    <?php endif; ?>
    <p class="form-note"><a href="summary.php">Back to gift summary</a></p>
</section>
<?php page_footer(); ?>
