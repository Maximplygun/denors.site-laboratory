<?php
declare(strict_types=1);

use Denors\Application;
use Denors\Support\Environment;
use Denors\Support\ErrorHandler;
use Denors\Support\Logger;

require_once __DIR__ . '/autoload.php';

$root = dirname(__DIR__);
ErrorHandler::register(new Logger($root . '/storage/logs'));
$environment = Environment::load($root . '/.env');
$settings = require $root . '/config/app.php';

// Production is deliberately unavailable until the approved release stage.
if (!in_array($settings['environment'], ['local', 'staging'], true)) {
    throw new RuntimeException('This build is restricted to local and staging.');
}
date_default_timezone_set('UTC');
return new Application($root . '/resources/views');
