<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\EmployeeSalary;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample departments if they don't exist
        $hrDept = Department::firstOrCreate([
            'code' => 'HR'
        ], [
            'name' => 'Human Resources',
            'description' => 'Human Resources Department',
            'is_active' => true,
        ]);

        $itDept = Department::firstOrCreate([
            'code' => 'IT'
        ], [
            'name' => 'Information Technology',
            'description' => 'IT Department',
            'is_active' => true,
        ]);

        $salesDept = Department::firstOrCreate([
            'code' => 'SALES'
        ], [
            'name' => 'Sales',
            'description' => 'Sales Department',
            'is_active' => true,
        ]);

        // Create some sample positions if they don't exist
        $managerPos = Position::firstOrCreate([
            'code' => 'MGR'
        ], [
            'title' => 'Manager',
            'description' => 'Department Manager',
            'level' => 'manager',
            'min_salary' => 60000,
            'max_salary' => 80000,
            'is_active' => true,
        ]);

        $seniorPos = Position::firstOrCreate([
            'code' => 'SR'
        ], [
            'title' => 'Senior Employee',
            'description' => 'Senior Level Employee',
            'level' => 'senior',
            'min_salary' => 45000,
            'max_salary' => 60000,
            'is_active' => true,
        ]);

        $juniorPos = Position::firstOrCreate([
            'code' => 'JR'
        ], [
            'title' => 'Junior Employee',
            'description' => 'Junior Level Employee',
            'level' => 'junior',
            'min_salary' => 30000,
            'max_salary' => 45000,
            'is_active' => true,
        ]);

        // Create sample employees
        $employees = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@company.com',
                'phone' => '+1-555-0101',
                'department_id' => $hrDept->id,
                'position_id' => $managerPos->id,
                'hire_date' => '2020-01-15',
                'employment_type' => 'full_time',
                'employment_status' => 'active',
                'basic_salary' => 65000,
                'salary_type' => 'monthly',
                'is_active' => true,
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@company.com',
                'phone' => '+1-555-0102',
                'department_id' => $itDept->id,
                'position_id' => $managerPos->id,
                'hire_date' => '2019-03-20',
                'employment_type' => 'full_time',
                'employment_status' => 'active',
                'basic_salary' => 70000,
                'salary_type' => 'monthly',
                'is_active' => true,
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Davis',
                'email' => 'mike.davis@company.com',
                'phone' => '+1-555-0103',
                'department_id' => $salesDept->id,
                'position_id' => $seniorPos->id,
                'hire_date' => '2021-06-10',
                'employment_type' => 'full_time',
                'employment_status' => 'active',
                'basic_salary' => 50000,
                'salary_type' => 'monthly',
                'is_active' => true,
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Wilson',
                'email' => 'emily.wilson@company.com',
                'phone' => '+1-555-0104',
                'department_id' => $itDept->id,
                'position_id' => $juniorPos->id,
                'hire_date' => '2022-09-01',
                'employment_type' => 'full_time',
                'employment_status' => 'active',
                'basic_salary' => 35000,
                'salary_type' => 'monthly',
                'is_active' => true,
            ],
        ];

        foreach ($employees as $employeeData) {
            // Generate employee number
            $employeeData['employee_number'] = Employee::generateEmployeeNumber();

            // Create user account for employee
            $user = User::firstOrCreate([
                'email' => $employeeData['email']
            ], [
                'name' => $employeeData['first_name'] . ' ' . $employeeData['last_name'],
                'password' => Hash::make('password123'),
            ]);

            // Assign employee role if it exists
            if (!$user->hasRole('employee')) {
                try {
                    $user->assignRole('employee');
                } catch (\Exception $e) {
                    // Role might not exist yet, continue
                }
            }

            // Add user_id to employee data
            $employeeData['user_id'] = $user->id;

            // Create employee if doesn't exist
            $employee = Employee::firstOrCreate([
                'email' => $employeeData['email']
            ], $employeeData);

            // Create initial salary record
            EmployeeSalary::firstOrCreate([
                'employee_id' => $employee->id,
                'effective_date' => $employeeData['hire_date'],
            ], [
                'basic_salary' => $employeeData['basic_salary'],
                'salary_type' => $employeeData['salary_type'],
                'hourly_rate' => $employeeData['salary_type'] === 'hourly' ? $employeeData['basic_salary'] : null,
                'overtime_rate' => $employeeData['salary_type'] === 'hourly' ? $employeeData['basic_salary'] * 1.5 : null,
                'allowances' => 0,
                'deductions' => 0,
                'gross_salary' => $employeeData['basic_salary'],
                'net_salary' => $employeeData['basic_salary'],
                'status' => 'active',
                'created_by' => 1, // Admin user
                'approved_by' => 1,
                'approved_at' => now(),
                'notes' => 'Initial salary record',
            ]);
        }

        // Set up manager relationships
        $johnSmith = Employee::where('email', 'john.smith@company.com')->first();
        $sarahJohnson = Employee::where('email', 'sarah.johnson@company.com')->first();
        $mikeDavis = Employee::where('email', 'mike.davis@company.com')->first();
        $emilyWilson = Employee::where('email', 'emily.wilson@company.com')->first();

        // Set Mike's manager to John (HR Manager)
        if ($mikeDavis && $johnSmith) {
            $mikeDavis->update(['manager_id' => $johnSmith->id]);
        }

        // Set Emily's manager to Sarah (IT Manager)
        if ($emilyWilson && $sarahJohnson) {
            $emilyWilson->update(['manager_id' => $sarahJohnson->id]);
        }

        // Update department managers
        $hrDept->update(['manager_id' => $johnSmith->id]);
        $itDept->update(['manager_id' => $sarahJohnson->id]);
    }
}
