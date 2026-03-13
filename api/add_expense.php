<?php
/**
 * API: Add a new expense record.
 * POST /api/add_expense.php
 * Body (JSON): { category_id, amount, description, expense_date }
 */

require_once __DIR__ . '/../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = getCurrentUserId();

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['error' => 'Invalid JSON body'], 400);
}

$categoryId  = isset($input['category_id']) ? (int) $input['category_id'] : 0;
$amount      = isset($input['amount']) ? (float) $input['amount'] : 0;
$description = isset($input['description']) ? sanitize($input['description']) : '';
$expenseDate = isset($input['expense_date']) ? $input['expense_date'] : '';

// Validation
$errors = [];

if ($categoryId <= 0) {
    $errors[] = 'Please select a valid category.';
}
if ($amount <= 0) {
    $errors[] = 'Amount must be greater than zero.';
}
if ($amount > 99999.99) {
    $errors[] = 'Amount is too large.';
}
if (!isValidDate($expenseDate)) {
    $errors[] = 'Please provide a valid date (YYYY-MM-DD).';
}
if (strlen($description) > 255) {
    $errors[] = 'Description must be 255 characters or fewer.';
}

if (!empty($errors)) {
    jsonResponse(['error' => implode(' ', $errors)], 422);
}

// Verify category exists
$pdo = getDBConnection();
$catStmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id');
$catStmt->execute([':id' => $categoryId]);
if (!$catStmt->fetch()) {
    jsonResponse(['error' => 'Category not found.'], 404);
}

// Insert expense
$stmt = $pdo->prepare(
    'INSERT INTO expenses (user_id, category_id, amount, description, expense_date)
     VALUES (:user_id, :category_id, :amount, :description, :expense_date)'
);
$stmt->execute([
    ':user_id'      => $userId,
    ':category_id'  => $categoryId,
    ':amount'       => $amount,
    ':description'  => $description,
    ':expense_date' => $expenseDate,
]);

jsonResponse([
    'success' => true,
    'message' => 'Expense added successfully.',
    'id'      => (int) $pdo->lastInsertId(),
]);
