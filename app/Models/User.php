<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;


use App\Traits\HasUtcDatabaseTimezones;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPasswordNotification;

use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use SoftDeletes;

    use HasUtcDatabaseTimezones;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'name',
        'email',
        'password',
        'profile_image',
        'phone',
        'address',
        'notes',
        'is_active',
        'google_id',
        'current_company_id',
        'onboarding_completed',
        'company_id',
        'is_setup_completed',
        'avatar',
        'profile_photo_path',
        'attachments',
    ];

    /**
     * Relationships to always eager-load.
     * Prevents N+1 queries in middleware and API controllers.
     *
     * @var array
     */
    protected $with = ['currentCompany'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'attachments' => 'array',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['role_name', 'company_id', 'is_setup_completed', 'avatar_url', 'profile_photo_path', 'attachments_urls'];

    public function getAttachmentsUrlsAttribute(): array
    {
        $list = $this->attachments ?? [];
        if (!is_array($list)) {
            $list = json_decode($list, true) ?? [];
        }

        $urls = [];
        foreach ($list as $idx => $path) {
            if ($path) {
                $urls[] = [
                    'index' => $idx,
                    'url' => str_starts_with($path, 'http') ? $path : asset('storage/' . $path),
                    'path' => $path,
                    'filename' => basename($path),
                ];
            }
        }
        return $urls;
    }

    /**
     * Get the name of the primary role.
     */
    public function getRoleNameAttribute()
    {
        return $this->roles->first() ? $this->roles->first()->name : '';
    }

    /**
     * Get company_id mapping to current_company_id.
     */
    public function getCompanyIdAttribute()
    {
        return $this->current_company_id;
    }

    /**
     * Set company_id mapping to current_company_id.
     */
    public function setCompanyIdAttribute($value)
    {
        $this->attributes['current_company_id'] = $value;
        $this->current_company_id = $value;
    }

    /**
     * Get is_setup_completed mapping to onboarding_completed.
     */
    public function getIsSetupCompletedAttribute()
    {
        return (bool) ($this->onboarding_completed ?? false);
    }

    /**
     * Set is_setup_completed mapping to onboarding_completed.
     */
    public function setIsSetupCompletedAttribute($value)
    {
        $this->attributes['onboarding_completed'] = $value;
        $this->onboarding_completed = $value;
    }

    /**
     * Get avatar mapping to profile_image.
     */
    public function getAvatarAttribute()
    {
        return $this->profile_image;
    }

    /**
     * Set avatar mapping to profile_image.
     */
    public function setAvatarAttribute($value)
    {
        $this->attributes['profile_image'] = $value;
        $this->profile_image = $value;
    }

    /**
     * Get profile_photo_path mapping to profile_image.
     */
    public function getProfilePhotoPathAttribute()
    {
        return $this->profile_image;
    }

    /**
     * Set profile_photo_path mapping to profile_image.
     */
    public function setProfilePhotoPathAttribute($value)
    {
        $this->attributes['profile_image'] = $value;
        $this->profile_image = $value;
    }

    /**
     * Get full public URL for avatar/profile image.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        if (str_starts_with($this->profile_image, 'http://') || str_starts_with($this->profile_image, 'https://')) {
            return $this->profile_image;
        }

        return Storage::disk('public')->url($this->profile_image);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSettings::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function currentCompany(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    public function companies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withPivot('role')->withTimestamps();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Check if user is an employee.
     */
    public function isEmployee(): bool
    {
        return $this->type === 'employee' || $this->hasRole('employee') || $this->employee()->exists();
    }

    /**
     * Check if user is a system user.
     */
    public function isSystemUser(): bool
    {
        return $this->type === 'user';
    }

    /**
     * Check if record has direct system login access.
     */
    public function hasLoginAccess(): bool
    {
        if (!(bool)$this->is_active || empty($this->password)) {
            return false;
        }

        $employee = $this->employee ?: Employee::where('user_id', $this->id)->orWhere('email', $this->email)->first();
        if ($employee) {
            return (bool)$employee->has_system_access && ($employee->status === 'active' || (bool)$employee->is_active);
        }

        return $this->type === 'user';
    }

    /**
     * Scope query to only include employees.
     */
    public function scopeEmployees($query)
    {
        return $query->where('type', 'employee');
    }

    /**
     * Scope query to only include system users.
     */
    public function scopeSystemUsers($query)
    {
        return $query->where('type', 'user');
    }

    /**
     * Scope query to only include users with login access.
     */
    public function scopeWithLoginAccess($query)
    {
        return $query->where('type', 'user')->whereNotNull('password')->where('is_active', true);
    }
}
