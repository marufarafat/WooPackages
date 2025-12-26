<?php

declare(strict_types=1);

namespace License\Enforcement;

final class Blocker
{
    public const MESSAGE_MISSING_LICENSE_KEY = 'License key is missing.';
    public const MESSAGE_SERVER_UNREACHABLE = 'License server unreachable.';

    public static function block(string $message): void
    {
        if (!headers_sent()) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=UTF-8');
        }

        echo $message;
        exit(1);
    }
}
