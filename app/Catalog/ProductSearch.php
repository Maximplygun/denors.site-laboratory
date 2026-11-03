<?php
declare(strict_types=1);
namespace Denors\Catalog;
use InvalidArgumentException;
final class ProductSearch
{
    public const PAGE_SIZE = 12;
    /** In-memory reference engine for local fixtures; replace data access after export analysis. */
    public function search(array $items, SearchQuery $query): array
    {
        $matches = [];
        $seen = [];
        $terms = $query->text === '' ? [] : explode(' ', mb_strtolower($query->text, 'UTF-8'));
        foreach ($items as $item) {
            if (!$item instanceof SearchItem || isset($seen[$item->originalPath])) {
                throw new InvalidArgumentException('Search entries must have distinct original paths.');
            }
            $seen[$item->originalPath] = true;
            $product = $item->product;
            $haystack = mb_strtolower($product->title . ' ' . ($product->sku ?? '') . ' ' . $product->description, 'UTF-8');
            foreach ($terms as $term) {
                if (!str_contains($haystack, $term)) continue 2;
            }
            if (($query->minimum !== null || $query->maximum !== null) && $product->priceKopecks === null) continue;
            if ($query->minimum !== null && $product->priceKopecks < $query->minimum) continue;
            if ($query->maximum !== null && $product->priceKopecks > $query->maximum) continue;
            $matches[] = $item;
        }
        if ($query->sort !== 'default') {
            usort($matches, static function (SearchItem $a, SearchItem $b) use ($query): int {
                if ($query->sort === 'name') {
                    $comparison = strcmp(mb_strtolower($a->product->title, 'UTF-8'), mb_strtolower($b->product->title, 'UTF-8'));
                } else {
                    $left = $a->product->priceKopecks;
                    $right = $b->product->priceKopecks;
                    if ($left === null) return $right === null ? strcmp($a->originalPath, $b->originalPath) : 1;
                    if ($right === null) return -1;
                    $comparison = $left <=> $right;
                    if ($query->sort === 'price_desc') $comparison = -$comparison;
                }
                return $comparison ?: strcmp($a->originalPath, $b->originalPath);
            });
        }
        $total = count($matches);
        $pages = max(1, (int) ceil($total / self::PAGE_SIZE));
        $page = min($query->page, $pages);
        return ['items' => array_slice($matches, ($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }
}
