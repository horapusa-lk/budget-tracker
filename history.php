<?php
/**
 * History - View spending for past months.
 */
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$months = getExpenseMonths($userId);

// Default to current month or selected
$selMonth = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
$selYear  = isset($_GET['year'])  ? (int) $_GET['year']  : (int) date('Y');

$allowance  = getCurrentAllowance($userId, $selMonth, $selYear);
$totalSpent = getTotalSpent($userId, $selMonth, $selYear);
$balance    = $allowance - $totalSpent;
$categories = getSpendingByCategory($userId, $selMonth, $selYear);

// Get expenses for selected month
$pdo = getDBConnection();
$stmt = $pdo->prepare(
    'SELECT e.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color
     FROM expenses e
     JOIN categories c ON c.id = e.category_id
     WHERE e.user_id = :user_id AND MONTH(e.expense_date) = :month AND YEAR(e.expense_date) = :year
     ORDER BY e.expense_date DESC, e.created_at DESC'
);
$stmt->execute([':user_id' => $userId, ':month' => $selMonth, ':year' => $selYear]);
$expenses = $stmt->fetchAll();

$monthName = date('F Y', mktime(0, 0, 0, $selMonth, 1, $selYear));

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 fade-in">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Spending History</h2>
            <p class="text-sm text-muted-foreground mt-1">Review your past spending</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="month" class="input" style="width: auto;" onchange="this.form.submit()">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m === $selMonth ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="year" class="input" style="width: auto;" onchange="this.form.submit()">
                <?php
                $currentYear = (int) date('Y');
                for ($y = $currentYear; $y >= $currentYear - 3; $y--):
                ?>
                    <option value="<?= $y ?>" <?= $y === $selYear ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8 fade-in" style="animation-delay: 0.05s;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Allowance</span>
                <span class="text-lg">📥</span>
            </div>
            <p class="card-value"><?= formatCurrency($allowance) ?></p>
            <p class="text-xs text-muted-foreground mt-1"><?= $monthName ?></p>
        </div>
        <div class="card">
            <div class="card-header">
                <span class="card-title">Total Spent</span>
                <span class="text-lg">📤</span>
            </div>
            <p class="card-value text-orange-400"><?= formatCurrency($totalSpent) ?></p>
        </div>
        <div class="card">
            <div class="card-header">
                <span class="card-title">Balance</span>
                <span class="text-lg">💳</span>
            </div>
            <p class="card-value <?= $balance < 0 ? 'text-red-400' : 'text-green-400' ?>"><?= formatCurrency($balance) ?></p>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="card mb-8 fade-in" style="animation-delay: 0.1s;">
        <h3 class="text-sm font-semibold mb-4 tracking-tight">Category Breakdown — <?= $monthName ?></h3>
        <div class="space-y-4">
            <?php foreach ($categories as $cat):
                $percent = $cat['budget_limit'] > 0
                    ? min(($cat['spent'] / $cat['budget_limit']) * 100, 100)
                    : 0;
                $progressClass = 'safe';
                if ($percent >= 90) $progressClass = 'danger';
                elseif ($percent >= 70) $progressClass = 'warning';
            ?>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="cat-dot <?= htmlspecialchars($cat['color']) ?>"></span>
                        <span class="text-sm font-medium">
                            <?= htmlspecialchars($cat['icon'] . ' ' . $cat['name']) ?>
                        </span>
                    </div>
                    <span class="text-xs text-muted-foreground">
                        <?= formatCurrency($cat['spent']) ?> / <?= formatCurrency($cat['budget_limit']) ?>
                        (<?= round($percent) ?>%)
                    </span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill <?= $progressClass ?>"
                         style="width: <?= round($percent, 1) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card fade-in" style="animation-delay: 0.15s;">
        <h3 class="text-sm font-semibold mb-4 tracking-tight">All Expenses — <?= $monthName ?></h3>

        <?php if (empty($expenses)): ?>
            <p class="text-muted-foreground text-sm text-center py-8">No expenses recorded for this month.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left">
                            <th class="pb-3 font-medium text-muted-foreground">Date</th>
                            <th class="pb-3 font-medium text-muted-foreground">Category</th>
                            <th class="pb-3 font-medium text-muted-foreground">Description</th>
                            <th class="pb-3 font-medium text-muted-foreground text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php foreach ($expenses as $exp): ?>
                        <tr class="expense-row">
                            <td class="py-3 text-muted-foreground">
                                <?= date('M j', strtotime($exp['expense_date'])) ?>
                            </td>
                            <td class="py-3">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="cat-dot <?= htmlspecialchars($exp['category_color']) ?>"></span>
                                    <?= htmlspecialchars($exp['category_icon'] . ' ' . $exp['category_name']) ?>
                                </span>
                            </td>
                            <td class="py-3 text-muted-foreground max-w-[200px] truncate">
                                <?= htmlspecialchars($exp['description']) ?: '—' ?>
                            </td>
                            <td class="py-3 text-right font-medium">
                                <?= formatCurrency($exp['amount']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
