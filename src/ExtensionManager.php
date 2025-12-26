<?php

declare(strict_types=1);

namespace License\Enforcement;

final class ExtensionManager
{
    public static function enabled(string $name): bool
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }

        $cache = new LicenseCache();
        $cacheData = $cache->read();
        if (!is_array($cacheData)) {
            return false;
        }

        $response = $cacheData['response'] ?? null;
        if (!is_array($response)) {
            return false;
        }

        $extensions = $response['acknowledgement']['extensions'] ?? null;
        if (!is_array($extensions)) {
            return false;
        }

        foreach ($extensions as $extension) {
            if (!is_array($extension)) {
                continue;
            }

            if (($extension['name'] ?? null) === $name) {
                return ($extension['is_enabled'] ?? false) === true;
            }
        }

        return false;
    }
}
