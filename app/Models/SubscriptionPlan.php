<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'yearly_price',
        'trial_days',
        'max_companies',
        'max_users_per_company',
        'is_popular',
        'is_custom',
        'is_active',
        'sort_order',
        'features',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'trial_days' => 'integer',
        'max_companies' => 'integer',
        'max_users_per_company' => 'integer',
        'is_popular' => 'boolean',
        'is_custom' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'features' => 'array',
    ];
}
