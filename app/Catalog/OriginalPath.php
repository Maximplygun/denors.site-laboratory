<?php
declare(strict_types=1);
namespace Denors\Catalog;
use InvalidArgumentException;
final class OriginalPath
{
    public static function validate(string $path): void
    {
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')
            || preg_match('/[\x00-\x20\x7f?#\\\\]/', $path)
            || preg_match('/%(?![a-fA-F0-9]{2})|%(?:00|0a|0d)/i', $path)) {
            throw new InvalidArgumentException('Invalid original path.');
        }
    }
}
