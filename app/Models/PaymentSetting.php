<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_enabled',
        'stripe_public_key',
        'stripe_secret_key',
        'square_enabled',
        'square_application_id',
        'square_access_token',
        'googlepay_enabled',
        'googlepay_merchant_id',
    ];

    protected $casts = [
        'stripe_enabled' => 'boolean',
        'square_enabled' => 'boolean',
        'googlepay_enabled' => 'boolean',
    ];

    protected $hidden = [
        'stripe_secret_key',
        'square_access_token',
    ];
}
