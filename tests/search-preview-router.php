<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit(1); }
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== '/__search-preview/') return false;
require dirname(__DIR__) . '/bootstrap/autoload.php';
$items = [];
$file = fopen(__DIR__ . '/../docs/audit/legacy-urls.csv', 'rb');
fgetcsv($file, escape: '');
while (($row = fgetcsv($file, escape: '')) !== false && count($items) < 30) {
    if ($row[0] !== 'product-sitemap.xml' || $row[2] === '/shop/') continue;
    $i = count($items) + 1;
    $items[] = new \Denors\Catalog\SearchItem(new \Denors\Catalog\ProductDetails(
        'Тестовый ' . ($i % 2 ? 'датчик' : 'видеомодуль') . ' ' . $i,
        'Тестовое описание для локальной проверки, не предложение к продаже.',
        sku: 'TEST-' . $i, priceKopecks: $i % 7 ? $i * 10050 : null,
    ), $row[2]);
}
fclose($file);
$data = ['action' => '/__search-preview/', 'values' => $_GET];
try { $data['query'] = \Denors\Catalog\SearchQuery::fromArray($_GET); }
catch (\InvalidArgumentException $error) {
    http_response_code(400);
    $data['error'] = $error->getMessage();
    $data['query'] = \Denors\Catalog\SearchQuery::fromArray([]);
}
$data['result'] = (new \Denors\Catalog\ProductSearch())->search($items, $data['query']);
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Cache-Control: no-store');
echo (new \Denors\Support\View(dirname(__DIR__) . '/resources/views'))->render('pages/search', $data);
