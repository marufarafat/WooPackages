<?php

declare(strict_types=1);

namespace License\Enforcement\Webhook;

use License\Enforcement\Blocker;
use License\Enforcement\LicenseCache;
use License\Enforcement\LicenseClient;
use License\Enforcement\Support\Env;

final class ForceUpdateController
{
    /** @var callable|null */
    private $responder;

    public function __construct(?callable $responder = null)
    {
        $this->responder = $responder;
    }

    public function handle(): void
    {
        $licenseKey = Env::get('LICENSE_KEY');
        if ($licenseKey === null) {
            $this->reject('License key is missing.');
            return;
        }

        $incomingKey = $this->getHeader('X-License-Key');
        if ($incomingKey === null || $incomingKey !== $licenseKey) {
            $this->reject('Unauthorized request.');
            return;
        }

        if (!$this->isFromLicenseServer()) {
            $this->reject('Unauthorized request.');
            return;
        }

        $cache = new LicenseCache();
        $cache->clear();

        $this->respond(200, 'License cache cleared.');
    }

    private function reject(string $message): void
    {
        $this->respond(403, $message);
    }

    private function respond(int $status, string $message): void
    {
        if (is_callable($this->responder)) {
            ($this->responder)($status, $message);
            return;
        }

        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: text/plain; charset=UTF-8');
        }

        echo $message;
        exit($status === 200 ? 0 : 1);
    }

    private function getHeader(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (isset($_SERVER[$key]) && trim((string) $_SERVER[$key]) !== '') {
            return trim((string) $_SERVER[$key]);
        }

        return null;
    }

    private function isFromLicenseServer(): bool
    {
        $url = LicenseClient::serverUrl();
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }

        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        $remoteAddr = trim((string) $remoteAddr);
        if ($remoteAddr === '') {
            return false;
        }

        $resolved = gethostbyname($host);
        if ($resolved === $host) {
            return false;
        }

        return $resolved === $remoteAddr;
    }
}
