<?php

declare(strict_types=1);

namespace WooPackages\Tests;

use PHPUnit\Framework\TestCase;
use WooPackages\Entitlements\Validator;

final class EntitlementValidatorTest extends TestCase
{
    public function testValidEntitlementPasses(): void
    {
        $validator = new Validator();
        $response = [
            'status' => true,
            'message' => 'ok',
            'acknowledgement' => [
                'license' => [
                    'status' => 'active',
                ],
            ],
        ];

        self::assertTrue($validator->isValid($response));
    }

    public function testMissingStatusFails(): void
    {
        $validator = new Validator();
        $response = [
            'message' => 'no status',
        ];

        self::assertFalse($validator->isValid($response));
    }

    public function testInactiveEntitlementFails(): void
    {
        $validator = new Validator();
        $response = [
            'status' => true,
            'message' => 'inactive',
            'acknowledgement' => [
                'license' => [
                    'status' => 'inactive',
                ],
            ],
        ];

        self::assertFalse($validator->isValid($response));
    }

    public function testExpiredEntitlementFails(): void
    {
        $validator = new Validator();
        $response = [
            'status' => true,
            'message' => 'expired',
            'acknowledgement' => [
                'license' => [
                    'status' => 'active',
                    'expires_at' => '2000-01-01T00:00:00+00:00',
                ],
            ],
        ];

        self::assertFalse($validator->isValid($response));
    }

    public function testFutureExpirationPasses(): void
    {
        $validator = new Validator();
        $response = [
            'status' => true,
            'message' => 'future',
            'acknowledgement' => [
                'license' => [
                    'status' => 'active',
                    'expires_at' => '2999-01-01T00:00:00+00:00',
                ],
            ],
        ];

        self::assertTrue($validator->isValid($response));
    }
}
