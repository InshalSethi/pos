<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskBoard extends Model
{
    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'created_by_id',
        'name',
        'description',
        'color',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(TaskColumn::class, 'task_board_id')->orderBy('order', 'asc');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_board_id')->orderBy('order', 'asc');
    }
}
