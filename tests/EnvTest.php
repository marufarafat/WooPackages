<?php

declare(strict_types=1);

namespace WooPackages\Tests;

use WooPackages\Support\Env;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('TEST_ENV');
        unset($_ENV['TEST_ENV'], $_SERVER['TEST_ENV']);
    }

    public function testReturnsNullWhenMissing(): void
    {
        putenv('TEST_ENV');
        unset($_ENV['TEST_ENV'], $_SERVER['TEST_ENV']);

        self::assertNull(Env::get('TEST_ENV'));
    }

    public function testReturnsValueFromEnv(): void
    {
        putenv('TEST_ENV=value');

        self::assertSame('value', Env::get('TEST_ENV'));
    }
}
