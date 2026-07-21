<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = null;

        $headerCompany = request()->header('X-Company-ID');
        $headerWorkspace = request()->header('X-Workspace-ID');

        if (!empty($headerCompany) && $headerCompany !== 'undefined' && $headerCompany !== 'null' && is_numeric($headerCompany)) {
            $companyId = (int) $headerCompany;
        } elseif (!empty($headerWorkspace) && $headerWorkspace !== 'undefined' && $headerWorkspace !== 'null' && is_numeric($headerWorkspace)) {
            $companyId = (int) $headerWorkspace;
        } elseif (auth()->check()) {
            $user = auth()->user();
            $companyId = $user->current_company_id ?: ($user->company_id ?? null);
        }

        if ($companyId) {
            $builder->where($model->getTable() . '.company_id', $companyId);
        }
    }
}
