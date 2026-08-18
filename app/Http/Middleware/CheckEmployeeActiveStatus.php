<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEmployeeActiveStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Check if user is inactive directly or via linked employee
            $isInactive = !$user->is_active;

            $employee = $user->employee;
            if (!$isInactive && $employee) {
                if ($employee->status === 'inactive' || !$employee->is_active) {
                    $isInactive = true;
                }
            }

            if ($isInactive) {
                // Delete active Sanctum tokens
                if (method_exists($user, 'tokens')) {
                    try {
                        $user->tokens()->delete();
                    } catch (\Exception $e) {
                        // Suppress token purge exceptions if table does not exist
                    }
                }

                return response()->json([
                    'error' => 'ACCOUNT_INACTIVE',
                    'message' => 'Your account has been deactivated by the administrator.'
                ], 403);
            }
        }

        return $next($request);
    }
}
