<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JournalEntryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'description',
        'debit_amount',
        'credit_amount',
        'partner_type',
        'partner_id',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

    // Relationships
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function partner(): MorphTo
    {
        return $this->morphTo();
    }

    // Accessors
    public function getFormattedDebitAmountAttribute(): string
    {
        return $this->debit_amount > 0 ? '$' . number_format($this->debit_amount, 2) : '';
    }

    public function getFormattedCreditAmountAttribute(): string
    {
        return $this->credit_amount > 0 ? '$' . number_format($this->credit_amount, 2) : '';
    }

    public function getAmountAttribute(): float
    {
        return $this->debit_amount > 0 ? $this->debit_amount : $this->credit_amount;
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getIsDebitAttribute(): bool
    {
        return $this->debit_amount > 0;
    }

    public function getIsCreditAttribute(): bool
    {
        return $this->credit_amount > 0;
    }

    public function getTypeAttribute(): string
    {
        return $this->is_debit ? 'Debit' : 'Credit';
    }
}
