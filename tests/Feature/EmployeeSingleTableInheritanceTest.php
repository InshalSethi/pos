<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmployeeSingleTableInheritanceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_employee_creation_without_login_creates_user_record_with_employee_type_and_null_password(): void
    {
        $admin = User::factory()->create(['type' => 'user']);
        $admin->givePermissionTo('manage_employees');

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/employees', [
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice.smith@example.com',
            'gender' => 'female',
            'hire_date' => '2026-01-15',
            'employment_type' => 'full_time',
            'basic_salary' => 4500,
            'salary_type' => 'monthly',
            'create_user_account' => false,
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'alice.smith@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('employee', $user->type);
        $this->assertNull($user->password);
        $this->assertFalse($user->hasLoginAccess());
        $this->assertTrue($user->isEmployee());
        $this->assertFalse($user->isSystemUser());

        $employee = Employee::where('email', 'alice.smith@example.com')->first();
        $this->assertNotNull($employee);
        $this->assertEquals($user->id, $employee->user_id);
    }

    public function test_employee_creation_with_login_creates_active_system_user(): void
    {
        $admin = User::factory()->create(['type' => 'user']);
        $admin->givePermissionTo('manage_employees');

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/employees', [
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'email' => 'bob.jones@example.com',
            'gender' => 'male',
            'hire_date' => '2026-01-15',
            'employment_type' => 'full_time',
            'basic_salary' => 5000,
            'salary_type' => 'monthly',
            'create_user_account' => true,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'bob.jones@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('user', $user->type);
        $this->assertNotNull($user->password);
        $this->assertTrue($user->hasLoginAccess());
        $this->assertTrue($user->isSystemUser());
    }

    public function test_non_login_employee_is_blocked_from_logging_in(): void
    {
        $admin = User::factory()->create(['type' => 'user']);
        $admin->givePermissionTo('manage_employees');

        $this->actingAs($admin, 'sanctum')->postJson('/api/employees', [
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'email' => 'charlie.brown@example.com',
            'gender' => 'male',
            'hire_date' => '2026-01-15',
            'employment_type' => 'full_time',
            'basic_salary' => 4000,
            'salary_type' => 'monthly',
            'create_user_account' => false,
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'charlie.brown@example.com',
            'password' => 'anypassword',
        ]);

        $loginResponse->assertStatus(422);
    }
}
