<?php
declare(strict_types=1);

namespace Denors\Support;

use RuntimeException;

final class Environment
{
    /** @param array<string, string> $values */
    private function __construct(private readonly array $values) {}

    public static function load(string $file): self
    {
        if (!file_exists($file)) {
            return new self([]);
        }
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new RuntimeException('Environment file is not readable.');
        }
        $values = [];
        foreach ($lines as $number => $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!preg_match('/\A([A-Z][A-Z0-9_]*)=(.*)\z/', $line, $matches)) {
                throw new RuntimeException('Invalid environment syntax at line ' . ($number + 1));
            }
            [, $key, $value] = $matches;
            $value = trim($value);
            if (isset($values[$key])) {
                throw new RuntimeException('Duplicate environment key at line ' . ($number + 1));
            }
            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                if (strlen($value) < 2 || $value[-1] !== $value[0]) {
                    throw new RuntimeException('Unclosed environment quote at line ' . ($number + 1));
                }
                $value = substr($value, 1, -1);
            }
            $values[$key] = $value;
        }
        return new self($values);
    }

    public function get(string $key, string $default = ''): string
    {
        $processValue = getenv($key);
        return $processValue === false ? ($this->values[$key] ?? $default) : $processValue;
    }
}
