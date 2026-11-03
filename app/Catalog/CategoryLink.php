<?php
declare(strict_types=1);

namespace Denors\Catalog;

use InvalidArgumentException;

/** Presentation data only; not a database model or an import format. */
final readonly class CategoryLink
{
    public function __construct(public string $title, public string $originalPath)
    {
        if (trim($title) === '' || !str_starts_with($originalPath, '/')
            || str_starts_with($originalPath, '//')
            || preg_match('/[\x00-\x20\x7f?#\\\\]/', $originalPath)
            || preg_match('/%(?![a-fA-F0-9]{2})|%(?:00|0a|0d)/i', $originalPath)) {
            throw new InvalidArgumentException('Invalid catalog category link.');
        }
    }
}
