<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use License\Enforcement\Webhook\ForceUpdateController;

$controller = new ForceUpdateController();
$controller->handle();
