<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_type_id',
        'name',
        'area_of_use',
        'description',
        'fields',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static array $areaOfUseOptions = [
        'sale_invoice' => 'Sale invoice',
        'sale_return' => 'Sale Return',
        'purchase_invoice' => 'Purchase Invoice',
        'purchase_return' => 'Purchase Return',
        'items' => 'Items',
        'expenses' => 'Expenses',
        'payment_out' => 'Payment Out',
        'payment_receipt' => 'Payment Receipt',
    ];

    /**
     * Relationship to BusinessType.
     */
    public function businessType()
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }
}
