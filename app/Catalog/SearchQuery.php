<?php
declare(strict_types=1);
namespace Denors\Catalog;
use InvalidArgumentException;
final readonly class SearchQuery
{
    private function __construct(
        public string $text, public ?int $minimum, public ?int $maximum,
        public string $sort, public int $page,
    ) {}

    public static function fromArray(array $input): self
    {
        $values = [];
        foreach (['q', 'min', 'max', 'sort', 'page'] as $key) {
            $value = $input[$key] ?? '';
            if (!is_string($value) || !mb_check_encoding($value, 'UTF-8')) {
                throw new InvalidArgumentException('Параметры должны быть строками UTF-8.');
            }
            $values[$key] = trim($value);
        }
        $text = trim(preg_replace('/\s+/u', ' ', $values['q']));
        if (mb_strlen($text, 'UTF-8') > 120) throw new InvalidArgumentException('Поисковый запрос — не более 120 символов.');
        $parsePrice = static function (string $value): ?int {
            if ($value === '') return null;
            if (!preg_match('/\A([0-9]{1,9})(?:[.,]([0-9]{1,2}))?\z/', $value, $match)) {
                throw new InvalidArgumentException('Цена должна быть от 0 до 999999999,99 с точностью до копейки.');
            }
            return (int) $match[1] * 100 + (int) str_pad($match[2] ?? '', 2, '0');
        };
        $minimum = $parsePrice($values['min']);
        $maximum = $parsePrice($values['max']);
        if ($minimum !== null && $maximum !== null && $minimum > $maximum) {
            throw new InvalidArgumentException('Цена «от» не должна превышать цену «до».');
        }
        $sort = $values['sort'] === '' ? 'default' : $values['sort'];
        if (!in_array($sort, ['default', 'price_asc', 'price_desc', 'name'], true)) {
            throw new InvalidArgumentException('Неизвестная сортировка.');
        }
        $page = $values['page'] === '' ? '1' : $values['page'];
        if (!preg_match('/\A[1-9][0-9]{0,5}\z/', $page)) throw new InvalidArgumentException('Некорректный номер страницы.');
        return new self($text, $minimum, $maximum, $sort, (int) $page);
    }

    public function parameters(): array
    {
        $price = static fn (?int $value): string => $value === null ? '' : intdiv($value, 100) . '.' . str_pad((string) ($value % 100), 2, '0', STR_PAD_LEFT);
        return ['q' => $this->text, 'min' => $price($this->minimum), 'max' => $price($this->maximum), 'sort' => $this->sort];
    }
}
