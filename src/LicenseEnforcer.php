<?php

declare(strict_types=1);

namespace WooPackages;

use WooPackages\Support\DomainResolver;
use WooPackages\Support\Env;

final class LicenseEnforcer
{
    public static function boot(): void
    {
        $licenseKey = Env::get('LICENSE_KEY');
        if ($licenseKey === null) {
            Blocker::block(Blocker::MESSAGE_MISSING_LICENSE_KEY);
        }

        $domain = DomainResolver::resolve();
        $cache = new LicenseCache();
        $validator = new LicenseValidator();

        $cacheData = $cache->read();
        $response = null;
        $now = time();

        if (is_array($cacheData)) {
            $nextCheckAt = $cacheData['next_check_at'] ?? null;
            $cachedResponse = $cacheData['response'] ?? null;

            if (is_int($nextCheckAt) && $now < $nextCheckAt && is_array($cachedResponse)) {
                $response = $cachedResponse;
            }
        }

        if ($response === null) {
            $client = new LicenseClient();
            $result = $client->verify($licenseKey, $domain);

            if ($result['success'] === true && is_array($result['response'])) {
                $response = $result['response'];
                if (self::shouldCacheResponse($response)) {
                    $cache->write($response, self::nextCheckAt());
                }
            } elseif (is_array($cacheData) && is_array($cacheData['response'] ?? null)) {
                $response = $cacheData['response'];
                if (!$validator->isValid($response)) {
                    Blocker::block($validator->getMessage($response));
                }

                return;
            } else {
                Blocker::block(Blocker::MESSAGE_SERVER_UNREACHABLE);
            }
        }

        if (!$validator->isValid($response)) {
            Blocker::block($validator->getMessage($response));
        }
    }

    private static function nextCheckAt(): int
    {
        $days = random_int(1, 7);
        return time() + ($days * 86400);
    }

    protected static function shouldCacheResponse(array $response): bool
    {
        return ($response['status'] ?? null) === true;
    }
}
