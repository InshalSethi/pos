<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FbrEntry extends Model
{
    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'reference_type',
        'reference_id',
        'reference_number',
        'invoice_type',
        'buyer_ntn',
        'buyer_name',
        'total_quantity',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'payload',
        'response_payload',
        'fbr_invoice_number',
        'fbr_qr_code',
        'status',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'total_quantity' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'payload' => 'array',
        'response_payload' => 'array',
        'synced_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
