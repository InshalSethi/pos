<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_email',
        'company_phone',
        'company_logo',
        'registration_number',
        'owner_role',
        'team_size',
        'intended_tasks',
        'business_type',
        'business_scale',
        'country',
        'system_language',
        'base_currency',
        'timezone_offset',
        'fiscal_year_start',
        'status',
        'draft_step',
    ];

    protected $casts = [
        'intended_tasks' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }
}
