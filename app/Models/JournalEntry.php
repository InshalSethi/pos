<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference',
        'description',
        'entry_type',
        'status',
        'total_debit',
        'total_credit',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
    }

    public function scopeManual($query)
    {
        return $query->where('entry_type', 'manual');
    }

    public function scopeAutomatic($query)
    {
        return $query->where('entry_type', 'automatic');
    }

    // Accessors
    public function getFormattedTotalDebitAttribute(): string
    {
        return '$' . number_format($this->total_debit, 2);
    }

    public function getFormattedTotalCreditAttribute(): string
    {
        return '$' . number_format($this->total_credit, 2);
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'posted' => 'Posted',
            'reversed' => 'Reversed',
            default => ucfirst($this->status)
        };
    }

    public function getEntryTypeDisplayAttribute(): string
    {
        return match($this->entry_type) {
            'manual' => 'Manual Entry',
            'automatic' => 'Automatic Entry',
            'adjustment' => 'Adjustment Entry',
            'closing' => 'Closing Entry',
            default => ucfirst($this->entry_type)
        };
    }

    public function getIsBalancedAttribute(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    public function getCanEditAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanPostAttribute(): bool
    {
        return $this->status === 'draft' && $this->is_balanced;
    }

    public function getCanReverseAttribute(): bool
    {
        return $this->status === 'posted';
    }
}
