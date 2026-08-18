<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use App\Traits\HasUtcDatabaseTimezones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use SoftDeletes;

    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'sale_number',
        'customer_id',
        'customer_phone',
        'customer_email',
        'category_id',
        'warehouse_id',
        'counter_id',
        'salesman_id',
        'user_id',
        'sale_date',
        'due_date',
        'order_number',
        'status',
        'sales_mode',
        'tax_type',
        'manual_tax_type',
        'manual_tax_value',
        'discount_type',
        'manual_discount_type',
        'manual_discount_value',
        'color',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'payment_details',
        'disabled_tax_ids',
        'notes',
        'footer',
        'attachments',
        'is_refund',
        'original_sale_id',
    ];

    protected $appends = [
        'formatted_total',
        'balance_due',
        'is_fully_returned',
        'return_status',
        'is_returned',
        'is_void',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'payment_details' => 'array',
        'disabled_tax_ids' => 'array',
        'is_refund' => 'boolean',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'salesman_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'original_sale_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Sale::class, 'original_sale_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year);
    }

    // Accessors
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format((float) ($this->total_amount ?? 0), 2);
    }

    public function getBalanceDueAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getIsFullyReturnedAttribute(): bool
    {
        if ($this->is_refund) {
            return false;
        }

        $refunds = $this->relationLoaded('refunds') ? $this->refunds : $this->refunds()->with('saleItems')->get();
        if ($refunds->isEmpty()) {
            return false;
        }

        $saleItems = $this->relationLoaded('saleItems') ? $this->saleItems : $this->saleItems()->get();
        $origQty = $saleItems->sum('quantity');

        $returnedQty = 0;
        foreach ($refunds as $refund) {
            $refItems = $refund->relationLoaded('saleItems') ? $refund->saleItems : $refund->saleItems()->get();
            $returnedQty += abs($refItems->sum('quantity'));
        }

        if ($origQty > 0 && $returnedQty >= $origQty) {
            return true;
        }

        $origTotal = abs((float) $this->total_amount);
        $returnedTotal = abs((float) $refunds->sum('total_amount'));
        if ($origTotal > 0 && $returnedTotal >= ($origTotal - 0.01)) {
            return true;
        }

        return $refunds->isNotEmpty() && ($returnedQty >= $origQty || ($origTotal > 0 && $returnedTotal >= $origTotal));
    }

    public function getReturnStatusAttribute(): string
    {
        if ($this->is_refund) {
            return 'none';
        }

        if ($this->is_fully_returned) {
            return 'full';
        }

        $refunds = $this->relationLoaded('refunds') ? $this->refunds : $this->refunds()->get();
        if ($refunds->isNotEmpty()) {
            return 'partial';
        }

        return 'none';
    }

    public function getIsReturnedAttribute(): bool
    {
        return $this->return_status !== 'none';
    }

    public function getIsVoidAttribute(): bool
    {
        return in_array(strtolower((string) $this->status), ['void', 'voided', 'cancelled']);
    }
}
