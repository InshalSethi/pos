<?php

namespace App\Http\Middleware;

use App\Models\Employee;
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
            $isInactive = !$user->is_active;

            if (!$isInactive) {
                $employee = $user->employee ?: Employee::where('user_id', $user->id)->orWhere('email', $user->email)->first();
                if ($employee && ($employee->status === 'inactive' || !$employee->is_active || $employee->employment_status === 'inactive')) {
                    $isInactive = true;
                }
            }

            if ($isInactive) {
                if (method_exists($user, 'tokens')) {
                    try {
                        $user->tokens()->delete();
                    } catch (\Exception $e) {}
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
