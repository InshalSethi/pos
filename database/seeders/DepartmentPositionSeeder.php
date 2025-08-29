<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;

class DepartmentPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if departments and positions already exist
        if (Department::count() > 0 || Position::count() > 0) {
            $this->command->info('Departments and positions already exist. Skipping department/position seeding.');
            return;
        }

        // Create Departments
        $departments = [
            [
                'name' => 'Administration',
                'code' => 'ADMIN',
                'description' => 'Administrative and management functions',
                'is_active' => true,
            ],
            [
                'name' => 'Sales',
                'code' => 'SALES',
                'description' => 'Sales and customer service operations',
                'is_active' => true,
            ],
            [
                'name' => 'Inventory',
                'code' => 'INV',
                'description' => 'Inventory management and warehouse operations',
                'is_active' => true,
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN',
                'description' => 'Financial management and accounting',
                'is_active' => true,
            ],
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Human resources and employee management',
                'is_active' => true,
            ],
            [
                'name' => 'IT Support',
                'code' => 'IT',
                'description' => 'Information technology and system support',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'code' => 'MKT',
                'description' => 'Marketing and promotional activities',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        // Create Positions
        $positions = [
            // Administration
            [
                'title' => 'General Manager',
                'code' => 'GM',
                'description' => 'Overall management and strategic direction',
                'department_id' => Department::where('code', 'ADMIN')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'Assistant Manager',
                'code' => 'AM',
                'description' => 'Assists in management duties and operations',
                'department_id' => Department::where('code', 'ADMIN')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'Store Supervisor',
                'code' => 'SUP',
                'description' => 'Supervises daily store operations',
                'department_id' => Department::where('code', 'ADMIN')->first()->id,
                'is_active' => true,
            ],

            // Sales
            [
                'title' => 'Sales Manager',
                'code' => 'SM',
                'description' => 'Manages sales team and customer relations',
                'department_id' => Department::where('code', 'SALES')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'Sales Associate',
                'code' => 'SA',
                'description' => 'Handles customer sales and service',
                'department_id' => Department::where('code', 'SALES')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'Cashier',
                'code' => 'CASH',
                'description' => 'Processes customer transactions',
                'department_id' => Department::where('code', 'SALES')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'Customer Service Representative',
                'code' => 'CSR',
                'description' => 'Provides customer support and assistance',
                'department_id' => Department::where('code', 'SALES')->first()->id,
                'is_active' => true,
            ],

            // Inventory
            [
                'title' => 'Inventory Manager',
                'code' => 'IM',
                'description' => 'Manages inventory levels and procurement',
                'department_id' => Department::where('code', 'INV')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'Warehouse Supervisor',
                'code' => 'WS',
                'description' => 'Supervises warehouse operations',
                'department_id' => Department::where('code', 'INV')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'Stock Clerk',
                'code' => 'SC',
                'description' => 'Manages stock receiving and organization',
                'department_id' => Department::where('code', 'INV')->first()->id,
                'is_active' => true,
            ],

            // Finance
            [
                'title' => 'Finance Manager',
                'code' => 'FM',
                'description' => 'Manages financial operations and reporting',
                'department_id' => Department::where('code', 'FIN')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'Accountant',
                'code' => 'ACC',
                'description' => 'Handles accounting and bookkeeping',
                'department_id' => Department::where('code', 'FIN')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'Accounts Payable Clerk',
                'code' => 'APC',
                'description' => 'Manages supplier payments and invoices',
                'department_id' => Department::where('code', 'FIN')->first()->id,
                'is_active' => true,
            ],

            // Human Resources
            [
                'title' => 'HR Manager',
                'code' => 'HRM',
                'description' => 'Manages human resources and employee relations',
                'department_id' => Department::where('code', 'HR')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'HR Assistant',
                'code' => 'HRA',
                'description' => 'Assists with HR administrative tasks',
                'department_id' => Department::where('code', 'HR')->first()->id,
                'is_active' => true,
            ],

            // IT Support
            [
                'title' => 'IT Manager',
                'code' => 'ITM',
                'description' => 'Manages IT infrastructure and systems',
                'department_id' => Department::where('code', 'IT')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'IT Support Specialist',
                'code' => 'ITS',
                'description' => 'Provides technical support and maintenance',
                'department_id' => Department::where('code', 'IT')->first()->id,
                'is_active' => true,
            ],

            // Marketing
            [
                'title' => 'Marketing Manager',
                'code' => 'MM',
                'description' => 'Manages marketing campaigns and promotions',
                'department_id' => Department::where('code', 'MKT')->first()->id,
                'is_active' => true,
            ],
            [
                'title' => 'Marketing Assistant',
                'code' => 'MA',
                'description' => 'Assists with marketing activities and events',
                'department_id' => Department::where('code', 'MKT')->first()->id,
                'is_active' => true,
            ],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(['code' => $position['code']], $position);
        }

        $this->command->info('Departments and positions seeded successfully!');
    }
}
