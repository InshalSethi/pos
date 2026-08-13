<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleCalendarSetting extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'is_synced',
        'calendar_id',
        'google_account_email',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'last_synced_at',
        'sync_sales',
        'sync_purchases',
        'sync_payments',
        'sync_receipts',
        'sync_expenses',
    ];

    protected $casts = [
        'is_synced' => 'boolean',
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'sync_sales' => 'boolean',
        'sync_purchases' => 'boolean',
        'sync_payments' => 'boolean',
        'sync_receipts' => 'boolean',
        'sync_expenses' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
