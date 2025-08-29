<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'user_id',
        'action',
        'old_status',
        'new_status',
        'old_values',
        'new_values',
        'changed_fields',
        'notes',
        'ip_address',
        'user_agent',
        'affects_accounting',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'affects_accounting' => 'boolean',
    ];

    // Relationships
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForExpense($query, $expenseId)
    {
        return $query->where('expense_id', $expenseId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeAffectsAccounting($query)
    {
        return $query->where('affects_accounting', true);
    }

    // Helper methods
    public static function logExpenseChange(Expense $expense, string $action, array $oldValues = [], array $newValues = [], ?string $notes = null): self
    {
        $changedFields = [];

        // Determine which fields changed
        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changedFields[] = $field;
            }
        }

        // Determine if this affects accounting
        $affectsAccounting = in_array($action, ['approved', 'paid', 'deleted']) ||
                           in_array($expense->status, ['approved', 'paid']) ||
                           array_intersect($changedFields, ['amount', 'status', 'category_id']);

        return self::create([
            'expense_id' => $expense->id,
            'user_id' => auth()->id() ?? 1,
            'action' => $action,
            'old_status' => $oldValues['status'] ?? null,
            'new_status' => $newValues['status'] ?? $expense->status,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $changedFields,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'affects_accounting' => $affectsAccounting,
        ]);
    }
}
