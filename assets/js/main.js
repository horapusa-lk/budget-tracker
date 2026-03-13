/**
 * Student Budget Tracker - Main JavaScript
 * Handles form submissions, data fetching, and UI updates.
 */

// ─── Toast Notification ─────────────────────────────────────────

function showToast(message, type = 'success') {
    // Remove any existing toast
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    // Trigger show animation
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ─── Currency Formatting ────────────────────────────────────────

function formatCurrency(amount) {
    return 'Rs. ' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// ─── Progress Bar Color ─────────────────────────────────────────

function getProgressClass(percent) {
    if (percent >= 90) return 'danger';
    if (percent >= 70) return 'warning';
    return 'safe';
}

// ─── Expense Form Handler ───────────────────────────────────────

function initExpenseForm() {
    const form = document.getElementById('expense-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const data = {
            category_id: parseInt(form.category_id.value, 10),
            amount: parseFloat(form.amount.value),
            description: form.description.value.trim(),
            expense_date: form.expense_date.value,
        };

        try {
            const res = await fetch('api/add_expense.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });

            const result = await res.json();

            if (!res.ok) {
                showToast(result.error || 'Failed to add expense.', 'error');
                return;
            }

            showToast('Expense added successfully!');
            form.reset();
            // Set date back to today
            form.expense_date.value = new Date().toISOString().split('T')[0];
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });

    // Default date to today
    const dateInput = form.querySelector('[name="expense_date"]');
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
}

// ─── Delete Expense ─────────────────────────────────────────────

async function deleteExpense(id) {
    if (!confirm('Delete this expense?')) return;

    try {
        const res = await fetch(`api/delete_expense.php?id=${id}`, {
            method: 'DELETE',
        });

        const result = await res.json();

        if (!res.ok) {
            showToast(result.error || 'Failed to delete.', 'error');
            return;
        }

        showToast('Expense deleted.');

        // Remove row from DOM
        const row = document.querySelector(`[data-expense-id="${id}"]`);
        if (row) {
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            row.style.transition = 'opacity 0.3s, transform 0.3s';
            setTimeout(() => {
                row.remove();
                // Refresh dashboard stats if on index page
                if (typeof refreshDashboard === 'function') {
                    refreshDashboard();
                }
            }, 300);
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
    }
}

// ─── Dashboard Refresh ──────────────────────────────────────────

async function refreshDashboard() {
    const statsContainer = document.getElementById('dashboard-stats');
    if (!statsContainer) return;

    try {
        const res = await fetch('api/get_expenses.php');
        const data = await res.json();

        // Update stat cards
        const balanceEl = document.getElementById('stat-balance');
        const allowanceEl = document.getElementById('stat-allowance');
        const spentEl = document.getElementById('stat-spent');

        if (balanceEl) balanceEl.textContent = formatCurrency(data.balance);
        if (allowanceEl) allowanceEl.textContent = formatCurrency(data.allowance);
        if (spentEl) spentEl.textContent = formatCurrency(data.totalSpent);

        // Update category progress bars
        if (data.categories) {
            data.categories.forEach((cat) => {
                const bar = document.getElementById(`progress-${cat.id}`);
                const label = document.getElementById(`progress-label-${cat.id}`);
                if (bar) {
                    const percent = cat.budget_limit > 0
                        ? Math.min((cat.spent / cat.budget_limit) * 100, 100)
                        : 0;
                    bar.style.width = percent + '%';
                    bar.className = `progress-fill ${getProgressClass(percent)}`;
                }
                if (label) {
                    label.textContent = `${formatCurrency(cat.spent)} / ${formatCurrency(cat.budget_limit)}`;
                }
            });
        }

        // Update balance card color
        if (balanceEl) {
            balanceEl.className = 'card-value ' + (data.balance < 0 ? 'text-red-400' : 'text-green-400');
        }
    } catch (err) {
        // Silently fail on background refresh
    }
}

// ─── Theme Toggle ──────────────────────────────────────────────

function toggleTheme() {
    document.documentElement.classList.add('theme-transition');
    document.documentElement.classList.toggle('dark');
    var isDark = document.documentElement.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    setTimeout(function() {
        document.documentElement.classList.remove('theme-transition');
    }, 300);
}

// ─── Initialize ─────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    initExpenseForm();
});
