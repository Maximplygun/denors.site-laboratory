<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit(1); }
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== '/__product-preview/') return false;
require dirname(__DIR__) . '/bootstrap/autoload.php';
$empty = isset($_GET['empty']);
$product = new \Denors\Catalog\ProductDetails(
    'Тестовый товар — образец интерфейса, не предложение к продаже',
    "Тестовое описание для проверки переноса строк.\nДанные не относятся к реальному оборудованию.",
    sku: 'TEST-ONLY', priceKopecks: $empty ? null : 123456,
    images: $empty ? [] : ['/assets/images/security-system.svg', '/assets/images/security-system.svg'],
    specifications: $empty ? [] : ['Тестовая характеристика' => 'Образец значения', 'Длинное название тестовой характеристики' => 'Длинное значение для проверки переноса на мобильном экране'],
);
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Cache-Control: no-store');
echo (new \Denors\Support\View(dirname(__DIR__) . '/resources/views'))->render('pages/product', ['product' => $product]);
