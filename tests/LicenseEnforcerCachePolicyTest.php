<?php

declare(strict_types=1);

namespace License\Enforcement\Tests;

use License\Enforcement\LicenseEnforcer;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class LicenseEnforcerCachePolicyTest extends TestCase
{
    public function testShouldCacheResponseOnlyWhenStatusTrue(): void
    {
        $method = new ReflectionMethod(LicenseEnforcer::class, 'shouldCacheResponse');
        $method->setAccessible(true);

        self::assertTrue($method->invoke(null, ['status' => true]));
        self::assertFalse($method->invoke(null, ['status' => false]));
        self::assertFalse($method->invoke(null, ['status' => 'true']));
        self::assertFalse($method->invoke(null, []));
    }
}
