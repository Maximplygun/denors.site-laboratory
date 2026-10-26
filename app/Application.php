<?php
declare(strict_types=1);

namespace Denors;

use Denors\Http\Response;

final readonly class Application
{
    public function __construct(private string $viewDirectory) {}

    public function handle(string $method, string $requestUri): Response
    {
        $headers = [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ];
        if (!in_array($method, ['GET', 'HEAD'], true)) {
            return new Response('Метод не поддерживается.', 405, $headers + ['Allow' => 'GET, HEAD']);
        }
        // Only the existing homepage is implemented. No guessed legacy routes or redirects.
        $path = explode('?', $requestUri, 2)[0];
        if ($path !== '/') {
            return new Response('Страница не найдена.', 404, $headers);
        }
        ob_start();
        try {
            require $this->viewDirectory . '/pages/staging.php';
            $html = (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
        return new Response($html, 200, $headers);
    }
}
