<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_key',
        'device_id',
        'plan',
        'status',
        'start_date',
        'expires_at',
        'last_opened_at',
        'features',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expires_at' => 'date',
        'last_opened_at' => 'datetime',
        'features' => 'array',
    ];

    /**
     * Generate a securely encrypted license key encoding user email, plan, start_date, and expires_at.
     */
    public static function generateEncryptedKey(string $email, string $plan, $startDate, $expiresAt): string
    {
        $payload = [
            'email'      => strtolower(trim($email)),
            'plan'       => strtolower(trim($plan)),
            'start_date' => Carbon::parse($startDate)->toDateString(),
            'expires_at' => Carbon::parse($expiresAt)->toDateString(),
            'issued_at'  => now()->toIso8601String(),
        ];

        return Crypt::encryptString(json_encode($payload));
    }

    /**
     * Decrypt an encrypted license key payload.
     * Returns null if key is invalid, tampered, or unreadable.
     */
    public static function decryptKey(string $encryptedKey): ?array
    {
        try {
            $json = Crypt::decryptString($encryptedKey);
            $payload = json_decode($json, true);
            if (is_array($payload) && isset($payload['email'], $payload['plan'], $payload['start_date'], $payload['expires_at'])) {
                return $payload;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper to provision/update an encrypted license for a user/plan.
     */
    public static function provisionLicense(string $email, string $plan = 'basic', $startDate = null, $expiresAt = null): self
    {
        $startDate = $startDate ? Carbon::parse($startDate)->toDateString() : now()->toDateString();
        $expiresAt = $expiresAt ? Carbon::parse($expiresAt)->toDateString() : now()->addYear()->toDateString();

        $encryptedKey = static::generateEncryptedKey($email, $plan, $startDate, $expiresAt);

        return static::updateOrCreate(
            ['id' => 1],
            [
                'license_key'    => $encryptedKey,
                'device_id'     => 'CLOUD-AUTHENTICATED',
                'plan'          => strtolower($plan),
                'status'        => 'active',
                'start_date'    => $startDate,
                'expires_at'    => $expiresAt,
                'last_opened_at' => now(),
            ]
        );
    }
}
