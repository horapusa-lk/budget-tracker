<?php
/**
 * POST /api/login.php — Authenticate a user.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['error' => 'Invalid request body.'], 400);
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if ($username === '' || $password === '') {
    jsonResponse(['error' => 'Username and password are required.'], 400);
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT id, full_name, password FROM users WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse(['error' => 'Invalid username or password.'], 401);
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['username'] = $username;

    jsonResponse(['success' => true, 'user' => ['id' => $user['id'], 'full_name' => $user['full_name']]]);

} catch (PDOException $e) {
    $msg = $e->getMessage();

    if (stripos($msg, 'Base table or view not found') !== false || stripos($msg, "doesn't exist") !== false) {
        jsonResponse(['error' => 'Database tables not found. Please import sql/schema.sql into MySQL first.'], 500);
    }

    jsonResponse(['error' => 'Database error: ' . $msg], 500);
}
