<?php
declare(strict_types=1);

/**
 * Checks if user session is active
 * 
 * @return bool
 */
function isUserLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Checks if current logged-in user has at least one role from allowed roles array
 * 
 * @param array $allowedRoles List of roles, e.g., ['professor', 'student']
 * @return bool
 */
function userHasRole(array $allowedRoles): bool
{
    if (!isset($_SESSION['user_role'])) {
        return false;
    }

    return in_array($_SESSION['user_role'], $allowedRoles, true);
}

/**
 * Securely logs out the user and destroys the session
 * 
 * @return void
 */
function logoutUser(): void
{
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
