<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use License\Enforcement\ExtensionManager;
use License\Enforcement\LicenseEnforcer;

// Simple .env loader for local testing.
$envPath = __DIR__ . '/.env';
if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// MUST be the first executable line
LicenseEnforcer::boot();

echo "License check passed.<br>";
echo ExtensionManager::enabled('test') ? "Extension 'test' is enabled.<br>" : "Extension 'test' is disabled.<br>";
echo ExtensionManager::enabled('test 2') ? "Extension 'test 2' is enabled.<br>" : "Extension 'test 2' is disabled.<br>";
echo ExtensionManager::enabled('test 3') ? "Extension 'test 3' is enabled.<br>" : "Extension 'test 3' is disabled.<br>";
