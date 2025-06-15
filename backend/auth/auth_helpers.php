<?php
// backend/auth/auth_helpers.php

require_once __DIR__ . '/../utils/session.php';

/**
 * Check if user is logged in
 */
function is_user_logged_in(): bool {
    return is_logged_in();
}

/**
 * Redirect logged-in users to their home page by role
 */
function redirect_to_home_by_role(): void {
    $role = get_user_role();
    if ($role === 'professor') {
        header("Location: /frontend/professor/dashboard.php");
    } elseif ($role === 'student') {
        header("Location: /frontend/student/dashboard.php");
    } else {
        header("Location: /frontend/index.php");
    }
    exit;
}
