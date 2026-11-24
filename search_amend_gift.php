<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_login();

$error = '';
$success = '';
$searchName = trim($_GET['recipient'] ?? '');
$gift = null;

if ($searchName !== '') {
    $search = $pdo->prepare('SELECT id, recipient, max_budget, actual_cost, purchase_date FROM christmas_gifts WHERE user_id = :user_id AND recipient = :recipient LIMIT 1');
    $search->execute(['user_id' => $_SESSION['user_id'], 'recipient' => $searchName]);
    $gift = $search->fetch();
    if (!$gift) {
        $error = 'No matching recipient was found in your gift list.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $giftId = filter_input(INPUT_POST, 'gift_id', FILTER_VALIDATE_INT);
    $actualCost = trim($_POST['actual_cost'] ?? '');
    $purchaseDate = trim($_POST['purchase_date'] ?? '');

    if (!$giftId) {
        $error = 'Invalid gift selected.';
    } elseif (!is_numeric($actualCost) || (float)$actualCost < 0 || (float)$actualCost > 99999999.99) {
        $error = 'Enter a valid non-negative actual cost.';
    } elseif ($purchaseDate === '' || !DateTime::createFromFormat('Y-m-d', $purchaseDate) || DateTime::createFromFormat('Y-m-d', $purchaseDate)->format('Y-m-d') !== $purchaseDate) {
        $error = 'Enter a valid purchase date.';
    } else {
        // The user_id condition prevents changing another user's record.
        $update = $pdo->prepare('UPDATE christmas_gifts SET actual_cost = :actual_cost, purchase_date = :purchase_date WHERE id = :id AND user_id = :user_id');
        $update->execute([
            'actual_cost' => number_format((float)$actualCost, 2, '.', ''),
            'purchase_date' => $purchaseDate,
            'id' => $giftId,
            'user_id' => $_SESSION['user_id']
        ]);
        if ($update->rowCount() >= 0) {
            header('Location: search_amend_gift.php?recipient=' . urlencode($_POST['recipient_name'] ?? ''));
            exit;
        }
        $error = 'The gift could not be updated.';
    }
}

page_header('Search and amend a gift');
?>
<section class="form-card">
    <h1>Search and amend a gift</h1>
    <form method="get" action="search_amend_gift.php">
        <label for="recipient">Recipient name</label>
        <input id="recipient" name="recipient" required value="<?= e($searchName) ?>" placeholder="Enter recipient name">
        <button type="submit">Search</button>
    </form>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($gift): ?>
        <div class="result-panel">
            <h2>Recipient found: <?= e($gift['recipient']) ?></h2>
            <p class="muted">Maximum budget: £<?= number_format((float)$gift['max_budget'], 2) ?></p>
            <form method="post" action="search_amend_gift.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="gift_id" value="<?= (int)$gift['id'] ?>">
                <input type="hidden" name="recipient_name" value="<?= e($gift['recipient']) ?>">
                <label for="actual_cost">Actual cost (£)</label>
                <input id="actual_cost" type="number" name="actual_cost" min="0" max="99999999.99" step="0.01" required value="<?= e($gift['actual_cost'] === null ? '' : (string)$gift['actual_cost']) ?>">
                <label for="purchase_date">Purchase date</label>
                <input id="purchase_date" type="date" name="purchase_date" required value="<?= e($gift['purchase_date'] ?? '') ?>">
                <button type="submit">Update gift</button>
            </form>
        </div>
    <?php endif; ?>
    <p class="form-foot"><a href="summary.php">Back to gift summary</a></p>
</section>
<?php page_footer(); ?>
