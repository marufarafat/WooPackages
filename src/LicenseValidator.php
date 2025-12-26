<?php

declare(strict_types=1);

namespace License\Enforcement;

final class LicenseValidator
{
    public function isValid(array $response): bool
    {
        if (!array_key_exists('status', $response)) {
            return false;
        }

        if ($response['status'] !== true) {
            return false;
        }

        if (!isset($response['acknowledgement']) || !is_array($response['acknowledgement'])) {
            return false;
        }

        $license = $response['acknowledgement']['license'] ?? null;
        if (!is_array($license)) {
            return false;
        }

        if (($license['status'] ?? null) !== 'active') {
            return false;
        }

        $expiresAt = $license['expires_at'] ?? null;
        if (is_string($expiresAt) && trim($expiresAt) !== '') {
            $timestamp = strtotime($expiresAt);
            if ($timestamp === false) {
                return false;
            }

            if ($timestamp < time()) {
                return false;
            }
        }

        return true;
    }

    public function getMessage(array $response): string
    {
        $message = $response['message'] ?? null;
        if (!is_string($message) || trim($message) === '') {
            return 'License verification failed.';
        }

        return $message;
    }
}
