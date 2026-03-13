<?php
/**
 * Authentication helpers — session management and access control.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to login page if user is not authenticated.
 */
function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Get the currently logged-in user's ID.
 */
function getCurrentUserId(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

/**
 * Get the currently logged-in user's display name.
 */
function getCurrentUserName(): string
{
    return $_SESSION['full_name'] ?? '';
}

/**
 * Check if a user is currently logged in.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}
