<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'date_of_birth',
        'gender',
        'tax_number',
        'notes',
        'is_active',
        'credit_limit',
        'total_purchases',
        'loyalty_points',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'loyalty_points' => 'decimal:2',
    ];

    // Relationships
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function journalEntryLines(): MorphMany
    {
        return $this->morphMany(JournalEntryLine::class, 'partner');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithPurchases($query)
    {
        return $query->whereHas('sales');
    }

    public function scopeHighValue($query, $threshold = 10000)
    {
        return $query->where('total_purchases', '>=', $threshold);
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        $address = collect([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ])->filter()->implode(', ');

        return $address;
    }

    public function getFormattedTotalPurchasesAttribute(): string
    {
        return '$' . number_format($this->total_purchases, 2);
    }

    public function getFormattedCreditLimitAttribute(): string
    {
        return '$' . number_format($this->credit_limit, 2);
    }

    public function getFormattedLoyaltyPointsAttribute(): string
    {
        return number_format($this->loyalty_points, 0);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getCustomerSinceAttribute(): string
    {
        return $this->created_at->format('M Y');
    }

    // Methods
    public function getTotalSales(): int
    {
        return $this->sales()->count();
    }

    public function getAverageOrderValue(): float
    {
        $totalSales = $this->getTotalSales();
        return $totalSales > 0 ? $this->total_purchases / $totalSales : 0;
    }

    public function getLastPurchaseDate(): ?string
    {
        $lastSale = $this->sales()->latest('sale_date')->first();
        return $lastSale ? $lastSale->sale_date->format('Y-m-d') : null;
    }

    public function getCreditUtilization(): float
    {
        if ($this->credit_limit <= 0) {
            return 0;
        }

        $outstandingBalance = $this->getOutstandingBalance();
        return ($outstandingBalance / $this->credit_limit) * 100;
    }

    public function getOutstandingBalance(): float
    {
        // Calculate outstanding balance from unpaid sales
        return $this->sales()
                   ->where('status', 'pending')
                   ->sum('total_amount');
    }

    public function getAvailableCredit(): float
    {
        return max(0, $this->credit_limit - $this->getOutstandingBalance());
    }

    public function canPurchase(float $amount): bool
    {
        return $this->is_active && ($this->getAvailableCredit() >= $amount);
    }

    public function addLoyaltyPoints(float $purchaseAmount): void
    {
        // 1 point per dollar spent (you can customize this logic)
        $points = floor($purchaseAmount);
        $this->increment('loyalty_points', $points);
    }

    public function redeemLoyaltyPoints(float $points): bool
    {
        if ($this->loyalty_points >= $points) {
            $this->decrement('loyalty_points', $points);
            return true;
        }
        return false;
    }

    // Static methods
    public static function getTopCustomers(int $limit = 10)
    {
        return static::orderBy('total_purchases', 'desc')
                    ->limit($limit)
                    ->get();
    }

    public static function getNewCustomersThisMonth()
    {
        return static::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();
    }
}
