<?php
declare(strict_types=1);
use Denors\Catalog\ProductDetails;
use Denors\Support\View;
use Denors\Application;
require dirname(__DIR__) . '/bootstrap/autoload.php';
$checks = 0;
$assert = static function (bool $ok, string $message) use (&$checks): void {
    if (!$ok) throw new RuntimeException('FAIL: ' . $message);
    ++$checks;
};
foreach ([0 => '0 ₽', 105 => '1,05 ₽', 123456 => '1 234,56 ₽', 100000 => '1 000 ₽'] as $value => $expected) {
    $assert((new ProductDetails('Test', '', priceKopecks: $value))->formattedPrice() === $expected, 'exact minor-unit price formatting');
}
$view = new View(dirname(__DIR__) . '/resources/views');
$product = new ProductDetails('<script>test</script>', '<b>unsafe</b>', sku: '" onmouseover="test', specifications: ['<b>Key</b>' => '<img src=x>']);
$html = $view->render('pages/product', ['product' => $product]);
$assert(!str_contains($html, '<script>test</script>') && str_contains($html, '&lt;script&gt;test&lt;/script&gt;'), 'title escaped');
$assert(str_contains($html, '&lt;b&gt;unsafe&lt;/b&gt;'), 'description is plain escaped text');
$assert(str_contains($html, '&lt;img src=x&gt;'), 'specification escaped');
$assert(str_contains($html, 'Изображение не добавлено') && str_contains($html, 'Цена не указана'), 'missing data states are explicit');
$assert(str_contains($html, '<dl class="product__specifications">'), 'specifications are semantic');
$empty = $view->render('pages/product', ['product' => new ProductDetails('Test', '')]);
$assert(!str_contains($empty, 'href="#product-specifications"'), 'missing specifications have no dead anchor');
$assert(!str_contains($empty, 'Артикул:'), 'missing SKU is not invented');
$assert(str_contains($empty, 'Описание пока не добавлено.'), 'missing description is explicit');
foreach (['https://example.com/a.jpg', '//example.com/a.jpg', '/config/app.php', '/uploads/../a', '/uploads/%2e%2e/a', '/uploads/a"b', '/uploads/a%zz'] as $path) {
    try { new ProductDetails('Test', '', images: [$path]); throw new RuntimeException('Invalid image accepted'); }
    catch (InvalidArgumentException) { ++$checks; }
}
try { new ProductDetails('Test', '', priceKopecks: -1); throw new RuntimeException('Negative price accepted'); }
catch (InvalidArgumentException) { ++$checks; }
try { $view->render('pages/product'); throw new RuntimeException('Missing product accepted'); }
catch (InvalidArgumentException) { ++$checks; }
$app = new Application(dirname(__DIR__) . '/resources/views');
$assert($app->handle('GET', '/__product-preview/')->status === 404, 'preview is not public');
echo "PASS: {$checks} product checks.\n";
