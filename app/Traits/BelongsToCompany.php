<?php

namespace App\Traits;

use App\Models\Scopes\CompanyScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    /**
     * Boot the BelongsToCompany trait for a model.
     *
     * @return void
     */
    protected static function bootBelongsToCompany()
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->current_company_id) {
                $model->company_id = Auth::user()->current_company_id;
            }
        });
    }

    /**
     * Get the company that owns the model.
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
