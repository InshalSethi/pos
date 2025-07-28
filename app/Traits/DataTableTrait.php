<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

trait DataTableTrait
{
    /**
     * Apply server-side data table functionality to a query
     *
     * @param Builder $query
     * @param Request $request
     * @param array $searchableFields
     * @param array $sortableFields
     * @param string $defaultSort
     * @param string $defaultOrder
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function applyDataTableFilters(
        Builder $query,
        Request $request,
        array $searchableFields = [],
        array $sortableFields = [],
        string $defaultSort = 'created_at',
        string $defaultOrder = 'desc'
    ) {
        // Global search
        if ($request->has('search') && $request->search && !empty($searchableFields)) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $searchableFields) {
                foreach ($searchableFields as $field) {
                    if (str_contains($field, '.')) {
                        // Handle relationship fields
                        $parts = explode('.', $field);
                        $relation = $parts[0];
                        $column = $parts[1];
                        
                        $q->orWhereHas($relation, function ($rq) use ($column, $search) {
                            $rq->where($column, 'like', "%{$search}%");
                        });
                    } else {
                        // Handle direct fields
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        // Column-specific filters
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') && $value !== null && $value !== '') {
                $column = str_replace('filter_', '', $key);
                
                if (str_contains($column, '.')) {
                    // Handle relationship filters
                    $parts = explode('.', $column);
                    $relation = $parts[0];
                    $relationColumn = $parts[1];
                    
                    $query->whereHas($relation, function ($rq) use ($relationColumn, $value) {
                        $rq->where($relationColumn, $value);
                    });
                } else {
                    // Handle direct column filters
                    $query->where($column, $value);
                }
            }
        }

        // Date range filters
        if ($request->has('date_from') && $request->date_from) {
            $dateColumn = $request->get('date_column', 'created_at');
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $dateColumn = $request->get('date_column', 'created_at');
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', $defaultSort);
        $sortOrder = $request->get('sort_order', $defaultOrder);
        
        // Validate sort fields
        if (!empty($sortableFields) && in_array($sortBy, $sortableFields)) {
            if (str_contains($sortBy, '.')) {
                // Handle relationship sorting
                $parts = explode('.', $sortBy);
                $relation = $parts[0];
                $column = $parts[1];
                
                $query->join(
                    str_plural($relation),
                    str_plural($relation) . '.id',
                    '=',
                    $query->getModel()->getTable() . '.' . $relation . '_id'
                )->orderBy(str_plural($relation) . '.' . $column, $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy($defaultSort, $defaultOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $perPage = min($perPage, 100); // Limit max per page to 100
        
        return $query->paginate($perPage);
    }

    /**
     * Apply advanced search filters
     *
     * @param Builder $query
     * @param Request $request
     * @param array $advancedFilters
     * @return Builder
     */
    protected function applyAdvancedFilters(Builder $query, Request $request, array $advancedFilters = [])
    {
        foreach ($advancedFilters as $filter => $config) {
            if (!$request->has($filter) || $request->get($filter) === null) {
                continue;
            }

            $value = $request->get($filter);
            $type = $config['type'] ?? 'exact';
            $column = $config['column'] ?? $filter;

            switch ($type) {
                case 'like':
                    $query->where($column, 'like', "%{$value}%");
                    break;
                    
                case 'in':
                    if (is_array($value)) {
                        $query->whereIn($column, $value);
                    }
                    break;
                    
                case 'between':
                    if (is_array($value) && count($value) === 2) {
                        $query->whereBetween($column, $value);
                    }
                    break;
                    
                case 'date_range':
                    if (isset($value['from'])) {
                        $query->whereDate($column, '>=', $value['from']);
                    }
                    if (isset($value['to'])) {
                        $query->whereDate($column, '<=', $value['to']);
                    }
                    break;
                    
                case 'relationship':
                    $relation = $config['relation'];
                    $relationColumn = $config['relation_column'];
                    
                    $query->whereHas($relation, function ($rq) use ($relationColumn, $value) {
                        $rq->where($relationColumn, $value);
                    });
                    break;
                    
                case 'exact':
                default:
                    $query->where($column, $value);
                    break;
            }
        }

        return $query;
    }

    /**
     * Get data table response with metadata
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginatedData
     * @param array $additionalData
     * @return array
     */
    protected function getDataTableResponse($paginatedData, array $additionalData = [])
    {
        return array_merge([
            'data' => $paginatedData->items(),
            'current_page' => $paginatedData->currentPage(),
            'per_page' => $paginatedData->perPage(),
            'total' => $paginatedData->total(),
            'last_page' => $paginatedData->lastPage(),
            'from' => $paginatedData->firstItem(),
            'to' => $paginatedData->lastItem(),
            'links' => [
                'first' => $paginatedData->url(1),
                'last' => $paginatedData->url($paginatedData->lastPage()),
                'prev' => $paginatedData->previousPageUrl(),
                'next' => $paginatedData->nextPageUrl(),
            ]
        ], $additionalData);
    }
}
