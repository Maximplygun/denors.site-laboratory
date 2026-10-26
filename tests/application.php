<?php
declare(strict_types=1);

use Denors\Application;
use Denors\Support\Environment;
use Denors\Support\Logger;

require dirname(__DIR__) . '/bootstrap/autoload.php';

$directory = sys_get_temp_dir() . '/denors-app-test-' . bin2hex(random_bytes(6));
mkdir($directory, 0700);
$checks = 0;
$assert = static function (bool $condition, string $label) use (&$checks): void {
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $label);
    }
    ++$checks;
};
$variable = 'DENORS_BOOTSTRAP_TEST';
$original = getenv($variable);
try {
    $file = $directory . '/environment.txt';
    file_put_contents($file, "# literal values\n{$variable}=\"value=1 # literal\"\n");
    $env = Environment::load($file);
    $assert($env->get($variable) === 'value=1 # literal', 'literal environment value preserved');
    putenv($variable . '=process-override');
    $assert($env->get($variable) === 'process-override', 'process overrides file');
    file_put_contents($file, "TOKEN=private\nTOKEN=other\n");
    try {
        Environment::load($file);
        throw new LogicException('Duplicate accepted');
    } catch (RuntimeException $error) {
        $assert(str_contains($error->getMessage(), 'Duplicate environment key'), 'duplicate rejected');
        $assert(!str_contains($error->getMessage(), 'private'), 'parser error does not expose value');
    }
    $app = new Application(dirname(__DIR__) . '/resources/views');
    $home = $app->handle('GET', '/?source=test');
    $assert($home->status === 200 && str_contains($home->body, '<h1>'), 'home renders via view');
    $assert(str_contains($home->headers['X-Robots-Tag'], 'noindex'), 'staging is not indexable');
    $assert($app->handle('GET', '/not-imported/')->status === 404, 'unknown legacy path stays 404');
    $assert($app->handle('GET', '/app/Support/Logger.php')->status === 404, 'private path not routed');
    $post = $app->handle('POST', '/');
    $assert($post->status === 405 && $post->headers['Allow'] === 'GET, HEAD', 'unsupported method refused');
    ob_start();
    $app->handle('HEAD', '/')->send(true);
    $head = ob_get_clean();
    $assert($head === '', 'HEAD sends no body');

    $logger = new Logger($directory . '/logs');
    $logger->exception(new RuntimeException('password=DO_NOT_LOG_THIS'), 'test-reference');
    $log = file_get_contents($directory . '/logs/application-' . gmdate('Y-m-d') . '.jsonl');
    $assert(!str_contains($log, 'DO_NOT_LOG_THIS'), 'secret exception message excluded from log');
    $record = json_decode($log, true, flags: JSON_THROW_ON_ERROR);
    $assert($record['reference'] === 'test-reference', 'log correlation preserved');
    $child = $directory . '/error-child.php';
    $code = '<?php require ' . var_export(dirname(__DIR__) . '/bootstrap/autoload.php', true)
        . '; Denors\\Support\\ErrorHandler::register(new Denors\\Support\\Logger('
        . var_export($directory . '/logs', true) . ')); throw new RuntimeException("SECRET_ERROR_VALUE");';
    file_put_contents($child, $code);
    $process = proc_open([PHP_BINARY, $child], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start error handler test');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    $assert($exitCode === 1, 'unhandled CLI exception fails the process');
    $assert($stdout === '' && str_contains($stderr, 'reference='), 'safe CLI error reference');
    $assert(!str_contains($stderr, 'SECRET_ERROR_VALUE'), 'CLI does not expose secret error');
    echo "PASS: {$checks} application checks.\n";
} finally {
    putenv($original === false ? $variable : $variable . '=' . $original);
    foreach (glob($directory . '/logs/*') ?: [] as $file) {
        unlink($file);
    }
    if (is_dir($directory . '/logs')) {
        rmdir($directory . '/logs');
    }
    if (is_file($directory . '/environment.txt')) {
        unlink($directory . '/environment.txt');
    }
    if (is_file($directory . '/error-child.php')) {
        unlink($directory . '/error-child.php');
    }
    rmdir($directory);
}
