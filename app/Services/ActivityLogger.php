<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Sensitive fields that must be masked or removed from audit log properties.
     */
    protected static array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'remember_token',
        'card_number',
        'card_cvc',
        'cvv',
        'secret',
        'api_token',
        'token',
        'license_key',
    ];

    /**
     * Log a general or domain-specific activity.
     */
    public static function log(
        string $type,
        string $event,
        string $description,
        ?Model $subject = null,
        ?string $subjectTitle = null,
        ?array $properties = [],
        ?User $user = null,
        ?int $companyId = null
    ): ?ActivityLog {
        try {
            $user = $user ?? Auth::user();
            
            // Resolve employee record if available
            $employeeId = null;
            if ($user) {
                $employee = Employee::where('user_id', $user->id)
                    ->orWhere('email', $user->email)
                    ->first();
                if ($employee) {
                    $employeeId = $employee->id;
                }
            }

            // Resolve company_id across User, Employee, Pivot, and Subject models
            $resolvedCompanyId = $companyId;
            if (!$resolvedCompanyId && $user) {
                $resolvedCompanyId = $user->current_company_id ?? $user->company_id;
                if (!$resolvedCompanyId && isset($employee) && $employee) {
                    $resolvedCompanyId = $employee->company_id;
                }
                if (!$resolvedCompanyId && method_exists($user, 'companies')) {
                    $resolvedCompanyId = $user->companies()->first()?->id;
                }
            }
            if (!$resolvedCompanyId && $subject && isset($subject->company_id)) {
                $resolvedCompanyId = $subject->company_id;
            }

            // Sync user's current_company_id if missing
            if ($user && !$user->current_company_id && $resolvedCompanyId) {
                try {
                    $user->update(['current_company_id' => $resolvedCompanyId]);
                } catch (\Throwable $th) {}
            }

            // Extract subject title if not provided
            if ($subject && empty($subjectTitle)) {
                $subjectTitle = static::resolveSubjectTitle($subject);
            }

            // Sanitize properties diff payload
            $sanitizedProperties = static::sanitizeProperties($properties);

            return ActivityLog::create([
                'company_id'    => $resolvedCompanyId,
                'user_id'       => $user?->id,
                'employee_id'   => $employeeId,
                'log_type'      => $type,
                'event'         => $event,
                'subject_type'  => $subject ? get_class($subject) : null,
                'subject_id'    => $subject?->getKey(),
                'subject_title' => $subjectTitle ? substr($subjectTitle, 0, 250) : null,
                'description'   => substr($description, 0, 490),
                'properties'    => $sanitizedProperties ?: null,
                'ip_address'    => Request::ip(),
                'user_agent'    => Request::header('User-Agent'),
            ]);
        } catch (\Throwable $e) {
            // Prevent activity logging failure from breaking main application process
            \Illuminate\Support\Facades\Log::error('ActivityLog Error: ' . $e->getMessage(), [
                'type' => $type,
                'event' => $event,
                'exception' => $e
            ]);
            return null;
        }
    }

    /**
     * Log authentication events (Login, Logout, Failed Login, Account Register).
     */
    public static function logAuth(string $event, string $description, ?User $user = null, ?array $properties = []): ?ActivityLog
    {
        return static::log(
            type: 'auth',
            event: $event,
            description: $description,
            subject: $user,
            subjectTitle: $user?->name ?? $user?->email,
            properties: $properties,
            user: $user
        );
    }

    /**
     * Resolve a human-readable title for a given Eloquent model.
     */
    public static function resolveSubjectTitle(Model $subject): string
    {
        if (isset($subject->first_name) || isset($subject->last_name)) {
            $name = trim(($subject->first_name ?? '') . ' ' . ($subject->last_name ?? ''));
            if (!empty($name)) return $name;
        }

        foreach (['name', 'title', 'sku', 'po_number', 'invoice_number', 'sale_number', 'receipt_number', 'reference_number', 'reference_no', 'reference', 'code', 'account_name', 'email'] as $field) {
            if (!empty($subject->{$field})) {
                return (string) $subject->{$field};
            }
        }
        return class_basename($subject) . ' #' . $subject->getKey();
    }

    /**
     * Sanitize array values to remove sensitive keys recursively.
     */
    public static function sanitizeProperties(?array $properties): ?array
    {
        if (empty($properties)) {
            return null;
        }

        foreach ($properties as $key => &$value) {
            if (in_array(strtolower((string)$key), static::$sensitiveFields, true)) {
                $value = '********';
            } elseif (is_array($value)) {
                $value = static::sanitizeProperties($value);
            }
        }

        return $properties;
    }
}
