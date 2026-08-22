<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'employee_id',
        'log_type',
        'event',
        'subject_type',
        'subject_id',
        'subject_title',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'actor_name',
        'actor_email',
        'actor_type',
        'actor_role',
        'actor_avatar',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getActorNameAttribute(): string
    {
        if ($this->employee) {
            return trim($this->employee->first_name . ' ' . $this->employee->last_name) ?: ($this->employee->name ?? 'Employee');
        }

        if ($this->user) {
            return $this->user->name ?? trim(($this->user->first_name ?? '') . ' ' . ($this->user->last_name ?? '')) ?: 'User';
        }

        return 'System / Guest';
    }

    public function getActorTypeAttribute(): string
    {
        if ($this->employee_id || ($this->user && $this->user->isEmployee())) {
            return 'Employee';
        }
        return 'Owner / User';
    }

    public function getActorRoleAttribute(): string
    {
        if ($this->user) {
            return $this->user->role_name ?: ($this->user->is_owner ? 'Owner' : 'User');
        }
        return 'User';
    }

    public function getActorAvatarAttribute(): ?string
    {
        if ($this->employee && $this->employee->profile_image) {
            return asset('storage/' . $this->employee->profile_image);
        }
        if ($this->user) {
            return $this->user->avatar_url;
        }
        return null;
    }

    public function getActorEmailAttribute(): ?string
    {
        if ($this->employee && $this->employee->email) {
            return $this->employee->email;
        }
        if ($this->user && $this->user->email) {
            return $this->user->email;
        }
        return null;
    }
}
