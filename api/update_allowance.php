<?php
/**
 * POST /api/update_allowance.php — Update monthly allowance.
 * Body (JSON): { amount }
 */
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$userId = getCurrentUserId();
$input  = json_decode(file_get_contents('php://input'), true);
$amount = isset($input['amount']) ? (float) $input['amount'] : -1;

if ($amount < 0 || $amount > 999999.99) {
    jsonResponse(['error' => 'Invalid amount.'], 400);
}

$month = (int) date('n');
$year  = (int) date('Y');
$pdo   = getDBConnection();

$stmt = $pdo->prepare(
    'INSERT INTO allowances (user_id, amount, month, year) VALUES (:user_id, :amount, :month, :year)
     ON DUPLICATE KEY UPDATE amount = :amount2'
);
$stmt->execute([
    ':user_id' => $userId,
    ':amount'  => $amount,
    ':month'   => $month,
    ':year'    => $year,
    ':amount2' => $amount,
]);

jsonResponse(['success' => true, 'amount' => $amount]);
