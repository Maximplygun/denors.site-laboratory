<?php
declare(strict_types=1);

namespace Denors\Support;

use InvalidArgumentException;
use RuntimeException;

final readonly class View
{
    public function __construct(private string $directory) {}

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        // Only internal template identifiers; user input must never select a file.
        if (!preg_match('/\A[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*\z/', $template)) {
            throw new InvalidArgumentException('Invalid template name.');
        }
        $file = $this->directory . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('Template not found.');
        }
        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        ob_start();
        try {
            // Data stays in one array, never extracted into local variables.
            require $file;
            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }
}
