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
