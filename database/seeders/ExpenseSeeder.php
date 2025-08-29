<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required data
        $categories = ExpenseCategory::all();
        $employees = Employee::all();
        $users = User::all();

        if ($categories->isEmpty()) {
            $this->command->error('No expense categories found. Please run ExpenseCategorySeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        // Sample expense data with different statuses
        $expenses = [
            [
                'title' => 'Office Supplies Purchase',
                'description' => 'Purchased pens, papers, and other office supplies',
                'amount' => 150.00,
                'status' => 'draft',
                'vendor_name' => 'Office Depot',
                'payment_method' => 'credit_card',
                'expense_date' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Business Lunch Meeting',
                'description' => 'Lunch meeting with potential client',
                'amount' => 85.50,
                'status' => 'draft',
                'vendor_name' => 'Restaurant ABC',
                'payment_method' => 'cash',
                'expense_date' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Travel Expenses',
                'description' => 'Flight tickets for business trip',
                'amount' => 450.00,
                'status' => 'submitted',
                'vendor_name' => 'Airlines Inc',
                'payment_method' => 'bank_transfer',
                'expense_date' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Software License',
                'description' => 'Annual software license renewal',
                'amount' => 299.99,
                'status' => 'approved',
                'vendor_name' => 'Software Company',
                'payment_method' => 'credit_card',
                'expense_date' => Carbon::now()->subDays(4),
            ],
            [
                'title' => 'Marketing Materials',
                'description' => 'Printed brochures and business cards',
                'amount' => 125.00,
                'status' => 'rejected',
                'vendor_name' => 'Print Shop',
                'payment_method' => 'cash',
                'expense_date' => Carbon::now()->subDays(5),
                'rejection_reason' => 'Exceeds budget allocation for marketing materials',
            ],
            [
                'title' => 'Equipment Maintenance',
                'description' => 'Printer maintenance and repair',
                'amount' => 75.00,
                'status' => 'paid',
                'vendor_name' => 'Tech Repair Co',
                'payment_method' => 'bank_transfer',
                'expense_date' => Carbon::now()->subDays(6),
                'payment_reference' => 'TXN-123456',
            ],
            [
                'title' => 'Training Course',
                'description' => 'Professional development training',
                'amount' => 350.00,
                'status' => 'draft',
                'vendor_name' => 'Training Institute',
                'payment_method' => 'credit_card',
                'expense_date' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Parking Fees',
                'description' => 'Monthly parking fees for office',
                'amount' => 120.00,
                'status' => 'submitted',
                'vendor_name' => 'Parking Management',
                'payment_method' => 'bank_transfer',
                'expense_date' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($expenses as $expenseData) {
            $category = $categories->random();
            $user = $users->random();
            $employee = $employees->isNotEmpty() ? $employees->random() : null;

            $expense = Expense::create([
                'expense_number' => Expense::generateExpenseNumber(),
                'category_id' => $category->id,
                'employee_id' => $employee?->id,
                'user_id' => $user->id,
                'title' => $expenseData['title'],
                'description' => $expenseData['description'],
                'amount' => $expenseData['amount'],
                'expense_date' => $expenseData['expense_date'],
                'status' => $expenseData['status'],
                'vendor_name' => $expenseData['vendor_name'],
                'payment_method' => $expenseData['payment_method'],
                'reference_number' => 'REF-' . strtoupper(uniqid()),
                'notes' => 'Generated by seeder',
            ]);

            // Set additional fields based on status
            switch ($expenseData['status']) {
                case 'submitted':
                    $expense->update([
                        'submitted_by' => $user->id,
                        'submitted_at' => $expenseData['expense_date']->addHours(2),
                    ]);
                    break;

                case 'approved':
                    $expense->update([
                        'submitted_by' => $user->id,
                        'submitted_at' => $expenseData['expense_date']->addHours(2),
                        'approved_by' => $users->random()->id,
                        'approved_at' => $expenseData['expense_date']->addHours(4),
                        'approval_notes' => 'Approved for payment',
                    ]);
                    break;

                case 'rejected':
                    $expense->update([
                        'submitted_by' => $user->id,
                        'submitted_at' => $expenseData['expense_date']->addHours(2),
                        'rejected_by' => $users->random()->id,
                        'rejected_at' => $expenseData['expense_date']->addHours(4),
                        'rejection_reason' => $expenseData['rejection_reason'] ?? 'Does not meet expense policy requirements',
                    ]);
                    break;

                case 'paid':
                    $expense->update([
                        'submitted_by' => $user->id,
                        'submitted_at' => $expenseData['expense_date']->addHours(2),
                        'approved_by' => $users->random()->id,
                        'approved_at' => $expenseData['expense_date']->addHours(4),
                        'approval_notes' => 'Approved for payment',
                        'paid_by' => $users->random()->id,
                        'paid_at' => $expenseData['expense_date']->addHours(6),
                        'payment_reference' => $expenseData['payment_reference'] ?? 'PAY-' . strtoupper(uniqid()),
                    ]);
                    break;
            }
        }

        $this->command->info('Expense test data seeded successfully!');
        $this->command->info('Created expenses with statuses: draft, submitted, approved, rejected, paid');
        $this->command->info('You can now see edit/delete buttons for draft and rejected expenses.');
    }
}
