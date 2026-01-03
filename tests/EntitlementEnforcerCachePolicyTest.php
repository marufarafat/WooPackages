<?php

declare(strict_types=1);

namespace WooPackages\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use WooPackages\Entitlements\Enforcer;

final class EntitlementEnforcerCachePolicyTest extends TestCase
{
    public function testShouldCacheResponseOnlyWhenStatusTrue(): void
    {
        $method = new ReflectionMethod(Enforcer::class, 'shouldCacheResponse');
        $method->setAccessible(true);

        self::assertTrue($method->invoke(null, ['status' => true]));
        self::assertFalse($method->invoke(null, ['status' => false]));
        self::assertFalse($method->invoke(null, ['status' => 'true']));
        self::assertFalse($method->invoke(null, []));
    }
}
