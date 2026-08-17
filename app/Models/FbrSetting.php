<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FbrSetting extends Model
{
    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'authority_type',
        'is_enabled',
        'environment',
        'pos_id',
        'ntn',
        'strn',
        'business_name',
        'branch_name',
        'api_token',
        'base_url',
        'auto_sync',
        'sync_sales',
        'sync_purchases',
        'sync_transactions',
        'sync_payments',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'auto_sync' => 'boolean',
        'sync_sales' => 'boolean',
        'sync_purchases' => 'boolean',
        'sync_transactions' => 'boolean',
        'sync_payments' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get or create the settings instance for the given company and authority type.
     */
    public static function getSettings(?int $companyId = null, string $authorityType = 'fbr'): self
    {
        $companyId = $companyId ?? (auth()->check() ? auth()->user()->current_company_id : null);

        if (!$companyId) {
            $companyId = Company::first()?->id;
        }

        if (!$companyId) {
            return new self();
        }

        $settings = static::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('authority_type', $authorityType)
            ->first();

        if (!$settings) {
            $company = Company::find($companyId);
            
            $defaultBaseUrls = [
                'fbr' => 'https://sandbox.fbr.gov.pk/api/v1',
                'pra' => 'https://e.pra.punjab.gov.pk/api/v1',
                'srb' => 'https://e.srb.gos.pk/api/v1',
                'kpra' => 'https://kpra.kp.gov.pk/api/v1',
                'bra' => 'https://bra.gob.pk/api/v1',
            ];

            $settings = static::withoutGlobalScopes()->create([
                'company_id' => $companyId,
                'authority_type' => $authorityType,
                'is_enabled' => false,
                'environment' => 'sandbox',
                'pos_id' => '100001',
                'ntn' => '1234567-8',
                'strn' => '3277876543210',
                'business_name' => $company ? ($company->company_name ?? 'POS Enterprise') : 'POS Enterprise',
                'branch_name' => 'Main Branch',
                'base_url' => $defaultBaseUrls[$authorityType] ?? 'https://sandbox.fbr.gov.pk/api/v1',
                'auto_sync' => true,
                'sync_sales' => true,
                'sync_purchases' => true,
                'sync_transactions' => true,
                'sync_payments' => true,
            ]);
        }

        return $settings;
    }
}
