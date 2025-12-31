<?php

declare(strict_types=1);

namespace WooPackages\Tests;

use WooPackages\ExtensionManager;
use WooPackages\LicenseCache;
use PHPUnit\Framework\TestCase;

final class ExtensionManagerTest extends TestCase
{
    private LicenseCache $cache;

    protected function setUp(): void
    {
        $this->cache = new LicenseCache();
        $this->cache->clear();
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
    }

    public function testEnabledReturnsTrueWhenExtensionEnabled(): void
    {
        $response = [
            'status' => true,
            'acknowledgement' => [
                'license' => [
                    'status' => 'active',
                ],
                'extensions' => [
                    [
                        'name' => 'analytics',
                        'is_enabled' => true,
                    ],
                ],
            ],
        ];

        $this->cache->write($response, time() + 3600);

        self::assertTrue(ExtensionManager::enabled('analytics'));
    }

    public function testEnabledReturnsFalseWhenMissing(): void
    {
        $response = [
            'status' => true,
            'acknowledgement' => [
                'license' => [
                    'status' => 'active',
                ],
                'extensions' => [
                    [
                        'name' => 'reports',
                        'is_enabled' => false,
                    ],
                ],
            ],
        ];

        $this->cache->write($response, time() + 3600);

        self::assertFalse(ExtensionManager::enabled('analytics'));
    }
}
