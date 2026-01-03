<?php

declare(strict_types=1);

namespace WooPackages\Tests;

use PHPUnit\Framework\TestCase;
use WooPackages\Entitlements\Cache;
use WooPackages\Webhook\ForceUpdateController;

final class ForceUpdateControllerTest extends TestCase
{
    private array $response;

    protected function setUp(): void
    {
        $this->response = [];
        putenv('LICENSE_KEY=test-key');
        $_ENV['LICENSE_KEY'] = 'test-key';
        $_SERVER['HTTP_X_LICENSE_KEY'] = 'test-key';
        $_SERVER['REMOTE_ADDR'] = gethostbyname('licensemanagement.test');
        (new Cache())->clear();
    }

    protected function tearDown(): void
    {
        putenv('LICENSE_KEY');
        unset($_ENV['LICENSE_KEY'], $_SERVER['HTTP_X_LICENSE_KEY'], $_SERVER['REMOTE_ADDR']);
        (new Cache())->clear();
    }

    public function testRejectsWhenEntitlementKeyMissing(): void
    {
        putenv('LICENSE_KEY');
        unset($_ENV['LICENSE_KEY']);

        $controller = new ForceUpdateController(function (int $status, string $message): void {
            $this->response = ['status' => $status, 'message' => $message];
        });

        $controller->handle();

        self::assertSame(403, $this->response['status']);
        self::assertSame('License key is missing.', $this->response['message']);
    }

    public function testRejectsWhenHeaderMissing(): void
    {
        unset($_SERVER['HTTP_X_LICENSE_KEY']);

        $controller = new ForceUpdateController(function (int $status, string $message): void {
            $this->response = ['status' => $status, 'message' => $message];
        });

        $controller->handle();

        self::assertSame(403, $this->response['status']);
        self::assertSame('Unauthorized request.', $this->response['message']);
    }

    public function testRejectsWhenOriginInvalid(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';

        $controller = new ForceUpdateController(function (int $status, string $message): void {
            $this->response = ['status' => $status, 'message' => $message];
        });

        $controller->handle();

        self::assertSame(403, $this->response['status']);
        self::assertSame('Unauthorized request.', $this->response['message']);
    }

    public function testRespondsUnreachableWhenVerificationFails(): void
    {
        $cache = new Cache();
        $cache->write(['status' => true], time() + 3600);

        $verifier = function (): array {
            return [
                'success' => false,
                'response' => null,
                'error' => 'unreachable',
            ];
        };

        $controller = new ForceUpdateController(
            function (int $status, string $message): void {
                $this->response = ['status' => $status, 'message' => $message];
            },
            $verifier
        );

        $controller->handle();

        self::assertSame(503, $this->response['status']);
        self::assertSame('License server unreachable.', $this->response['message']);
        self::assertNull($cache->read());
    }

    public function testRejectsInvalidEntitlementResponse(): void
    {
        $verifier = function (): array {
            return [
                'success' => true,
                'response' => [
                    'status' => false,
                    'message' => 'License is not active.',
                    'acknowledgement' => [
                        'license' => [
                            'status' => 'revoked',
                            'expires_at' => '2999-01-01T00:00:00+00:00',
                        ],
                        'extensions' => [],
                    ],
                ],
                'error' => null,
            ];
        };

        $controller = new ForceUpdateController(
            function (int $status, string $message): void {
                $this->response = ['status' => $status, 'message' => $message];
            },
            $verifier
        );

        $controller->handle();

        self::assertSame(403, $this->response['status']);
        self::assertSame('License is not active.', $this->response['message']);
        self::assertNull((new Cache())->read());
    }

    public function testRefreshesCacheOnValidResponse(): void
    {
        $response = [
            'status' => true,
            'message' => 'License verified successfully',
            'acknowledgement' => [
                'license' => [
                    'status' => 'active',
                    'expires_at' => '2999-01-01T00:00:00+00:00',
                ],
                'extensions' => [
                    [
                        'name' => 'test',
                        'is_enabled' => true,
                    ],
                ],
            ],
        ];

        $verifier = function () use ($response): array {
            return [
                'success' => true,
                'response' => $response,
                'error' => null,
            ];
        };

        $controller = new ForceUpdateController(
            function (int $status, string $message): void {
                $this->response = ['status' => $status, 'message' => $message];
            },
            $verifier
        );

        $controller->handle();

        self::assertSame(200, $this->response['status']);
        self::assertSame('License cache refreshed.', $this->response['message']);

        $cacheData = (new Cache())->read();
        self::assertIsArray($cacheData);
        self::assertSame($response, $cacheData['response']);
        self::assertGreaterThan(time(), $cacheData['next_check_at']);
    }
}
