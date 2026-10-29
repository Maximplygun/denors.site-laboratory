<?php
declare(strict_types=1);

namespace Denors;

use Denors\Http\Response;
use Denors\Controllers\HomeController;
use Denors\Routing\Router;
use Denors\Support\View;

final readonly class Application
{
    private Router $router;
    private View $view;

    public function __construct(string $viewDirectory)
    {
        $this->view = new View($viewDirectory);
        $this->router = new Router();
        $home = new HomeController($this->view);
        $this->router->add('GET', '/', $home(...));
    }

    public function handle(string $method, string $requestUri): Response
    {
        $headers = [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ];
        $response = $this->router->dispatch($method, $requestUri);
        $body = $response->status === 404
            ? $this->view->render('errors/not-found')
            : $response->body;
        return new Response($body, $response->status, $response->headers + $headers);
    }
}
