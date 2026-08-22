<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Get paginated activity logs with filtering and analytics stats.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $companyId = $user->current_company_id ?? $user->company_id;
        if (!$companyId && $user->employee) {
            $companyId = $user->employee->company_id;
        }
        if (!$companyId && method_exists($user, 'companies')) {
            $companyId = $user->companies()->first()?->id;
        }

        $query = ActivityLog::with(['user:id,name,email,profile_image', 'employee:id,first_name,last_name,email,profile_image,is_manager'])
            ->where(function ($q) use ($companyId, $user) {
                if ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('employee', function ($eq) use ($companyId) {
                          $eq->where('company_id', $companyId);
                      })
                      ->orWhere(function ($sq) use ($user) {
                          $sq->whereNull('company_id')->where('user_id', $user->id);
                      });
                } else {
                    $q->where('user_id', $user->id);
                }
            });

        // Filter by Log Type
        if ($request->filled('log_type') && $request->log_type !== 'all') {
            $query->where('log_type', $request->log_type);
        }

        // Filter by Event
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by User/Actor
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by Employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Search by description, subject title, or IP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('subject_title', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Dynamic Sorting & Pagination Limiter
        $allowedSorts = ['created_at', 'log_type', 'event', 'description', 'ip_address', 'id'];
        $sortBy = in_array($request->get('sort_by'), $allowedSorts, true) ? $request->get('sort_by') : 'created_at';
        $sortOrder = strtolower($request->get('sort_order')) === 'asc' ? 'asc' : 'desc';

        $perPage = (int) $request->get('per_page', 15);
        if ($perPage < 5) $perPage = 15;
        if ($perPage > 200) $perPage = 200;

        $logs = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        // Stats calculation
        $todayQuery = ActivityLog::where(function ($q) use ($companyId) {
            if ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereHas('employee', function ($eq) use ($companyId) {
                      $eq->where('company_id', $companyId);
                  });
            }
        })->whereDate('created_at', today());

        $stats = [
            'total_today' => (clone $todayQuery)->count(),
            'active_users_today' => (clone $todayQuery)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'security_events_today' => (clone $todayQuery)->whereIn('log_type', ['security', 'auth'])->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $logs,
            'stats' => $stats,
        ]);
    }

    /**
     * Get details of a single activity log.
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $companyId = $user->current_company_id ?? $user->company_id;

        $log = ActivityLog::with(['user', 'employee', 'company'])
            ->where(function ($q) use ($companyId, $user) {
                if ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhere(function ($sq) use ($user) {
                          $sq->whereNull('company_id')->where('user_id', $user->id);
                      });
                } else {
                    $q->where('user_id', $user->id);
                }
            })
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    /**
     * Get available log categories.
     */
    public function types(): JsonResponse
    {
        $types = [
            ['key' => 'all', 'label' => 'All Activities'],
            ['key' => 'auth', 'label' => 'Authentication'],
            ['key' => 'company', 'label' => 'Company & Setup'],
            ['key' => 'team', 'label' => 'Team & Employees'],
            ['key' => 'sales', 'label' => 'Sales & POS'],
            ['key' => 'inventory', 'label' => 'Inventory & Stock'],
            ['key' => 'finance', 'label' => 'Finance & Expenses'],
            ['key' => 'security', 'label' => 'Security & Access'],
            ['key' => 'crud', 'label' => 'General Updates'],
        ];

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
