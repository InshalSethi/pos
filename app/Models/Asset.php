<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes, BelongsToCompany, HasUtcDatabaseTimezones, HasFactory, LogsActivity;

    protected static string $activityLogType = 'finance';

    protected $fillable = [
        'company_id',
        'product_id',
        'asset_code',
        'name',
        'category',
        'serial_number',
        'quantity',
        'purchase_date',
        'purchase_cost',
        'current_value',
        'salvage_value',
        'useful_life_years',
        'depreciation_method',
        'accumulated_depreciation',
        'location',
        'status',
        'chart_account_id',
        'supplier_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'useful_life_years' => 'integer',
        'accumulated_depreciation' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_account_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Calculates depreciation based on purchase date, useful life, and depreciation method.
     */
    public function calculateDepreciation(): array
    {
        $cost = (float) $this->purchase_cost;
        $salvage = (float) $this->salvage_value;
        $lifeYears = max(1, (int) $this->useful_life_years);
        $purchaseDate = \Carbon\Carbon::parse($this->purchase_date);
        $now = \Carbon\Carbon::now();

        if ($this->depreciation_method === 'none' || $cost <= $salvage) {
            return [
                'annual_depreciation' => 0.00,
                'accumulated_depreciation' => 0.00,
                'current_value' => $cost,
                'age_years' => 0,
            ];
        }

        $ageMonths = max(0, $purchaseDate->diffInMonths($now));
        $ageYears = round($ageMonths / 12, 2);

        $depreciableAmount = $cost - $salvage;
        $annualDepreciation = $depreciableAmount / $lifeYears;

        if ($this->depreciation_method === 'straight_line') {
            $accumulated = min($depreciableAmount, ($annualDepreciation / 12) * $ageMonths);
        } else {
            // Declining balance calculation
            $rate = 2 / $lifeYears; // Double declining rate
            $current = $cost;
            for ($i = 0; $i < floor($ageYears); $i++) {
                $dep = min($current - $salvage, $current * $rate);
                $current -= $dep;
            }
            $accumulated = min($depreciableAmount, $cost - $current);
        }

        $currentValue = max($salvage, $cost - $accumulated);

        return [
            'annual_depreciation' => round($annualDepreciation, 2),
            'accumulated_depreciation' => round($accumulated, 2),
            'current_value' => round($currentValue, 2),
            'age_years' => $ageYears,
        ];
    }
}
