<?php

declare(strict_types=1);

namespace WooPackages\Tests;

use PHPUnit\Framework\TestCase;
use WooPackages\Entitlements\Cache;
use WooPackages\Entitlements\ExtensionManager;

final class ExtensionManagerTest extends TestCase
{
    private Cache $cache;

    protected function setUp(): void
    {
        $this->cache = new Cache();
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
