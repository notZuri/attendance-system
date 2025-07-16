<?php
// Email validation
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
// Password strength validation (at least 6 chars, can be improved)
function isStrongPassword($password) {
    return is_string($password) && strlen($password) >= 6;
} 