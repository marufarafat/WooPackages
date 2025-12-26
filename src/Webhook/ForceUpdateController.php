<?php

declare(strict_types=1);

namespace License\Enforcement\Webhook;

use License\Enforcement\Blocker;
use License\Enforcement\LicenseCache;
use License\Enforcement\LicenseClient;
use License\Enforcement\LicenseValidator;
use License\Enforcement\Support\DomainResolver;
use License\Enforcement\Support\Env;

final class ForceUpdateController
{
    /** @var callable|null */
    private $responder;
    /** @var callable|null */
    private $verifier;
    /** @var callable|null */
    private $domainResolver;

    public function __construct(?callable $responder = null, ?callable $verifier = null, ?callable $domainResolver = null)
    {
        $this->responder = $responder;
        $this->verifier = $verifier;
        $this->domainResolver = $domainResolver;
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

        $domain = $this->resolveDomain();
        $result = $this->verifyLicense($licenseKey, $domain);

        if ($result['success'] !== true || !is_array($result['response'])) {
            $this->respond(503, 'License server unreachable.');
            return;
        }

        $response = $result['response'];
        $validator = new LicenseValidator();
        if (!$validator->isValid($response)) {
            $this->respond(403, $validator->getMessage($response));
            return;
        }

        $cache->write($response, $this->nextCheckAt());
        $this->respond(200, 'License cache refreshed.');
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

    private function verifyLicense(string $licenseKey, string $domain): array
    {
        if (is_callable($this->verifier)) {
            return ($this->verifier)($licenseKey, $domain);
        }

        $client = new LicenseClient();
        return $client->verify($licenseKey, $domain);
    }

    private function resolveDomain(): string
    {
        if (is_callable($this->domainResolver)) {
            return (string) ($this->domainResolver)();
        }

        return DomainResolver::resolve();
    }

    private function nextCheckAt(): int
    {
        $days = random_int(1, 7);
        return time() + ($days * 86400);
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
