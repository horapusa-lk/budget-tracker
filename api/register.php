<?php
/**
 * POST /api/register.php — Create a new user account.
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

$fullName = trim($input['full_name'] ?? '');
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

// Validation
if ($fullName === '' || $username === '' || $password === '') {
    jsonResponse(['error' => 'All fields are required.'], 400);
}

if (strlen($password) < 6) {
    jsonResponse(['error' => 'Password must be at least 6 characters.'], 400);
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    jsonResponse(['error' => 'Username may only contain letters, numbers, and underscores.'], 400);
}

try {
    $pdo = getDBConnection();

    // Check if username is taken
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'Username is already taken.'], 409);
    }

    // Create user
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (username, full_name, password) VALUES (:username, :full_name, :password)');
    $stmt->execute([
        ':username'  => $username,
        ':full_name' => $fullName,
        ':password'  => $hashedPassword,
    ]);

    $userId = (int) $pdo->lastInsertId();

    // Create default allowance for the new user (current month)
    $stmt = $pdo->prepare('INSERT INTO allowances (user_id, amount, month, year) VALUES (:user_id, :amount, :month, :year)');
    $stmt->execute([
        ':user_id' => $userId,
        ':amount'  => 30000.00,
        ':month'   => (int) date('n'),
        ':year'    => (int) date('Y'),
    ]);

    // Seed per-user category limits from defaults
    $pdo->exec(
        "INSERT INTO user_category_limits (user_id, category_id, budget_limit)
         SELECT {$userId}, id, budget_limit FROM categories"
    );

    // Auto-login after registration
    $_SESSION['user_id'] = $userId;
    $_SESSION['full_name'] = $fullName;
    $_SESSION['username'] = $username;

    jsonResponse(['success' => true, 'user' => ['id' => $userId, 'full_name' => $fullName]], 201);

} catch (PDOException $e) {
    $msg = $e->getMessage();

    // Check for common issues and give helpful error messages
    if (stripos($msg, 'Base table or view not found') !== false || stripos($msg, "doesn't exist") !== false) {
        jsonResponse(['error' => 'Database tables not found. Please import sql/schema.sql into MySQL first.'], 500);
    }

    jsonResponse(['error' => 'Database error: ' . $msg], 500);
}
