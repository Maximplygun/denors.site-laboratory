<?php
declare(strict_types=1);

use Denors\Http\Response;
use Denors\Routing\Router;
use Denors\Support\View;

require dirname(__DIR__) . '/bootstrap/autoload.php';
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $message);
    }
    ++$checks;
};
$router = new Router();
$paths = [];
$stream = fopen(dirname(__DIR__) . '/docs/audit/legacy-urls.csv', 'rb');
fgetcsv($stream, escape: '');
while (($row = fgetcsv($stream, escape: '')) !== false) {
    $path = $row[2];
    if (isset($paths[$path])) {
        continue;
    }
    $paths[$path] = true;
    // Test fixtures only: no sitemap URL is published as a placeholder route.
    $router->add('GET', $path, static fn (): Response => new Response($path));
}
fclose($stream);
$assert(count($paths) === 733, 'legacy baseline has 733 distinct paths');
foreach ($paths as $path => $_) {
    $response = $router->dispatch('GET', $path . '?audit=1');
    $assert($response->status === 200 && $response->body === $path, 'exact original path round trip');
}
$assert($router->dispatch('GET', '/ABOUT_US/')->status === 404, 'no case normalization');
$assert($router->dispatch('GET', '/about_us')->status === 404, 'no implicit trailing slash');
$assert($router->dispatch('POST', '/about_us/')->status === 405, 'known path wrong method');
$assert($router->dispatch('POST', '/unknown-route/')->status === 404, 'unknown path has no redirect or method alias');
$assert($router->dispatch('HEAD', '/about_us/')->status === 200, 'HEAD falls back to GET');
foreach (['https://denors.ru/', "/\r\nHeader: injected", '/%00', '/invalid%zz', '/?fragment=#bad'] as $uri) {
    $assert($router->dispatch('GET', $uri)->status === 400, 'invalid request URI rejected');
}
$encoded = new Router();
$encoded->add('GET', '/a%2Fb/', static fn (): Response => new Response('encoded'));
$encoded->add('GET', '/a/b/', static fn (): Response => new Response('nested'));
$assert($encoded->dispatch('GET', '/a%2Fb/')->body === 'encoded', 'escaped slash remains distinct');
$assert($encoded->dispatch('GET', '/a/b/')->body === 'nested', 'nested path remains distinct');
try {
    $encoded->add('GET', '/a/b/', static fn (): Response => new Response('duplicate'));
    throw new RuntimeException('Duplicate route accepted');
} catch (LogicException) {
    ++$checks;
}
$view = new View(dirname(__DIR__) . '/resources/views');
try {
    $view->render('../../config/app');
    throw new RuntimeException('Template traversal accepted');
} catch (InvalidArgumentException) {
    ++$checks;
}
echo "PASS: {$checks} routing checks, including all 733 legacy paths.\n";
