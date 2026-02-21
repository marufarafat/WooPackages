<?php

declare(strict_types=1);

namespace WooPackages\Entitlements;

final class ExtensionManager
{
    /**
     * Checks if an extension exists by its ID and is enabled.
     *
     * @param string $id
     * @return bool
     */
    public static function has(string $id): bool
    {
        $id = trim($id);
        if ($id === '') {
            return false;
        }

        $extensions = self::list();

        foreach ($extensions as $extension) {
            if (($extension['product_id'] ?? null) === $id) {
                return ($extension['is_enabled'] ?? false) === true;
            }
        }

        return false;
    }

    /**
     * Gets the limit for a specific extension by its ID.
     *
     * @param string $id
     * @return int
     */
    public static function limit(string $id): int
    {
        $id = trim($id);
        if ($id === '') {
            return 0;
        }

        $extensions = self::list();

        foreach ($extensions as $extension) {
            if (($extension['product_id'] ?? null) === $id) {
                return (int) ($extension['limit'] ?? 0);
            }
        }

        return 0;
    }

    /**
     * Returns the full list of extensions from the license cache.
     *
     * @return array
     */
    public static function list(): array
    {
        $cache = new Cache();
        $cacheData = $cache->read();
        
        if (!is_array($cacheData)) {
            return [];
        }

        $response = $cacheData['response'] ?? null;
        if (!is_array($response)) {
            return [];
        }

        $extensions = $response['acknowledgement']['extensions'] ?? null;
        if (!is_array($extensions)) {
            return [];
        }

        // Filter out any invalid items
        return array_filter($extensions, function ($ext) {
            return is_array($ext);
        });
    }
}
