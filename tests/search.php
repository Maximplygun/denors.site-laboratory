<?php
declare(strict_types=1);
use Denors\Catalog\{ProductDetails, SearchItem, SearchQuery, ProductSearch};
use Denors\Support\View;
use Denors\Application;
require dirname(__DIR__) . '/bootstrap/autoload.php';
$checks = 0;
$assert = static function (bool $ok, string $message) use (&$checks): void {
    if (!$ok) throw new RuntimeException('FAIL: ' . $message);
    ++$checks;
};
$engine = new ProductSearch();
$items = [
    new SearchItem(new ProductDetails('Камера белая', 'Для улицы', sku: 'TEST-A', priceKopecks: 10050), '/first/'),
    new SearchItem(new ProductDetails('Камера чёрная', 'Для офиса', sku: 'TEST-B', priceKopecks: 20000), '/second/'),
    new SearchItem(new ProductDetails('Датчик', 'Камера совместима', sku: 'TEST-C'), '/third/'),
    new SearchItem(new ProductDetails('Бесплатный тест', '', priceKopecks: 0), '/zero/'),
];
$search = static fn (array $parameters): array => $engine->search($items, SearchQuery::fromArray($parameters));
$assert($search(['q' => 'КАМЕРА'])['total'] === 3, 'Cyrillic case insensitive description and title search');
$assert($search(['q' => 'камера улицы'])['total'] === 1, 'all terms must match');
$assert($search(['q' => 'test-b'])['items'][0]->originalPath === '/second/', 'SKU search');
$assert($search(['q' => 'not-found'])['total'] === 0, 'empty results');
$assert($search(['min' => '100,50', 'max' => '100.50'])['total'] === 1, 'inclusive exact price bounds');
$assert($search(['min' => '0', 'max' => '0'])['items'][0]->originalPath === '/zero/', 'zero price is a real value');
$assert($search(['min' => '0'])['total'] === 3, 'unknown prices excluded by bounds');
foreach (['price_asc', 'price_desc'] as $sort) {
    $assert($search(['sort' => $sort])['items'][3]->originalPath === '/third/', 'unknown prices sort last');
}
$assert($search(['sort' => 'price_desc'])['items'][0]->originalPath === '/second/', 'descending price');
$assert($search(['sort' => 'price_asc'])['items'][0]->originalPath === '/zero/', 'ascending price');
$assert($search(['sort' => 'name'])['items'][0]->originalPath === '/zero/', 'Cyrillic name ordering');
$assert(SearchQuery::fromArray(['min' => '0,01'])->parameters()['min'] === '0.01', 'query prices serialize exactly');
$assert($search(['page' => '999999'])['page'] === 1, 'out of range page clamps to last');
$assert(SearchQuery::fromArray(['q' => '  КАМЕРА   улицы '])->text === 'КАМЕРА улицы', 'query whitespace normalized');
foreach ([['q' => []], ['min' => []], ['sort' => []], ['page' => []], ['q' => str_repeat('я',121)], ['q' => "\xff"], ['min' => '-1'], ['max' => '1e3'], ['min' => '1.001'], ['min' => '1000000000'], ['min' => '2', 'max' => '1'], ['sort' => 'SQL'], ['page' => '0'], ['page' => '-1'], ['page' => '1.5']] as $parameters) {
    try { SearchQuery::fromArray($parameters); throw new RuntimeException('Invalid query accepted'); }
    catch (InvalidArgumentException) { ++$checks; }
}
$many = [];
for ($i=0; $i<25; ++$i) $many[] = new SearchItem(new ProductDetails('Equal', '', priceKopecks: 100), '/item-' . str_pad((string)$i,2,'0',STR_PAD_LEFT) . '/');
$result = $engine->search(array_reverse($many), SearchQuery::fromArray(['sort' => 'price_asc', 'page' => '2']));
$assert($result['total'] === 25 && $result['pages'] === 3 && count($result['items']) === 12, 'pagination totals and size');
$assert($result['items'][0]->originalPath === '/item-12/', 'ties have stable original path ordering');
$assert(count($engine->search($many, SearchQuery::fromArray(['page' => '3']))['items']) === 1, 'last partial page');
try { $engine->search([$items[0], $items[0]], SearchQuery::fromArray([])); throw new RuntimeException('Duplicate accepted'); }
catch (InvalidArgumentException) { ++$checks; }
$query = SearchQuery::fromArray(['q' => '"><script>x</script>', 'sort' => 'name']);
$html = (new View(dirname(__DIR__) . '/resources/views'))->render('pages/search', ['action' => '/preview/', 'query' => $query, 'result' => $result]);
$assert(!str_contains($html, '<script>x</script>') && str_contains($html, '&lt;script&gt;'), 'reflected query escaped');
$assert(str_contains($html, 'sort=name&amp;page=3'), 'pagination retains sorting and query parameters');
$assert((new Application(dirname(__DIR__) . '/resources/views'))->handle('GET','/__search-preview/')->status === 404, 'preview remains unpublished');
echo "PASS: {$checks} search checks.\n";
