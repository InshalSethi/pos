<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUtcDatabaseTimezones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskColumn extends Model
{
    use SoftDeletes;

    use BelongsToCompany;
    use HasUtcDatabaseTimezones;
    use HasFactory;

    protected $fillable = [
        'task_board_id',
        'company_id',
        'name',
        'color',
        'order',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(TaskBoard::class, 'task_board_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_column_id')->orderBy('order', 'asc');
    }
}
