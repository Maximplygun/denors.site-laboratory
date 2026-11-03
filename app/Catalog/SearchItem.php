<?php
declare(strict_types=1);
namespace Denors\Catalog;
/** Independent presentation entry; no import/database schema is implied. */
final readonly class SearchItem
{
    public function __construct(public ProductDetails $product, public string $originalPath)
    {
        OriginalPath::validate($originalPath);
    }
}
