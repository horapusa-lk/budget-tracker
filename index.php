<?php
/**
 * Dashboard - Main view showing balance, allowance, spending, and recent expenses.
 */
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$userId     = getCurrentUserId();
$allowance  = getCurrentAllowance($userId);
$totalSpent = getTotalSpent($userId);
$balance    = $allowance - $totalSpent;
$categories = getSpendingByCategory($userId);
$expenses   = getRecentExpenses($userId, 15);
$goals      = getSavingsGoals($userId);

require_once __DIR__ . '/includes/header.php';
?>

<div id="dashboard-stats">

    <!-- ─── Stat Cards ────────────────────────────────────────── -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

        <!-- Balance -->
        <div class="card fade-in">
            <div class="card-header">
                <span class="card-title">Balance</span>
                <span class="text-lg">💳</span>
            </div>
            <p id="stat-balance"
               class="card-value <?= $balance < 0 ? 'text-red-400' : 'text-green-400' ?>">
                <?= formatCurrency($balance) ?>
            </p>
            <p class="text-xs text-muted-foreground mt-1">Remaining this month</p>
        </div>

        <!-- Allowance -->
        <div class="card fade-in" style="animation-delay: 0.05s;">
            <div class="card-header">
                <span class="card-title">Allowance</span>
                <span class="text-lg">📥</span>
            </div>
            <p id="stat-allowance" class="card-value">
                <?= formatCurrency($allowance) ?>
            </p>
            <p class="text-xs text-muted-foreground mt-1"><?= date('F Y') ?></p>
        </div>

        <!-- Total Spent -->
        <div class="card fade-in" style="animation-delay: 0.1s;">
            <div class="card-header">
                <span class="card-title">Total Spent</span>
                <span class="text-lg">📤</span>
            </div>
            <p id="stat-spent" class="card-value text-orange-400">
                <?= formatCurrency($totalSpent) ?>
            </p>
            <p class="text-xs text-muted-foreground mt-1">
                <?= $allowance > 0 ? round(($totalSpent / $allowance) * 100) : 0 ?>% of allowance
            </p>
        </div>
    </div>

    <!-- ─── Spending Pie Chart ─────────────────────────────────── -->
    <?php
    // Build pie chart data — only categories with spending > 0
    $pieData = [];
    $pieColors = [
        'orange' => '#f97316', 'blue' => '#3b82f6', 'purple' => '#8b5cf6',
        'green' => '#22c55e', 'indigo' => '#818cf8', 'red' => '#ef4444',
        'teal' => '#14b8a6', 'cyan' => '#06b6d4', 'amber' => '#f59e0b',
        'slate' => '#94a3b8',
    ];
    foreach ($categories as $cat) {
        if ((float)$cat['spent'] > 0) {
            $pieData[] = [
                'name'  => $cat['icon'] . ' ' . $cat['name'],
                'spent' => (float) $cat['spent'],
                'color' => $pieColors[$cat['color']] ?? '#94a3b8',
            ];
        }
    }
    ?>
    <?php if ($totalSpent > 0): ?>
    <div class="card mb-8 fade-in" style="animation-delay: 0.13s;">
        <h2 class="text-sm font-semibold mb-4 tracking-tight">Spending Breakdown</h2>
        <div class="flex flex-col sm:flex-row items-center gap-6">
            <!-- SVG Pie Chart -->
            <div class="shrink-0" style="width: 200px; height: 200px;">
                <svg viewBox="0 0 42 42" class="w-full h-full" style="transform: rotate(-90deg);">
                    <?php
                    $offset = 0;
                    $radius = 15.91549430918954; // circumference = 100
                    foreach ($pieData as $slice):
                        $pct = ($slice['spent'] / $totalSpent) * 100;
                        $gap = count($pieData) > 1 ? 0.5 : 0;
                        $dash = max($pct - $gap, 0.1);
                    ?>
                    <circle cx="21" cy="21" r="<?= $radius ?>"
                            fill="transparent"
                            stroke="<?= $slice['color'] ?>"
                            stroke-width="6"
                            stroke-dasharray="<?= round($dash, 2) ?> <?= round(100 - $dash, 2) ?>"
                            stroke-dashoffset="<?= round(-$offset, 2) ?>"
                            class="pie-slice" />
                    <?php $offset += $pct; endforeach; ?>
                    <!-- Center text -->
                    <text x="21" y="20" text-anchor="middle" fill="hsl(var(--foreground))"
                          font-size="4" font-weight="700"
                          style="transform: rotate(90deg); transform-origin: 21px 21px;">
                        <?= round(($totalSpent / max($allowance, 1)) * 100) ?>%
                    </text>
                    <text x="21" y="24.5" text-anchor="middle" fill="hsl(var(--muted-foreground))"
                          font-size="2.2"
                          style="transform: rotate(90deg); transform-origin: 21px 21px;">
                        spent
                    </text>
                </svg>
            </div>
            <!-- Legend -->
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 w-full">
                <?php foreach ($pieData as $slice):
                    $pct = round(($slice['spent'] / $totalSpent) * 100, 1);
                ?>
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="shrink-0 w-2.5 h-2.5 rounded-full" style="background-color: <?= $slice['color'] ?>;"></span>
                        <span class="text-xs truncate"><?= htmlspecialchars($slice['name']) ?></span>
                    </div>
                    <span class="text-xs text-muted-foreground shrink-0"><?= $pct ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ─── Category Budgets ──────────────────────────────────── -->
    <div class="card mb-8 fade-in" style="animation-delay: 0.15s;">
        <h2 class="text-sm font-semibold mb-4 tracking-tight">Budget by Category</h2>
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
                    <span id="progress-label-<?= $cat['id'] ?>" class="text-xs text-muted-foreground">
                        <?= formatCurrency($cat['spent']) ?> / <?= formatCurrency($cat['budget_limit']) ?>
                    </span>
                </div>
                <div class="progress-track">
                    <div id="progress-<?= $cat['id'] ?>"
                         class="progress-fill <?= $progressClass ?>"
                         style="width: <?= round($percent, 1) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ─── Savings Goals ──────────────────────────────────────── -->
    <?php if (!empty($goals)): ?>
    <div class="card mb-8 fade-in" style="animation-delay: 0.18s;">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold tracking-tight">Savings Goals</h2>
            <a href="settings.php#goals" class="btn btn-ghost text-xs">Manage</a>
        </div>
        <div class="space-y-4">
            <?php foreach ($goals as $goal):
                $goalPercent = $goal['target_amount'] > 0
                    ? min(($goal['current_amount'] / $goal['target_amount']) * 100, 100)
                    : 0;
                $goalClass = $goalPercent >= 100 ? 'safe' : ($goalPercent >= 50 ? 'warning' : 'danger');
            ?>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-sm font-medium">🎯 <?= htmlspecialchars($goal['name']) ?></span>
                    <span class="text-xs text-muted-foreground">
                        <?= formatCurrency($goal['current_amount']) ?> / <?= formatCurrency($goal['target_amount']) ?>
                        <?php if ($goal['deadline']): ?>
                            · Due <?= date('M j', strtotime($goal['deadline'])) ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill <?= $goalClass ?>"
                         style="width: <?= round($goalPercent, 1) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ─── Recent Expenses Table ─────────────────────────────── -->
    <div class="card fade-in" style="animation-delay: 0.2s;">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h2 class="text-sm font-semibold tracking-tight">Recent Expenses</h2>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="api/export_csv.php" class="btn btn-ghost text-xs">📥 Export CSV</a>
                <a href="add-transaction.php" class="btn btn-ghost text-xs">+ Add New</a>
            </div>
        </div>

        <?php if (empty($expenses)): ?>
            <div class="text-center py-12">
                <p class="text-muted-foreground text-sm">No expenses recorded yet.</p>
                <a href="add-transaction.php" class="btn btn-primary mt-4 text-sm">Add Your First Expense</a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left">
                            <th class="pb-3 font-medium text-muted-foreground">Date</th>
                            <th class="pb-3 font-medium text-muted-foreground">Category</th>
                            <th class="pb-3 font-medium text-muted-foreground">Description</th>
                            <th class="pb-3 font-medium text-muted-foreground text-right">Amount</th>
                            <th class="pb-3 font-medium text-muted-foreground text-right w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php foreach ($expenses as $exp): ?>
                        <tr class="expense-row" data-expense-id="<?= $exp['id'] ?>">
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
                            <td class="py-3 text-right">
                                <button onclick="deleteExpense(<?= $exp['id'] ?>)"
                                        class="btn btn-ghost text-xs text-red-400 hover:text-red-300 px-2 py-1"
                                        title="Delete">
                                    ✕
                                </button>
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
