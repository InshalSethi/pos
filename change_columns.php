<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('companies', function (Blueprint $table) {
    $table->string('owner_role')->nullable()->change();
    $table->string('team_size')->nullable()->change();
    $table->json('intended_tasks')->nullable()->change();
    $table->string('business_type')->nullable()->change();
    $table->string('business_scale')->nullable()->change();
    $table->string('country')->nullable()->change();
    $table->string('base_currency')->nullable()->change();
    $table->string('timezone_offset')->nullable()->change();
    $table->string('fiscal_year_start')->nullable()->change();
});

echo "Columns changed successfully.\n";
