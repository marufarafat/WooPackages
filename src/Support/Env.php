<?php

declare(strict_types=1);

namespace WooPackages\Support;

final class Env
{
    public static function get(string $key): ?string
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            if (array_key_exists($key, $_ENV)) {
                $value = $_ENV[$key];
            } elseif (array_key_exists($key, $_SERVER)) {
                $value = $_SERVER[$key];
            } else {
                return null;
            }
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
