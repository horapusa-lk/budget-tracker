<?php
/**
 * API: Delete an expense record by ID.
 * DELETE /api/delete_expense.php?id=1
 */

require_once __DIR__ . '/../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = getCurrentUserId();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    jsonResponse(['error' => 'Invalid expense ID.'], 400);
}

$pdo = getDBConnection();

// Check the record exists and belongs to current user
$stmt = $pdo->prepare('SELECT id FROM expenses WHERE id = :id AND user_id = :user_id');
$stmt->execute([':id' => $id, ':user_id' => $userId]);

if (!$stmt->fetch()) {
    jsonResponse(['error' => 'Expense not found.'], 404);
}

// Delete
$del = $pdo->prepare('DELETE FROM expenses WHERE id = :id AND user_id = :user_id');
$del->execute([':id' => $id, ':user_id' => $userId]);

jsonResponse([
    'success' => true,
    'message' => 'Expense deleted successfully.',
]);
