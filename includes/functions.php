<?php
/**
 * Helper functions for currency formatting, data sanitization, and queries.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Format a number as currency (Sri Lankan Rupee).
 */
function formatCurrency(float $amount): string
{
    return 'Rs. ' . number_format($amount, 2);
}

/**
 * Sanitize user input string.
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate a date string (Y-m-d format).
 */
function isValidDate(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Get all categories from the database.
 */
function getCategories(): array
{
    $pdo = getDBConnection();
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
    return $stmt->fetchAll();
}

/**
 * Get the current month's allowance for a user.
 */
function getCurrentAllowance(int $userId, ?int $month = null, ?int $year = null): float
{
    $pdo = getDBConnection();
    $month = $month ?? (int) date('n');
    $year  = $year ?? (int) date('Y');
    $stmt  = $pdo->prepare(
        'SELECT amount FROM allowances WHERE user_id = :user_id AND month = :month AND year = :year'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':month'   => $month,
        ':year'    => $year,
    ]);
    $row = $stmt->fetch();
    return $row ? (float) $row['amount'] : 0.0;
}

/**
 * Get total spent for a user in a given month.
 */
function getTotalSpent(int $userId, ?int $month = null, ?int $year = null): float
{
    $pdo   = getDBConnection();
    $month = $month ?? (int) date('n');
    $year  = $year ?? (int) date('Y');
    $stmt  = $pdo->prepare(
        'SELECT COALESCE(SUM(amount), 0) AS total
         FROM expenses
         WHERE user_id = :user_id AND MONTH(expense_date) = :month AND YEAR(expense_date) = :year'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':month'   => $month,
        ':year'    => $year,
    ]);
    return (float) $stmt->fetchColumn();
}

/**
 * Get spending per category for a user in a given month.
 */
function getSpendingByCategory(int $userId, ?int $month = null, ?int $year = null): array
{
    $pdo   = getDBConnection();
    $month = $month ?? (int) date('n');
    $year  = $year ?? (int) date('Y');
    $stmt  = $pdo->prepare(
        'SELECT c.id, c.name, COALESCE(ucl.budget_limit, c.budget_limit) AS budget_limit,
                c.icon, c.color,
                COALESCE(SUM(e.amount), 0) AS spent
         FROM categories c
         LEFT JOIN user_category_limits ucl ON ucl.category_id = c.id AND ucl.user_id = :user_id
         LEFT JOIN expenses e ON e.category_id = c.id
              AND e.user_id = :user_id2
              AND MONTH(e.expense_date) = :month
              AND YEAR(e.expense_date) = :year
         GROUP BY c.id
         ORDER BY c.name'
    );
    $stmt->execute([
        ':user_id'  => $userId,
        ':user_id2' => $userId,
        ':month'    => $month,
        ':year'     => $year,
    ]);
    return $stmt->fetchAll();
}

/**
 * Get user's category limits (for settings page).
 */
function getUserCategoryLimits(int $userId): array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT c.id, c.name, c.icon, c.color,
                COALESCE(ucl.budget_limit, c.budget_limit) AS budget_limit
         FROM categories c
         LEFT JOIN user_category_limits ucl ON ucl.category_id = c.id AND ucl.user_id = :user_id
         ORDER BY c.name'
    );
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Get recent expenses with category info for a user.
 */
function getRecentExpenses(int $userId, int $limit = 20): array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT e.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color
         FROM expenses e
         JOIN categories c ON c.id = e.category_id
         WHERE e.user_id = :user_id
         ORDER BY e.expense_date DESC, e.created_at DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get savings goals for a user.
 */
function getSavingsGoals(int $userId): array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT * FROM savings_goals WHERE user_id = :user_id ORDER BY deadline ASC, created_at DESC'
    );
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Get distinct months that have expenses for a user (for history dropdown).
 */
function getExpenseMonths(int $userId): array
{
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT DISTINCT MONTH(expense_date) AS m, YEAR(expense_date) AS y
         FROM expenses
         WHERE user_id = :user_id
         ORDER BY y DESC, m DESC'
    );
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Send a JSON response and exit.
 */
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
