<?php

declare(strict_types=1);

namespace WooPackages\Tests;

use WooPackages\EntitlementEnforcer;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class EntitlementEnforcerCachePolicyTest extends TestCase
{
    public function testShouldCacheResponseOnlyWhenStatusTrue(): void
    {
        $method = new ReflectionMethod(EntitlementEnforcer::class, 'shouldCacheResponse');
        $method->setAccessible(true);

        self::assertTrue($method->invoke(null, ['status' => true]));
        self::assertFalse($method->invoke(null, ['status' => false]));
        self::assertFalse($method->invoke(null, ['status' => 'true']));
        self::assertFalse($method->invoke(null, []));
    }
}
