<?php
declare(strict_types=1);

/**
 * Sends a JSON response with HTTP status code and data.
 *
 * @param int $status_code HTTP status code
 * @param array $data Associative array to encode as JSON
 * @return void
 */
function send_json_response(int $status_code, array $data): void
{
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
