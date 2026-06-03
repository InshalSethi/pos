<?php

namespace App\Models;

use App\Traits\HasUtcDatabaseTimezones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'is_active',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'is_active'     => 'boolean',
    ];

    /**
     * Scope to only return active currencies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
