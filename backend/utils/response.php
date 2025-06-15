<?php
declare(strict_types=1);

/**
 * Sends a JSON response with HTTP status code and data.
 *
 * @param int $statusCode HTTP status code
 * @param array $data Associative array to encode as JSON
 * @return void
 */
function sendJsonResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
