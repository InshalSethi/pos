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
     * Get or create the FBR settings instance for the given company or current active company.
     */
    public static function getSettings(?int $companyId = null): self
    {
        $companyId = $companyId ?? (auth()->check() ? auth()->user()->current_company_id : null);

        if (!$companyId) {
            $companyId = Company::first()?->id;
        }

        if (!$companyId) {
            return new self();
        }

        $settings = static::withoutGlobalScopes()->where('company_id', $companyId)->first();

        if (!$settings) {
            $company = Company::find($companyId);
            $settings = static::withoutGlobalScopes()->create([
                'company_id' => $companyId,
                'is_enabled' => false,
                'environment' => 'sandbox',
                'pos_id' => '100001',
                'ntn' => '1234567-8',
                'strn' => '3277876543210',
                'business_name' => $company ? ($company->company_name ?? 'POS Enterprise') : 'POS Enterprise',
                'branch_name' => 'Main Branch',
                'base_url' => 'https://sandbox.fbr.gov.pk/api/v1',
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
