<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('companies', function (Blueprint $table) {
    if (!Schema::hasColumn('companies', 'tax_number')) {
        $table->string('tax_number')->nullable();
    }
    if (!Schema::hasColumn('companies', 'business_address')) {
        $table->text('business_address')->nullable();
    }
});

echo "Columns added successfully.\n";
