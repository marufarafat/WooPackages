<?php

declare(strict_types=1);

namespace License\Enforcement\Tests;

use License\Enforcement\Support\DomainResolver;
use PHPUnit\Framework\TestCase;

final class DomainResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']);
    }

    public function testResolvesHostFromHttpHost(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com:8080';
        unset($_SERVER['SERVER_NAME']);

        self::assertSame('example.com', DomainResolver::resolve());
    }

    public function testFallsBackToLocalhost(): void
    {
        unset($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']);

        self::assertSame('localhost', DomainResolver::resolve());
    }
}
