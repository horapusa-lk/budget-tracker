<?php
/**
 * Savings Goals API
 * POST   — Create a new goal { name, target_amount, deadline? }
 * PUT    — Add money to a goal { id, add_amount }
 * DELETE — Remove a goal ?id=X
 */
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDBConnection();

// ─── CREATE ──────────────────────────────────────────────
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name   = trim($input['name'] ?? '');
    $target = isset($input['target_amount']) ? (float) $input['target_amount'] : 0;
    $deadline = !empty($input['deadline']) ? $input['deadline'] : null;

    if ($name === '' || $target <= 0) {
        jsonResponse(['error' => 'Name and a positive target amount are required.'], 400);
    }

    if ($deadline && !isValidDate($deadline)) {
        jsonResponse(['error' => 'Invalid deadline date.'], 400);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO savings_goals (user_id, name, target_amount, deadline) VALUES (:uid, :name, :target, :deadline)'
    );
    $stmt->execute([':uid' => $userId, ':name' => $name, ':target' => $target, ':deadline' => $deadline]);

    jsonResponse(['success' => true, 'id' => (int) $pdo->lastInsertId()], 201);
}

// ─── UPDATE (add money) ──────────────────────────────────
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $goalId   = isset($input['id']) ? (int) $input['id'] : 0;
    $addAmount = isset($input['add_amount']) ? (float) $input['add_amount'] : 0;

    if ($goalId <= 0 || $addAmount <= 0) {
        jsonResponse(['error' => 'Goal ID and a positive amount are required.'], 400);
    }

    // Verify ownership
    $stmt = $pdo->prepare('SELECT id FROM savings_goals WHERE id = :id AND user_id = :uid');
    $stmt->execute([':id' => $goalId, ':uid' => $userId]);
    if (!$stmt->fetch()) {
        jsonResponse(['error' => 'Goal not found.'], 404);
    }

    $stmt = $pdo->prepare('UPDATE savings_goals SET current_amount = current_amount + :amt WHERE id = :id');
    $stmt->execute([':amt' => $addAmount, ':id' => $goalId]);

    jsonResponse(['success' => true]);
}

// ─── DELETE ──────────────────────────────────────────────
if ($method === 'DELETE') {
    $goalId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($goalId <= 0) {
        jsonResponse(['error' => 'Invalid goal ID.'], 400);
    }

    $stmt = $pdo->prepare('DELETE FROM savings_goals WHERE id = :id AND user_id = :uid');
    $stmt->execute([':id' => $goalId, ':uid' => $userId]);

    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Method not allowed.'], 405);
