<?php
declare(strict_types=1);

namespace Denors\Support;

use ErrorException;
use Throwable;

final class ErrorHandler
{
    public static function register(Logger $logger): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ALL);
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        set_exception_handler(static function (Throwable $error) use ($logger): void {
            $reference = bin2hex(random_bytes(8));
            $logger->exception($error, $reference);
            if (PHP_SAPI === 'cli') {
                fwrite(STDERR, "Application error; reference={$reference}\n");
                exit(1);
            }
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/plain; charset=UTF-8');
                header('Cache-Control: no-store');
                header('X-Robots-Tag: noindex, nofollow');
            }
            echo 'Внутренняя ошибка. Код обращения: ' . $reference;
        });
    }
}
