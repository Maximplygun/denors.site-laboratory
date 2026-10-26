<?php
declare(strict_types=1);

// $environment is supplied by bootstrap/app.php; configuration has no side effects.
return [
    'environment' => $environment->get('APP_ENV', 'staging'),
];
