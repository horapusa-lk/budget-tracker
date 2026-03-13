<?php
/**
 * Settings - Manage monthly allowance and savings goals.
 */
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$userId     = getCurrentUserId();
$allowance  = getCurrentAllowance($userId);
$goals      = getSavingsGoals($userId);
$catLimits  = getUserCategoryLimits($userId);

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-2xl mx-auto">

    <div class="mb-6 fade-in">
        <h2 class="text-2xl font-bold tracking-tight">Settings</h2>
        <p class="text-sm text-muted-foreground mt-1">Manage your allowance and savings goals</p>
    </div>

    <!-- ─── Monthly Allowance ──────────────────────────────── -->
    <div class="card mb-6 fade-in" style="animation-delay: 0.05s;">
        <h3 class="text-sm font-semibold mb-4 tracking-tight">Monthly Allowance</h3>
        <form id="allowance-form" class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="flex-1">
                <label for="allowance_amount" class="label">Amount (Rs.) for <?= date('F Y') ?></label>
                <input type="number" name="amount" id="allowance_amount" class="input"
                       value="<?= $allowance ?>" step="0.01" min="0" max="999999.99" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>

    <!-- ─── Category Budget Limits ────────────────────────── -->
    <div class="card mb-6 fade-in" style="animation-delay: 0.08s;">
        <h3 class="text-sm font-semibold mb-4 tracking-tight">Category Budget Limits</h3>
        <form id="limits-form" class="space-y-3">
            <?php foreach ($catLimits as $cat): ?>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 w-28 sm:w-40 shrink-0">
                    <span class="cat-dot <?= htmlspecialchars($cat['color']) ?>"></span>
                    <span class="text-sm"><?= htmlspecialchars($cat['icon'] . ' ' . $cat['name']) ?></span>
                </div>
                <input type="number" name="limit_<?= $cat['id'] ?>" class="input text-sm"
                       value="<?= $cat['budget_limit'] ?>" step="0.01" min="0" max="999999.99"
                       data-cat-id="<?= $cat['id'] ?>" style="max-width: 160px;">
            </div>
            <?php endforeach; ?>
            <div class="pt-2">
                <button type="submit" class="btn btn-primary text-sm">Save Limits</button>
            </div>
        </form>
    </div>

    <!-- ─── Savings Goals ──────────────────────────────────── -->
    <div class="card fade-in" id="goals" style="animation-delay: 0.1s;">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold tracking-tight">Savings Goals</h3>
            <button onclick="toggleGoalForm()" class="btn btn-ghost text-xs" id="toggle-goal-btn">+ New Goal</button>
        </div>

        <!-- New Goal Form (hidden by default) -->
        <div id="goal-form-wrapper" class="hidden mb-6">
            <form id="goal-form" class="space-y-3 p-4 border border-border rounded-lg">
                <div>
                    <label for="goal_name" class="label">Goal Name</label>
                    <input type="text" name="name" id="goal_name" class="input" required maxlength="100"
                           placeholder="e.g. New Textbook">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="goal_target" class="label">Target (Rs.)</label>
                        <input type="number" name="target_amount" id="goal_target" class="input"
                               step="0.01" min="1" max="999999.99" required>
                    </div>
                    <div>
                        <label for="goal_deadline" class="label">Deadline <span class="text-muted-foreground font-normal">(optional)</span></label>
                        <input type="date" name="deadline" id="goal_deadline" class="input">
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary text-sm">Save Goal</button>
                    <button type="button" onclick="toggleGoalForm()" class="btn btn-ghost text-sm">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Goals List -->
        <div id="goals-list" class="space-y-4">
            <?php if (empty($goals)): ?>
                <p class="text-muted-foreground text-sm text-center py-4" id="no-goals-msg">No savings goals yet. Create one to start saving!</p>
            <?php else: ?>
                <?php foreach ($goals as $goal):
                    $goalPercent = $goal['target_amount'] > 0
                        ? min(($goal['current_amount'] / $goal['target_amount']) * 100, 100)
                        : 0;
                    $goalClass = $goalPercent >= 100 ? 'safe' : ($goalPercent >= 50 ? 'warning' : 'danger');
                ?>
                <div class="goal-item p-3 border border-border rounded-lg" data-goal-id="<?= $goal['id'] ?>">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
                        <span class="text-sm font-medium">🎯 <?= htmlspecialchars($goal['name']) ?></span>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-muted-foreground">
                                <?= formatCurrency($goal['current_amount']) ?> / <?= formatCurrency($goal['target_amount']) ?>
                                <?php if ($goal['deadline']): ?>
                                    · Due <?= date('M j, Y', strtotime($goal['deadline'])) ?>
                                <?php endif; ?>
                            </span>
                            <button onclick="deleteGoal(<?= $goal['id'] ?>)"
                                    class="text-red-400 hover:text-red-300 text-xs px-1" title="Delete">✕</button>
                        </div>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill <?= $goalClass ?>"
                             style="width: <?= round($goalPercent, 1) ?>%"></div>
                    </div>
                    <div class="mt-2 flex items-center gap-2">
                        <input type="number" class="input text-xs" style="max-width: 120px; padding: 0.25rem 0.5rem;"
                               placeholder="Add amount" step="0.01" min="0.01" id="add-goal-<?= $goal['id'] ?>">
                        <button onclick="addToGoal(<?= $goal['id'] ?>)"
                                class="btn btn-ghost text-xs py-1 px-2">+ Add</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleGoalForm() {
    const wrapper = document.getElementById('goal-form-wrapper');
    wrapper.classList.toggle('hidden');
}

// Update allowance
document.getElementById('allowance-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const amount = parseFloat(e.target.amount.value);
    try {
        const res = await fetch('api/update_allowance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ amount }),
        });
        const data = await res.json();
        if (!res.ok) { alert(data.error || 'Failed'); return; }
        showToast('Allowance updated!');
    } catch { alert('Network error.'); }
});

// Save category limits
document.getElementById('limits-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const inputs = e.target.querySelectorAll('input[data-cat-id]');
    const limits = [];
    inputs.forEach(input => {
        limits.push({ category_id: parseInt(input.dataset.catId), budget_limit: parseFloat(input.value) || 0 });
    });
    try {
        const res = await fetch('api/update_category_limits.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ limits }),
        });
        const data = await res.json();
        if (!res.ok) { alert(data.error || 'Failed'); return; }
        showToast('Category limits updated!');
    } catch { alert('Network error.'); }
});

// Add goal
document.getElementById('goal-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = {
        name: e.target.name.value.trim(),
        target_amount: parseFloat(e.target.target_amount.value),
        deadline: e.target.deadline.value || null,
    };
    try {
        const res = await fetch('api/savings_goals.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) { alert(data.error || 'Failed'); return; }
        showToast('Goal created!');
        location.reload();
    } catch { alert('Network error.'); }
});

// Add money to goal
async function addToGoal(goalId) {
    const input = document.getElementById('add-goal-' + goalId);
    const amount = parseFloat(input.value);
    if (!amount || amount <= 0) { alert('Enter a valid amount.'); return; }
    try {
        const res = await fetch('api/savings_goals.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: goalId, add_amount: amount }),
        });
        const data = await res.json();
        if (!res.ok) { alert(data.error || 'Failed'); return; }
        showToast('Saved Rs. ' + amount.toFixed(2) + '!');
        location.reload();
    } catch { alert('Network error.'); }
}

// Delete goal
async function deleteGoal(goalId) {
    if (!confirm('Delete this savings goal?')) return;
    try {
        const res = await fetch('api/savings_goals.php?id=' + goalId, { method: 'DELETE' });
        const data = await res.json();
        if (!res.ok) { alert(data.error || 'Failed'); return; }
        showToast('Goal deleted.');
        const el = document.querySelector('[data-goal-id="' + goalId + '"]');
        if (el) el.remove();
    } catch { alert('Network error.'); }
}

function showToast(message, type = 'success') {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
