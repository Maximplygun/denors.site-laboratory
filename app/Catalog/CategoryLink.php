<?php
declare(strict_types=1);

namespace Denors\Catalog;

use InvalidArgumentException;

/** Presentation data only; not a database model or an import format. */
final readonly class CategoryLink
{
    public function __construct(public string $title, public string $originalPath)
    {
        OriginalPath::validate($originalPath);
        if (trim($title) === '') {
            throw new InvalidArgumentException('Invalid catalog category title.');
        }
    }
}
