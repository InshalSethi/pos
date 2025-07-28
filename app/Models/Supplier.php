<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_number',
        'website',
        'notes',
        'is_active',
        'credit_limit',
        'payment_terms_days',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
    ];

    // Relationships
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
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

    public function scopeWithOrders($query)
    {
        return $query->whereHas('purchaseOrders');
    }

    public function scopeHighValue($query, $threshold = 50000)
    {
        return $query->where('credit_limit', '>=', $threshold);
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

    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?: $this->name;
    }

    public function getFormattedCreditLimitAttribute(): string
    {
        return '$' . number_format($this->credit_limit, 2);
    }

    public function getPaymentTermsDisplayAttribute(): string
    {
        return $this->payment_terms_days . ' days';
    }

    public function getSupplierSinceAttribute(): string
    {
        return $this->created_at->format('M Y');
    }

    // Methods
    public function getTotalPurchaseOrders(): int
    {
        return $this->purchaseOrders()->count();
    }

    public function getTotalPurchaseValue(): float
    {
        return $this->purchaseOrders()->sum('total_amount');
    }

    public function getAverageOrderValue(): float
    {
        $totalOrders = $this->getTotalPurchaseOrders();
        return $totalOrders > 0 ? $this->getTotalPurchaseValue() / $totalOrders : 0;
    }

    public function getLastOrderDate(): ?string
    {
        $lastOrder = $this->purchaseOrders()->latest('order_date')->first();
        return $lastOrder ? $lastOrder->order_date->format('Y-m-d') : null;
    }

    public function getOutstandingBalance(): float
    {
        // Calculate outstanding balance from unpaid purchase orders
        return $this->purchaseOrders()
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

    public function getCreditUtilization(): float
    {
        if ($this->credit_limit <= 0) {
            return 0;
        }

        $outstandingBalance = $this->getOutstandingBalance();
        return ($outstandingBalance / $this->credit_limit) * 100;
    }

    public function isPaymentOverdue(): bool
    {
        return $this->purchaseOrders()
                   ->where('status', 'pending')
                   ->where('expected_delivery_date', '<', now()->subDays($this->payment_terms_days))
                   ->exists();
    }

    // Static methods
    public static function getTopSuppliers(int $limit = 10)
    {
        return static::withSum('purchaseOrders', 'total_amount')
                    ->orderBy('purchase_orders_sum_total_amount', 'desc')
                    ->limit($limit)
                    ->get();
    }

    public static function getNewSuppliersThisMonth()
    {
        return static::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();
    }

    public static function getSuppliersWithOverduePayments()
    {
        return static::whereHas('purchaseOrders', function ($query) {
            $query->where('status', 'pending')
                  ->whereRaw('expected_delivery_date < DATE_SUB(NOW(), INTERVAL payment_terms_days DAY)');
        })->get();
    }
}
