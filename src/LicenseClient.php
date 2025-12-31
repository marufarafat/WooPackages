<?php

declare(strict_types=1);

namespace WooPackages;

use GuzzleHttp\Client;
use Throwable;

final class LicenseClient
{
    private const LICENSE_SERVER_URL = 'http://licensemanagement.test';

    private const LICENSE_SERVER_ENDPOINT = '/api/licenses/verify';

    public static function serverUrl(): string
    {
        return self::LICENSE_SERVER_URL . self::LICENSE_SERVER_ENDPOINT;
    }

    public function verify(string $licenseKey, string $domain): array
    {
        $client = new Client([
            'timeout' => 5,
            'http_errors' => false,
        ]);

        try {
            $response = $client->post(self::serverUrl(), [
                'headers' => [
                    'X-License-Key' => $licenseKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'activation' => $domain,
                ],
            ]);
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'response' => null,
                'error' => $exception->getMessage() !== '' ? $exception->getMessage() : 'License server unreachable.',
            ];
        }

        $decoded = json_decode((string) $response->getBody(), true);

        if (!is_array($decoded)) {
            return [
                'success' => false,
                'response' => null,
                'error' => 'Invalid response from license server.',
            ];
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            return [
                'success' => true,
                'response' => $decoded,
                'error' => null,
            ];
        }

        return [
            'success' => true,
            'response' => $decoded,
            'error' => null,
        ];
    }
}
