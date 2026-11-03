<?php
declare(strict_types=1);

// Local visual fixture router. Not registered in Application or the public root.
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit(1);
}
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== '/__catalog-preview/') {
    return false;
}
require dirname(__DIR__) . '/bootstrap/autoload.php';
$categories = [];
$file = fopen(__DIR__ . '/../docs/audit/legacy-urls.csv', 'rb');
fgetcsv($file, escape: '');
while (($row = fgetcsv($file, escape: '')) !== false && count($categories) < 8) {
    if ($row[0] === 'product_cat-sitemap.xml') {
        $number = count($categories) + 1;
        $title = $number === 2
            ? 'Тестовое длинное название раздела для проверки переноса строк и доступности'
            : 'Тестовый раздел ' . $number;
        $categories[] = new \Denors\Catalog\CategoryLink($title, $row[2]);
    }
}
fclose($file);
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Cache-Control: no-store');
echo (new \Denors\Support\View(dirname(__DIR__) . '/resources/views'))->render('pages/catalog', ['categories' => $categories]);
