<?php
declare(strict_types=1);

namespace Denors\Catalog;

use InvalidArgumentException;

/** Presentation contract only; not the database schema or an import format. */
final readonly class ProductDetails
{
    /** @param list<string> $images @param array<string, string> $specifications */
    public function __construct(
        public string $title,
        public string $description,
        public ?string $sku = null,
        public ?int $priceKopecks = null,
        public array $images = [],
        public array $specifications = [],
    ) {
        if (trim($title) === '' || ($priceKopecks !== null && $priceKopecks < 0) || !array_is_list($images)) {
            throw new InvalidArgumentException('Invalid product presentation.');
        }
        foreach ($images as $path) {
            if (!is_string($path) || !preg_match('~\A/(?:assets/images|uploads|wp-content/uploads)/[a-zA-Z0-9_./%-]+\z~', $path)
                || str_contains(rawurldecode($path), '..') || preg_match('/%(?![a-fA-F0-9]{2})|%(?:00|0a|0d)/i', $path)) {
                throw new InvalidArgumentException('Invalid product image path.');
            }
        }
        foreach ($specifications as $name => $value) {
            if (!is_string($name) || trim($name) === '' || !is_string($value)) {
                throw new InvalidArgumentException('Invalid product specification.');
            }
        }
    }

    public function formattedPrice(): ?string
    {
        if ($this->priceKopecks === null) return null;
        $whole = intdiv($this->priceKopecks, 100);
        $fraction = $this->priceKopecks % 100;
        return preg_replace('/\\B(?=(\\d{3})+(?!\\d))/', ' ', (string) $whole) . ($fraction === 0 ? '' : ',' . str_pad((string) $fraction, 2, '0', STR_PAD_LEFT)) . ' ₽';
    }
}
