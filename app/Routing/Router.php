<?php
declare(strict_types=1);

namespace Denors\Routing;

use Closure;
use Denors\Http\Response;
use InvalidArgumentException;
use LogicException;

final class Router
{
    /** @var array<string, array<string, Closure(): Response>> */
    private array $routes = [];

    public function add(string $method, string $originalPath, Closure $handler): void
    {
        if (!preg_match('/\A[A-Z]+\z/', $method) || !self::validPath($originalPath)) {
            throw new InvalidArgumentException('Invalid route definition.');
        }
        if (isset($this->routes[$originalPath][$method])) {
            throw new LogicException('Duplicate route definition.');
        }
        $this->routes[$originalPath][$method] = $handler;
    }

    public function dispatch(string $method, string $requestUri): Response
    {
        // Do not decode escapes, collapse slashes, lowercase or append a trailing slash.
        $path = explode('?', $requestUri, 2)[0];
        if (!self::validPath($path) || preg_match('/[\x00-\x20\x7f#]/', $requestUri)) {
            return new Response('Некорректный адрес.', 400);
        }
        $handlers = $this->routes[$path] ?? null;
        if ($handlers === null) {
            return new Response('Страница не найдена.', 404);
        }
        $handler = $handlers[$method] ?? ($method === 'HEAD' ? ($handlers['GET'] ?? null) : null);
        if ($handler === null) {
            $allowed = array_keys($handlers);
            if (isset($handlers['GET']) && !isset($handlers['HEAD'])) {
                $allowed[] = 'HEAD';
            }
            return new Response('Метод не поддерживается.', 405, ['Allow' => implode(', ', $allowed)]);
        }
        return $handler();
    }

    private static function validPath(string $path): bool
    {
        return str_starts_with($path, '/')
            && !preg_match('/[\x00-\x20\x7f?#\\\\]/', $path)
            && !preg_match('/%(?![a-fA-F0-9]{2})|%(?:00|0a|0d)/i', $path);
    }
}
