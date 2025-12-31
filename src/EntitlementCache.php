<?php

declare(strict_types=1);

namespace WooPackages;

final class EntitlementCache
{
    private const CACHE_FILE = 'storage/license.cache';

    public function read(): ?array
    {
        $path = $this->getCachePath();
        if (!is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false || trim($contents) === '') {
            return null;
        }

        $data = json_decode($contents, true);
        if (!is_array($data)) {
            return null;
        }

        return $data;
    }

    public function write(array $response, int $nextCheckAt): void
    {
        $payload = [
            'cached_at' => time(),
            'next_check_at' => $nextCheckAt,
            'response' => $response,
        ];

        $path = $this->getCachePath();
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, json_encode($payload), LOCK_EX);
    }

    public function clear(): void
    {
        $path = $this->getCachePath();
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function getCachePath(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . self::CACHE_FILE;
    }
}
