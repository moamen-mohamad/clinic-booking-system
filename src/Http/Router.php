<?php

namespace App\Http;

use InvalidArgumentException;

class Router
{
    private array $routes = [];

    public function get(
        string $path,
        callable $handler
    ): void {
        $this->addRoute(
            method: 'GET',
            path: $path,
            handler: $handler
        );
    }

    public function post(
        string $path,
        callable $handler
    ): void {
        $this->addRoute(
            method: 'POST',
            path: $path,
            handler: $handler
        );
    }

    public function patch(
        string $path,
        callable $handler
    ): void {
        $this->addRoute(
            method: 'PATCH',
            path: $path,
            handler: $handler
        );
    }

    public function dispatch(
        string $method,
        string $uri
    ): void {
        $path = parse_url($uri, PHP_URL_PATH);

        if ($path === false || $path === null) {
            throw new InvalidArgumentException(
                'Invalid request URI.'
            );
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $matches = [];

            if (
                preg_match(
                    $route['pattern'],
                    $path,
                    $matches
                ) !== 1
            ) {
                continue;
            }

            array_shift($matches);

            $matches = array_map(
                function (string $value): int|string {
                    return ctype_digit($value)
                        ? (int) $value
                        : $value;
                },
                $matches
            );

            $route['handler'](...$matches);

            return;
        }

        JsonResponse::send([
            'message' => 'Route not found.'
        ], 404);
    }

    private function addRoute(
        string $method,
        string $path,
        callable $handler
    ): void {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $this->convertPathToRegex($path),
            'handler' => $handler,
        ];
    }

    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            '([^/]+)',
            $path
        );

        if ($pattern === null) {
            throw new InvalidArgumentException(
                'Invalid route pattern.'
            );
        }

        return '#^' . $pattern . '$#';
    }
}
