<?php

namespace App\Http;

use InvalidArgumentException;

class Request
{
    public function getJsonBody(): array
    {
        $body = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($body)) {
            throw new InvalidArgumentException(
                'Invalid JSON body.'
            );
        }

        return $body;
    }

    public function getQuery(string $key): ?string
    {
        return isset($_GET[$key]) ? trim($_GET[$key]) : null;
    }

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
}
