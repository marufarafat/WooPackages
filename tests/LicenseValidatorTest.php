<?php

declare(strict_types=1);

namespace License\Enforcement\Tests;

use License\Enforcement\LicenseValidator;
use PHPUnit\Framework\TestCase;

final class LicenseValidatorTest extends TestCase
{
    public function testValidLicensePasses(): void
    {
        $validator = new LicenseValidator();
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
        $validator = new LicenseValidator();
        $response = [
            'message' => 'no status',
        ];

        self::assertFalse($validator->isValid($response));
    }

    public function testInactiveLicenseFails(): void
    {
        $validator = new LicenseValidator();
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

    public function testExpiredLicenseFails(): void
    {
        $validator = new LicenseValidator();
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
        $validator = new LicenseValidator();
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
