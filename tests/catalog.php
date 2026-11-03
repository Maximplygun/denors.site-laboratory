<?php
declare(strict_types=1);

use Denors\Application;
use Denors\Catalog\CategoryLink;
use Denors\Support\View;

require dirname(__DIR__) . '/bootstrap/autoload.php';
$checks = 0;
$assert = static function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) throw new RuntimeException('FAIL: ' . $message);
    ++$checks;
};
$view = new View(dirname(__DIR__) . '/resources/views');
// Synthetic renderer fixtures only. Never registered as public catalog routes.
$category = new CategoryLink('<script>alert(1)</script>', '/MiXeD%2fPath/');
$html = $view->render('pages/catalog', ['categories' => [$category]]);
$assert(str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;'), 'category title is escaped');
$assert(!str_contains($html, '<script>alert(1)</script>'), 'category cannot inject script');
$assert(str_contains($html, 'href="/MiXeD%2fPath/"'), 'original path is preserved');
$quoted = $view->render('pages/catalog', ['categories' => [new CategoryLink('Test', '/a"b/')]]);
$assert(str_contains($quoted, 'href="/a&quot;b/"'), 'path attribute is escaped');
foreach (['', 'https://example.com/', '//example.com/', '/bad path/', '/bad\\path/', '/bad%zz', '/%0a', '/?x=1', '/#x'] as $path) {
    try { new CategoryLink('Test', $path); throw new RuntimeException('Unsafe path accepted'); }
    catch (InvalidArgumentException) { ++$checks; }
}
try { new CategoryLink('  ', '/test/'); throw new RuntimeException('Empty title accepted'); }
catch (InvalidArgumentException) { ++$checks; }
foreach ([[], [$category, $category], ['untyped'], 'scalar', ['named' => $category]] as $categories) {
    try { $view->render('pages/catalog', ['categories' => $categories]); throw new RuntimeException('Invalid directory accepted'); }
    catch (InvalidArgumentException) { ++$checks; }
}
$app = new Application(dirname(__DIR__) . '/resources/views');
$assert($app->handle('GET', '/shop/')->status === 404, 'unpopulated shop is not published');
$assert($app->handle('GET', '/MiXeD%2fPath/')->status === 404, 'fixtures are never published');
$assert($app->handle('GET', '/__catalog-preview/')->status === 404, 'visual preview is not an application route');
echo "PASS: {$checks} catalog checks.\n";
