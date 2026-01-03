<?php

declare(strict_types=1);

namespace WooPackages\Entitlements;

final class Blocker
{
    public const MESSAGE_MISSING_LICENSE_KEY = 'License key is missing.';
    public const MESSAGE_SERVER_UNREACHABLE = 'License server unreachable.';

    public static function block(string $message): void
    {
        if (!headers_sent()) {
            http_response_code(403);
            header('Content-Type: text/html; charset=UTF-8');
        }

        echo self::renderPage($message);
        exit(1);
    }

    private static function renderPage(string $message): string
    {
        $templatePath = dirname(__DIR__) . '/templates/blocker.php';
        ob_start();
        require $templatePath;
        return (string) ob_get_clean();
    }
}
