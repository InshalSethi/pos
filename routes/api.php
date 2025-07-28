<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\InventoryAdjustmentController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\BankTransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentSettingsController;
use App\Http\Controllers\Api\PurchaseReturnController;
use App\Http\Controllers\Api\FinancialReportController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\EmployeeUserController;
use App\Http\Controllers\Api\EmployeeSalaryController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\SalaryAdjustmentController;
use App\Http\Controllers\Api\CustomerLedgerController;
use App\Http\Controllers\Api\SupplierLedgerController;

// Public routes
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);

// Test route for debugging
Route::get('/test', function () {
    return response()->json(['message' => 'API is working', 'time' => now()]);
});

// Test dropdown route (temporary fallback - remove in production)
Route::get('/test-dropdown', function() {
    $employees = \App\Models\Employee::active()
        ->select('id', 'first_name', 'last_name', 'middle_name', 'employee_number')
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->get()
        ->map(function ($employee) {
            return [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'employee_number' => $employee->employee_number,
            ];
        });

    return response()->json($employees);
});

// Test employee list route (temporary - remove in production)
Route::get('/test-employee-list', function() {
    $employees = \App\Models\Employee::with(['department', 'position', 'manager'])
        ->paginate(15);

    return response()->json($employees);
});

// Test departments route (temporary - remove in production)
Route::get('/test-departments', function() {
    $departments = \App\Models\Department::with(['manager', 'parent', 'children', 'employees'])
        ->orderBy('name')
        ->get();

    return response()->json($departments);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // User profile routes
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/profile-image', [AuthController::class, 'uploadProfileImage']);
    Route::get('/user/settings', [AuthController::class, 'getSettings']);
    Route::put('/user/settings', [AuthController::class, 'updateSettings']);

    // Product management routes
    Route::apiResource('products', ProductController::class);
    Route::post('/products/import', [ProductController::class, 'import']);
    Route::get('/products/export', [ProductController::class, 'export']);
    Route::get('/products/download-template', [ProductController::class, 'downloadTemplate']);
    Route::apiResource('categories', CategoryController::class);

    // Customer management routes
    Route::get('/customers/statistics', [CustomerController::class, 'getStatistics']);
    Route::get('/customers/search/quick', [CustomerController::class, 'quickSearch']);
    Route::get('/customers/{customer}/purchase-history', [CustomerController::class, 'getPurchaseHistory']);
    Route::post('/customers/{customer}/credit-limit', [CustomerController::class, 'updateCreditLimit']);
    Route::post('/customers/{customer}/loyalty-points', [CustomerController::class, 'addLoyaltyPoints']);
    Route::post('/customers/{customer}/deactivate', [CustomerController::class, 'deactivate']);
    Route::post('/customers/{customer}/reactivate', [CustomerController::class, 'reactivate']);
    Route::apiResource('customers', CustomerController::class);

    // Customer Ledger and Accounting routes
    Route::get('/customers/{customer}/ledger', [CustomerLedgerController::class, 'getLedger']);
    Route::get('/customers/{customer}/aging-report', [CustomerLedgerController::class, 'getAgingReport']);
    Route::get('/customers/{customer}/statement', [CustomerLedgerController::class, 'getStatement']);
    Route::get('/customers/{customer}/transaction-summary', [CustomerLedgerController::class, 'getTransactionSummary']);

    // Sales/POS routes
    Route::apiResource('sales', SaleController::class);
    Route::post('/sales/{sale}/refund', [SaleController::class, 'refund']);
    Route::get('/sales/statistics/summary', [SaleController::class, 'statistics']);

    // Inventory management routes
    Route::get('/suppliers/statistics', [SupplierController::class, 'getStatistics']);
    Route::get('/suppliers/search/quick', [SupplierController::class, 'quickSearch']);
    Route::get('/suppliers/{supplier}/purchase-history', [SupplierController::class, 'getPurchaseHistory']);
    Route::post('/suppliers/{supplier}/credit-limit', [SupplierController::class, 'updateCreditLimit']);
    Route::post('/suppliers/{supplier}/payment-terms', [SupplierController::class, 'updatePaymentTerms']);
    Route::post('/suppliers/{supplier}/deactivate', [SupplierController::class, 'deactivate']);
    Route::post('/suppliers/{supplier}/reactivate', [SupplierController::class, 'reactivate']);
    Route::apiResource('suppliers', SupplierController::class);

    // Supplier Ledger and Accounting routes
    Route::get('/suppliers/{supplier}/ledger', [SupplierLedgerController::class, 'getLedger']);
    Route::get('/suppliers/{supplier}/aging-report', [SupplierLedgerController::class, 'getAgingReport']);
    Route::get('/suppliers/{supplier}/statement', [SupplierLedgerController::class, 'getStatement']);
    Route::get('/suppliers/{supplier}/transaction-summary', [SupplierLedgerController::class, 'getTransactionSummary']);

    Route::apiResource('purchase-orders', PurchaseOrderController::class);
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);

    Route::apiResource('inventory-adjustments', InventoryAdjustmentController::class)->only(['index', 'store', 'show']);
    Route::get('/inventory/summary', [InventoryAdjustmentController::class, 'summary']);
    Route::get('/inventory/low-stock', [InventoryAdjustmentController::class, 'lowStock']);

    // Accounting routes
    Route::apiResource('accounts', AccountController::class);
    Route::get('/accounts/tree/structure', [AccountController::class, 'tree']);
    Route::get('/accounts/balances/summary', [AccountController::class, 'balances']);

    Route::apiResource('journal-entries', JournalEntryController::class);
    Route::post('/journal-entries/{journalEntry}/post', [JournalEntryController::class, 'post']);
    Route::post('/journal-entries/{journalEntry}/reverse', [JournalEntryController::class, 'reverse']);

    // Banking routes
    Route::apiResource('bank-accounts', BankAccountController::class);
    Route::get('/bank-accounts/{bankAccount}/transactions', [BankAccountController::class, 'transactions']);
    Route::post('/bank-accounts/{bankAccount}/reconcile', [BankAccountController::class, 'reconcile']);
    Route::get('/bank-accounts/{bankAccount}/reconciliation-summary', [BankAccountController::class, 'reconciliationSummary']);

    Route::apiResource('bank-transactions', BankTransactionController::class);
    Route::post('/bank-transactions/{bankTransaction}/match', [BankTransactionController::class, 'match']);
    Route::post('/bank-transactions/import', [BankTransactionController::class, 'import']);

    // User management routes
    Route::apiResource('users', UserController::class);
    Route::get('/roles', [UserController::class, 'roles']);

    // Role management routes
    Route::apiResource('roles', RoleController::class);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // Payment settings routes
    Route::get('/payment-settings', [PaymentSettingsController::class, 'index']);
    Route::put('/payment-settings', [PaymentSettingsController::class, 'update']);
    Route::post('/payment-settings/test-connection', [PaymentSettingsController::class, 'testConnection']);

    // Purchase return routes
    Route::apiResource('purchase-returns', PurchaseReturnController::class);
    Route::post('/purchase-returns/{purchaseReturn}/approve', [PurchaseReturnController::class, 'approve']);
    Route::post('/purchase-returns/{purchaseReturn}/reject', [PurchaseReturnController::class, 'reject']);

    // Financial report routes
    Route::get('/reports/financial/profit-loss', [FinancialReportController::class, 'profitLoss']);
    Route::get('/reports/financial/balance-sheet', [FinancialReportController::class, 'balanceSheet']);
    Route::get('/reports/financial/trial-balance', [FinancialReportController::class, 'trialBalance']);
    Route::get('/reports/financial/cash-flow', [FinancialReportController::class, 'cashFlow']);
    Route::get('/permissions', [RoleController::class, 'permissions']);

    // Report routes
    Route::prefix('reports')->group(function () {
        Route::get('/sales/summary', [ReportController::class, 'salesSummary']);
        Route::get('/sales/monthly-revenue', [ReportController::class, 'monthlyRevenue']);
        Route::get('/sales/top-products', [ReportController::class, 'topSellingProducts']);
        Route::get('/sales/customer-analysis', [ReportController::class, 'customerSalesAnalysis']);
        Route::get('/inventory/summary', [ReportController::class, 'inventoryReport']);
        Route::get('/inventory/low-stock', [ReportController::class, 'lowStockAlert']);
        Route::get('/inventory/valuation', [ReportController::class, 'inventoryValuation']);
        Route::get('/inventory/stock-movement', [ReportController::class, 'stockMovementHistory']);
    });

    // Expense management routes
    Route::apiResource('expense-categories', ExpenseCategoryController::class);
    Route::get('/expense-categories/tree/structure', [ExpenseCategoryController::class, 'tree']);

    Route::apiResource('expenses', ExpenseController::class);
    Route::post('/expenses/{expense}/submit', [ExpenseController::class, 'submit']);
    Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve']);
    Route::post('/expenses/{expense}/reject', [ExpenseController::class, 'reject']);
    Route::post('/expenses/{expense}/mark-as-paid', [ExpenseController::class, 'markAsPaid']);
    Route::get('/expenses/statistics/summary', [ExpenseController::class, 'statistics']);

    // Employee management routes
    Route::apiResource('departments', DepartmentController::class);
    Route::get('/departments/tree/structure', [DepartmentController::class, 'tree']);

    Route::apiResource('positions', PositionController::class);

    // Employee specific routes (must come before resource routes)
    Route::get('/employees/statistics/summary', [EmployeeController::class, 'statistics']);
    Route::get('/employees/for-dropdown', [EmployeeController::class, 'forDropdown']);
    Route::get('/employees/without-accounts', [EmployeeUserController::class, 'employeesWithoutAccounts']);
    Route::get('/employees/audit-user-relationships', [EmployeeUserController::class, 'auditRelationships']);
    Route::post('/employees/bulk-create-user-accounts', [EmployeeUserController::class, 'bulkCreateUserAccounts']);
    Route::post('/employees/{employee}/terminate', [EmployeeController::class, 'terminate']);
    Route::post('/employees/{employee}/reactivate', [EmployeeController::class, 'reactivate']);
    Route::post('/employees/{employee}/create-user-account', [EmployeeUserController::class, 'createUserAccount']);
    Route::post('/employees/{employee}/sync-user-account', [EmployeeUserController::class, 'syncUserAccount']);
    Route::post('/employees/{employee}/reset-password', [EmployeeUserController::class, 'resetPassword']);
    Route::post('/employees/{employee}/deactivate-user-account', [EmployeeUserController::class, 'deactivateUserAccount']);
    Route::post('/employees/{employee}/reactivate-user-account', [EmployeeUserController::class, 'reactivateUserAccount']);

    Route::apiResource('employees', EmployeeController::class);

    // Employee Salary Management routes
    Route::get('/employee-salaries/statistics', [EmployeeSalaryController::class, 'getStatistics']);
    Route::get('/employees/{employee}/salary/current', [EmployeeSalaryController::class, 'getCurrentSalary']);
    Route::get('/employees/{employee}/salary/history', [EmployeeSalaryController::class, 'getSalaryHistory']);
    Route::post('/employees/{employee}/salary/adjustment', [EmployeeSalaryController::class, 'createAdjustment']);
    Route::post('/employee-salaries/{employeeSalary}/approve', [EmployeeSalaryController::class, 'approve']);
    Route::apiResource('employee-salaries', EmployeeSalaryController::class);

    // Payroll Management routes
    Route::get('/payroll/statistics', [PayrollController::class, 'getStatistics']);
    Route::post('/payroll/generate-all', [PayrollController::class, 'generateForAllEmployees']);
    Route::post('/employees/{employee}/payroll/generate', [PayrollController::class, 'generateForEmployee']);
    Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve']);
    Route::post('/payroll/{payroll}/mark-as-paid', [PayrollController::class, 'markAsPaid']);
    Route::post('/payroll/{payroll}/cancel', [PayrollController::class, 'cancel']);
    Route::apiResource('payroll', PayrollController::class);

    // Salary Adjustment routes
    Route::get('/salary-adjustments/statistics', [SalaryAdjustmentController::class, 'getStatistics']);
    Route::get('/salary-adjustments/pending-approvals', [SalaryAdjustmentController::class, 'getPendingApprovals']);
    Route::get('/salary-adjustments/ready-for-implementation', [SalaryAdjustmentController::class, 'getReadyForImplementation']);
    Route::post('/salary-adjustments/{salaryAdjustment}/approve', [SalaryAdjustmentController::class, 'approve']);
    Route::post('/salary-adjustments/{salaryAdjustment}/reject', [SalaryAdjustmentController::class, 'reject']);
    Route::post('/salary-adjustments/{salaryAdjustment}/implement', [SalaryAdjustmentController::class, 'implement']);
    Route::apiResource('salary-adjustments', SalaryAdjustmentController::class);

    // Additional API routes will be added here
});
