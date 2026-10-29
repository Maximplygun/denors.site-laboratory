<?php
declare(strict_types=1);

namespace Denors\Controllers;

use Denors\Http\Response;
use Denors\Support\View;

final readonly class HomeController
{
    public function __construct(private View $view) {}

    public function __invoke(): Response
    {
        return new Response($this->view->render('pages/staging'));
    }
}
