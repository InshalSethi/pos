<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

echo "=== TESTING ATTACHMENTS HANDLING ON EMPLOYEE ===\n\n";

// Login as super admin user
$admin = User::first();
if (!$admin) {
    echo "ERROR: Admin user not found\n";
    exit(1);
}
auth()->login($admin);

// Create a dummy uploaded file
Storage::disk('public')->makeDirectory('test_files');
$dummyFilePath = storage_path('app/public/test_files/test_doc.pdf');
file_put_contents($dummyFilePath, '%PDF-1.4 dummy pdf content');

$uploadedFile = new UploadedFile(
    $dummyFilePath,
    'test_doc.pdf',
    'application/pdf',
    null,
    true
);

// Call EmployeeController store via Request
$request = Illuminate\Http\Request::create('/api/employees', 'POST', [
    'first_name' => 'AttachmentTest',
    'last_name' => 'User',
    'email' => 'att_test_' . time() . '@example.com',
    'phone' => '+1 555-987-6543',
    'gender' => 'male',
    'hire_date' => '2026-01-01',
    'employment_type' => 'full_time',
    'employment_status' => 'active',
    'basic_salary' => 5000,
    'salary_type' => 'monthly',
    'create_user_account' => true,
], [], [
    'attachments' => [$uploadedFile]
]);

$controller = new App\Http\Controllers\Api\EmployeeController();
$response = $controller->store($request);

echo "1. Store Response Status: " . $response->getStatusCode() . "\n";
$data = json_decode($response->getContent(), true);
$employeeId = $data['employee']['id'] ?? null;
echo "Created Employee ID: " . ($employeeId ?? 'NONE') . "\n";

if ($employeeId) {
    $emp = Employee::find($employeeId);
    echo "Stored Attachments JSON in Employee: " . json_encode($emp->attachments) . "\n";
    echo "Attachments URLs Accessor Output: " . json_encode($emp->attachments_urls) . "\n";

    // Now test update with retained existing attachments
    $updateRequest = Illuminate\Http\Request::create('/api/employees/' . $employeeId, 'POST', [
        '_method' => 'PUT',
        'first_name' => 'AttachmentTestUpdated',
        'last_name' => 'User',
        'email' => $emp->email,
        'phone' => '+1 555-987-6543',
        'gender' => 'male',
        'hire_date' => '2026-01-01',
        'employment_type' => 'full_time',
        'employment_status' => 'active',
        'basic_salary' => 5500,
        'salary_type' => 'monthly',
        'existing_attachments' => [$emp->attachments[0] ?? ''],
    ]);

    $updateResponse = $controller->update($updateRequest, $emp);
    echo "\n2. Update Response Status: " . $updateResponse->getStatusCode() . "\n";
    
    $emp->refresh();
    echo "After Update Attachments JSON: " . json_encode($emp->attachments) . "\n";
    echo "After Update Attachments URLs Accessor: " . json_encode($emp->attachments_urls) . "\n";

    // Clean up test employee
    $emp->user?->delete();
    $emp->delete();
    echo "\nCleaned up test employee successfully.\n";
}

echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";
