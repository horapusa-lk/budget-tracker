<?php
/**
 * Export expenses as CSV.
 * GET /api/export_csv.php?month=3&year=2026
 */
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$month  = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
$year   = isset($_GET['year'])  ? (int) $_GET['year']  : (int) date('Y');

$pdo  = getDBConnection();
$stmt = $pdo->prepare(
    'SELECT e.expense_date, c.name AS category, e.description, e.amount
     FROM expenses e
     JOIN categories c ON c.id = e.category_id
     WHERE e.user_id = :user_id AND MONTH(e.expense_date) = :month AND YEAR(e.expense_date) = :year
     ORDER BY e.expense_date ASC'
);
$stmt->execute([':user_id' => $userId, ':month' => $month, ':year' => $year]);
$expenses = $stmt->fetchAll();

$monthName = date('F_Y', mktime(0, 0, 0, $month, 1, $year));
$filename  = "expenses_{$monthName}.csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Category', 'Description', 'Amount (Rs.)']);

$total = 0;
foreach ($expenses as $exp) {
    fputcsv($output, [
        $exp['expense_date'],
        $exp['category'],
        $exp['description'],
        number_format($exp['amount'], 2, '.', ''),
    ]);
    $total += $exp['amount'];
}

fputcsv($output, []);
fputcsv($output, ['', '', 'TOTAL', number_format($total, 2, '.', '')]);

fclose($output);
exit;
