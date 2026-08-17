<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'paid_at',
        'payment_method',
        'account_id',
        'amount',
        'description',
        'category_id',
        'customer_id',
        'vendor_id',
        'employee_id',
        'tax_id',
        'number',
        'reference',
        'attachment_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date:Y-m-d',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'category_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'vendor_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }
}
