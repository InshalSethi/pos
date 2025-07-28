<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSettings extends Model
{
    protected $fillable = [
        'user_id',
        'email_notifications',
        'sales_alerts',
        'low_stock_alerts',
        'theme',
        'items_per_page',
        'default_payment_method',
        'auto_print_receipts',
        'sound_effects',
        'session_timeout',
        'two_factor_auth',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'sales_alerts' => 'boolean',
        'low_stock_alerts' => 'boolean',
        'auto_print_receipts' => 'boolean',
        'sound_effects' => 'boolean',
        'two_factor_auth' => 'boolean',
        'items_per_page' => 'integer',
        'session_timeout' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
