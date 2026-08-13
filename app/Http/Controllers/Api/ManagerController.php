<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class ManagerController extends Controller
{
    /**
     * Display manager profile details and eager-loaded direct report subordinates.
     */
    public function getSubordinates($id): JsonResponse
    {
        $manager = Employee::with([
            'department',
            'position',
            'managedDepartments',
            'user.roles'
        ])->findOrFail($id);

        $subordinates = Employee::where('manager_id', $manager->id)
            ->orWhere('manager_id', $manager->user_id)
            ->with(['department', 'position', 'user'])
            ->get();

        return response()->json([
            'success' => true,
            'manager' => $manager,
            'subordinates' => $subordinates,
            'count' => $subordinates->count()
        ]);
    }
}
