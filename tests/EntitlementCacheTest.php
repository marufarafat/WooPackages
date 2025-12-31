<?php

declare(strict_types=1);

namespace WooPackages\Tests;

use WooPackages\EntitlementCache;
use PHPUnit\Framework\TestCase;

final class EntitlementCacheTest extends TestCase
{
    private EntitlementCache $cache;

    protected function setUp(): void
    {
        $this->cache = new EntitlementCache();
        $this->cache->clear();
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
    }

    public function testWriteAndRead(): void
    {
        $response = [
            'status' => true,
        ];
        $nextCheckAt = time() + 3600;

        $this->cache->write($response, $nextCheckAt);
        $data = $this->cache->read();

        self::assertIsArray($data);
        self::assertSame($response, $data['response']);
        self::assertSame($nextCheckAt, $data['next_check_at']);
    }

    public function testClearRemovesCache(): void
    {
        $this->cache->write(['status' => true], time() + 3600);
        $this->cache->clear();

        self::assertNull($this->cache->read());
    }
}
