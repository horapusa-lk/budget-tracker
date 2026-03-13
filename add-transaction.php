<?php
/**
 * Add Transaction - Form page for recording new expenses.
 */
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$userId     = getCurrentUserId();
$categories = getCategories();
$allowance  = getCurrentAllowance($userId);
$totalSpent = getTotalSpent($userId);
$balance    = $allowance - $totalSpent;

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-lg mx-auto">

    <!-- Page Title -->
    <div class="mb-6 fade-in">
        <h2 class="text-2xl font-bold tracking-tight">Add Expense</h2>
        <p class="text-sm text-muted-foreground mt-1">
            Current balance: <span class="font-medium <?= $balance < 0 ? 'text-red-400' : 'text-green-400' ?>"><?= formatCurrency($balance) ?></span>
        </p>
    </div>

    <!-- Expense Form -->
    <div class="card fade-in" style="animation-delay: 0.05s;">
        <form id="expense-form" class="space-y-5">

            <!-- Category -->
            <div>
                <label for="category_id" class="label">Category</label>
                <select name="category_id" id="category_id" class="input" required>
                    <option value="" disabled selected>Select a category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['icon'] . ' ' . $cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Amount -->
            <div>
                <label for="amount" class="label">Amount (Rs.)</label>
                <input type="number"
                       name="amount"
                       id="amount"
                       class="input"
                       placeholder="0.00"
                       step="0.01"
                       min="0.01"
                       max="99999.99"
                       required>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="label">Description <span class="text-muted-foreground font-normal">(optional)</span></label>
                <input type="text"
                       name="description"
                       id="description"
                       class="input"
                       placeholder="e.g. Lunch at cafeteria"
                       maxlength="255">
            </div>

            <!-- Date -->
            <div>
                <label for="expense_date" class="label">Date</label>
                <input type="date"
                       name="expense_date"
                       id="expense_date"
                       class="input"
                       required>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn btn-primary flex-1">
                    Save Expense
                </button>
                <a href="index.php" class="btn btn-ghost">Cancel</a>
            </div>

        </form>
    </div>

    <!-- Quick Category Summary -->
    <div class="card mt-6 fade-in" style="animation-delay: 0.1s;">
        <h3 class="text-sm font-semibold mb-3 tracking-tight">Category Limits</h3>
        <div class="space-y-2">
            <?php
            $categorySpending = getSpendingByCategory($userId);
            foreach ($categorySpending as $cat):
                $percent = $cat['budget_limit'] > 0
                    ? min(($cat['spent'] / $cat['budget_limit']) * 100, 100)
                    : 0;
                $progressClass = 'safe';
                if ($percent >= 90) $progressClass = 'danger';
                elseif ($percent >= 70) $progressClass = 'warning';
            ?>
            <div class="flex items-center justify-between text-xs">
                <span class="flex items-center gap-1.5">
                    <span class="cat-dot <?= htmlspecialchars($cat['color']) ?>"></span>
                    <?= htmlspecialchars($cat['icon'] . ' ' . $cat['name']) ?>
                </span>
                <span class="text-muted-foreground">
                    <?= formatCurrency($cat['spent']) ?> / <?= formatCurrency($cat['budget_limit']) ?>
                    (<?= round($percent) ?>%)
                </span>
            </div>
            <div class="progress-track" style="height: 4px;">
                <div class="progress-fill <?= $progressClass ?>"
                     style="width: <?= round($percent, 1) ?>%"></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
