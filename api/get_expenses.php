<?php
/**
 * API: Fetch expenses with optional filters.
 * GET /api/get_expenses.php?month=3&year=2026
 */

require_once __DIR__ . '/../includes/functions.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

$userId = getCurrentUserId();
$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
$year  = isset($_GET['year'])  ? (int) $_GET['year']  : (int) date('Y');

$pdo = getDBConnection();

// Fetch expenses for the given month/year
$stmt = $pdo->prepare(
    'SELECT e.id, e.amount, e.description, e.expense_date,
            c.name AS category_name, c.icon AS category_icon, c.color AS category_color
     FROM expenses e
     JOIN categories c ON c.id = e.category_id
     WHERE e.user_id = :user_id AND MONTH(e.expense_date) = :month AND YEAR(e.expense_date) = :year
     ORDER BY e.expense_date DESC, e.created_at DESC'
);
$stmt->execute([':user_id' => $userId, ':month' => $month, ':year' => $year]);
$expenses = $stmt->fetchAll();

// Fetch summary data
$allowance = getCurrentAllowance($userId, $month, $year);
$totalSpent = getTotalSpent($userId, $month, $year);
$categorySpending = getSpendingByCategory($userId, $month, $year);

jsonResponse([
    'expenses'   => $expenses,
    'allowance'  => $allowance,
    'totalSpent' => $totalSpent,
    'balance'    => $allowance - $totalSpent,
    'categories' => $categorySpending,
]);
