<?php

declare(strict_types=1);

namespace License\Enforcement\Tests;

use License\Enforcement\LicenseCache;
use License\Enforcement\Webhook\ForceUpdateController;
use PHPUnit\Framework\TestCase;

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
        (new LicenseCache())->clear();
    }

    protected function tearDown(): void
    {
        putenv('LICENSE_KEY');
        unset($_ENV['LICENSE_KEY'], $_SERVER['HTTP_X_LICENSE_KEY'], $_SERVER['REMOTE_ADDR']);
        (new LicenseCache())->clear();
    }

    public function testRejectsWhenLicenseKeyMissing(): void
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

    public function testClearsCacheOnValidRequest(): void
    {
        $cache = new LicenseCache();
        $cache->write(['status' => true], time() + 3600);

        $controller = new ForceUpdateController(function (int $status, string $message): void {
            $this->response = ['status' => $status, 'message' => $message];
        });

        $controller->handle();

        self::assertSame(200, $this->response['status']);
        self::assertSame('License cache cleared.', $this->response['message']);
        self::assertNull($cache->read());
    }
}
