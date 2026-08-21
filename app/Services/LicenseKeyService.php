<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LicenseKeyService
{
    /**
     * Secret key used for symmetric AES-256 encryption/decryption of license keys.
     */
    protected static function getSecretKey(): string
    {
        $appKey = config('app.key') ?: 'POS-SYSTEM-DESKTOP-LICENSE-SECRET-KEY-2026';
        return hash('sha256', $appKey);
    }

    /**
     * Generate an encrypted license key containing:
     * 1. User email
     * 2. Subscription plan name
     * 3. Subscription start date
     * 4. Subscription end date
     *
     * Returns a clean hex-encoded cipher string.
     */
    public static function generateEncryptedKey(string $email, string $plan, string $startDate, string $endDate): string
    {
        $payload = json_encode([
            'email'      => strtolower(trim($email)),
            'plan'       => strtolower(trim($plan)),
            'start_date' => Carbon::parse($startDate)->toDateString(),
            'end_date'   => Carbon::parse($endDate)->toDateString(),
        ], JSON_UNESCAPED_SLASHES);

        $secret = self::getSecretKey();
        $iv = openssl_random_pseudo_bytes(16);
        $cipher = openssl_encrypt($payload, 'AES-256-CBC', $secret, OPENSSL_RAW_DATA, $iv);

        return bin2hex($iv . $cipher);
    }

    /**
     * Decrypt and validate an encrypted license key.
     * Returns array with stored information if valid, or null if invalid.
     *
     * @param string|null $encryptedKey
     * @return array|null
     */
    public static function decryptKey(?string $encryptedKey): ?array
    {
        if (empty($encryptedKey) || !ctype_xdigit($encryptedKey) || strlen($encryptedKey) < 32) {
            return null;
        }

        try {
            $raw = hex2bin($encryptedKey);
            if (!$raw || strlen($raw) <= 16) {
                return null;
            }

            $secret = self::getSecretKey();
            $iv = substr($raw, 0, 16);
            $cipher = substr($raw, 16);

            $decrypted = openssl_decrypt($cipher, 'AES-256-CBC', $secret, OPENSSL_RAW_DATA, $iv);
            if (!$decrypted) {
                return null;
            }

            $data = json_decode($decrypted, true);
            if (!is_array($data)) {
                return null;
            }

            // Support dictionary format ['email'=>..., 'plan'=>..., 'start_date'=>..., 'end_date'=>...]
            if (isset($data['email'], $data['plan'], $data['start_date'])) {
                return [
                    'email'      => $data['email'],
                    'plan'       => $data['plan'],
                    'start_date' => $data['start_date'],
                    'end_date'   => $data['end_date'] ?? $data['expires_at'] ?? null,
                ];
            }

            // Support indexed format [$email, $plan, $startDate, $endDate]
            if (count($data) >= 4 && isset($data[0], $data[1], $data[2], $data[3])) {
                return [
                    'email'      => $data[0],
                    'plan'       => $data[1],
                    'start_date' => $data[2],
                    'end_date'   => $data[3],
                ];
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('License key decryption error: ' . $e->getMessage());
            return null;
        }
    }
}
