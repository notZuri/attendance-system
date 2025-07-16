<?php
// utils/session.php

session_start();

/**
 * Check if user is logged in
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function get_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function get_user_role(): ?string {
    return $_SESSION['role'] ?? null;
}

/**
 * Check if user has at least one of the allowed roles
 */
function user_has_role(array $allowed_roles): bool {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $allowed_roles, true);
}

/**
 * Securely log out the user and destroy the session
 */
function logout_user(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Require login or redirect to login page
 */
function require_login(): void {
    if (!is_logged_in()) {
        header("Location: /frontend/index.php?error=not_logged_in");
        exit;
    }
}

/**
 * Require role or redirect
 */
function require_role(string $required_role): void {
    require_login();
    if (get_user_role() !== $required_role) {
        header("Location: /frontend/home.php?error=access_denied");
        exit;
    }
}
?>
