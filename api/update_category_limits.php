<?php
/**
 * POST /api/update_category_limits.php — Save per-user category budget limits.
 * Body (JSON): { limits: [{category_id, budget_limit}, ...] }
 */
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$userId = getCurrentUserId();
$input  = json_decode(file_get_contents('php://input'), true);
$limits = $input['limits'] ?? [];

if (!is_array($limits) || empty($limits)) {
    jsonResponse(['error' => 'No limits provided.'], 400);
}

try {
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO user_category_limits (user_id, category_id, budget_limit)
         VALUES (:user_id, :cat_id, :limit)
         ON DUPLICATE KEY UPDATE budget_limit = :limit2'
    );

    foreach ($limits as $item) {
        $catId = (int) ($item['category_id'] ?? 0);
        $limit = (float) ($item['budget_limit'] ?? 0);
        if ($catId <= 0 || $limit < 0) continue;

        $stmt->execute([
            ':user_id' => $userId,
            ':cat_id'  => $catId,
            ':limit'   => $limit,
            ':limit2'  => $limit,
        ]);
    }

    jsonResponse(['success' => true]);

} catch (PDOException $e) {
    jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
}
