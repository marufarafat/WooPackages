<?php

declare(strict_types=1);

namespace License\Enforcement\Support;

final class DomainResolver
{
    public static function resolve(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $host = trim((string) $host);

        if ($host === '') {
            return 'localhost';
        }

        $host = strtolower($host);
        $parts = explode(':', $host, 2);
        return $parts[0] !== '' ? $parts[0] : 'localhost';
    }
}
