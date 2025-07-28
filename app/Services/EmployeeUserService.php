<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EmployeeUserService
{
    /**
     * Create a user account for an employee
     */
    public function createUserAccountForEmployee(Employee $employee, array $userData = []): User
    {
        return DB::transaction(function () use ($employee, $userData) {
            // Check if employee already has a user account
            if ($employee->user_id) {
                throw new \Exception('Employee already has a user account');
            }

            // Generate default password if not provided
            $password = $userData['password'] ?? $this->generateDefaultPassword();
            
            // Create user account
            $user = User::create([
                'name' => $employee->full_name,
                'email' => $employee->email,
                'password' => Hash::make($password),
                'profile_image' => $employee->profile_image,
            ]);

            // Link employee to user
            $employee->update(['user_id' => $user->id]);

            // Assign default role based on position/department
            $this->assignDefaultRole($user, $employee);

            // Create default user settings
            $this->createDefaultUserSettings($user);

            return $user;
        });
    }

    /**
     * Update user account when employee information changes
     */
    public function syncUserWithEmployee(Employee $employee): void
    {
        if (!$employee->user) {
            return;
        }

        $user = $employee->user;
        
        // Update user information
        $user->update([
            'name' => $employee->full_name,
            'email' => $employee->email,
            'profile_image' => $employee->profile_image,
        ]);

        // Update role if position changed
        $this->updateUserRole($user, $employee);
    }

    /**
     * Deactivate user account when employee is terminated
     */
    public function deactivateUserAccount(Employee $employee): void
    {
        if (!$employee->user) {
            return;
        }

        $user = $employee->user;
        
        // Revoke all tokens (logout from all devices)
        $user->tokens()->delete();
        
        // Remove all roles and permissions
        $user->syncRoles([]);
        
        // Optionally, you could soft delete the user or mark as inactive
        // For now, we'll just remove access by removing roles
    }

    /**
     * Reactivate user account when employee is reactivated
     */
    public function reactivateUserAccount(Employee $employee): void
    {
        if (!$employee->user) {
            return;
        }

        $user = $employee->user;
        
        // Reassign appropriate role
        $this->assignDefaultRole($user, $employee);
    }

    /**
     * Generate a default password for new user accounts
     */
    private function generateDefaultPassword(): string
    {
        // Generate a secure random password
        return Str::random(12);
    }

    /**
     * Assign default role based on employee position/department
     */
    private function assignDefaultRole(User $user, Employee $employee): void
    {
        // Remove existing roles
        $user->syncRoles([]);

        // Determine role based on position level or department
        $roleName = $this->determineRoleFromEmployee($employee);
        
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $user->assignRole($role);
        }
    }

    /**
     * Update user role when employee position changes
     */
    private function updateUserRole(User $user, Employee $employee): void
    {
        $newRoleName = $this->determineRoleFromEmployee($employee);
        $currentRoles = $user->getRoleNames()->toArray();
        
        // Only update if role should change
        if (!in_array($newRoleName, $currentRoles)) {
            $this->assignDefaultRole($user, $employee);
        }
    }

    /**
     * Determine appropriate role based on employee information
     */
    private function determineRoleFromEmployee(Employee $employee): string
    {
        // Check if employee is a department manager
        if ($employee->department && $employee->department->manager_id === $employee->id) {
            return 'manager';
        }

        // Check position level
        if ($employee->position) {
            switch ($employee->position->level) {
                case 'executive':
                case 'director':
                    return 'admin';
                case 'manager':
                case 'lead':
                    return 'manager';
                case 'senior':
                case 'mid':
                case 'junior':
                case 'entry':
                default:
                    return 'employee';
            }
        }

        // Default role
        return 'employee';
    }

    /**
     * Create default user settings
     */
    private function createDefaultUserSettings(User $user): void
    {
        UserSettings::create([
            'user_id' => $user->id,
            'email_notifications' => true,
            'sales_alerts' => false,
            'low_stock_alerts' => false,
            'theme' => 'light',
            'items_per_page' => 15,
            'default_payment_method' => 'cash',
            'auto_print_receipts' => false,
            'sound_effects' => true,
            'session_timeout' => 60,
            'two_factor_auth' => false,
        ]);
    }

    /**
     * Reset user password
     */
    public function resetUserPassword(User $user, string $newPassword = null): string
    {
        $password = $newPassword ?? $this->generateDefaultPassword();
        
        $user->update([
            'password' => Hash::make($password)
        ]);

        // Revoke all existing tokens to force re-login
        $user->tokens()->delete();

        return $password;
    }

    /**
     * Get employees without user accounts
     */
    public function getEmployeesWithoutUserAccounts()
    {
        return Employee::whereNull('user_id')
                      ->active()
                      ->with(['department', 'position'])
                      ->get();
    }

    /**
     * Bulk create user accounts for multiple employees
     */
    public function bulkCreateUserAccounts(array $employeeIds): array
    {
        $results = [];
        
        foreach ($employeeIds as $employeeId) {
            try {
                $employee = Employee::findOrFail($employeeId);
                $user = $this->createUserAccountForEmployee($employee);
                $results[] = [
                    'employee_id' => $employee->id,
                    'user_id' => $user->id,
                    'success' => true,
                    'password' => $this->generateDefaultPassword() // You might want to store this temporarily
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'employee_id' => $employeeId,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Check if employee should have system access
     */
    public function shouldHaveSystemAccess(Employee $employee): bool
    {
        // Define criteria for system access
        return $employee->isActive() && 
               $employee->position && 
               in_array($employee->position->level, [
                   'manager', 'lead', 'senior', 'mid', 'director', 'executive'
               ]);
    }

    /**
     * Audit user-employee relationships
     */
    public function auditUserEmployeeRelationships(): array
    {
        return [
            'employees_without_users' => Employee::whereNull('user_id')->active()->count(),
            'users_without_employees' => User::whereDoesntHave('employee')->count(),
            'inactive_employees_with_active_users' => Employee::where('is_active', false)
                                                              ->whereHas('user')
                                                              ->count(),
            'mismatched_emails' => Employee::whereHas('user', function($query) {
                                       $query->whereColumn('users.email', '!=', 'employees.email');
                                   })->count(),
        ];
    }
}
