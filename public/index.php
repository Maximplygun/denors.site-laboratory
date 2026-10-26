<?php
declare(strict_types=1);

// Suppress PHP diagnostics even if bootstrap itself cannot be loaded.
ini_set('display_errors', '0');
/** @var \Denors\Application $application */
$application = require dirname(__DIR__) . '/bootstrap/app.php';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$application->handle($method, $_SERVER['REQUEST_URI'] ?? '/')->send($method === 'HEAD');
