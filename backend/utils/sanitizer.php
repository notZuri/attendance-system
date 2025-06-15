<?php
// utils/sanitizer.php

/**
 * Sanitize a string input (removes tags, escapes special characters)
 */
function clean_input(string $data): string {
    return htmlspecialchars(trim(strip_tags($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize an email
 */
function clean_email(string $email): string {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

/**
 * Validate if input is a valid integer
 */
function is_valid_int($value): bool {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}
?>
