<?php
declare(strict_types=1);

namespace Denors\Support;

use Throwable;

final class Logger
{
    public function __construct(private readonly string $directory) {}

    public function exception(Throwable $error, string $reference): void
    {
        // Do not record messages, request data or trace arguments: they may contain secrets.
        $record = json_encode([
            'time' => gmdate('c'),
            'reference' => $reference,
            'type' => $error::class,
            'code' => $error->getCode(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
        ], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        if (!is_dir($this->directory) && !@mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            error_log('Denors: private log unavailable; reference=' . $reference);
            return;
        }
        $path = $this->directory . '/application-' . gmdate('Y-m-d') . '.jsonl';
        if (@file_put_contents($path, $record . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            error_log('Denors: private log unavailable; reference=' . $reference);
            return;
        }
        @chmod($path, 0600);
    }
}
