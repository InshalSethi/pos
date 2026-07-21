<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();
foreach ($users as $user) {
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Google ID: " . $user->google_id . "\n";
    echo "Current Company ID: " . $user->current_company_id . "\n";
    echo "Onboarding Completed: " . ($user->onboarding_completed ? 'Yes' : 'No') . "\n";
    echo "is_setup_completed: " . ($user->is_setup_completed ? 'Yes' : 'No') . "\n";
    echo "--------------------------\n";
}
echo "Total companies: " . \App\Models\Company::count() . "\n";
