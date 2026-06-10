<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select('SHOW COLUMNS FROM companies');

foreach ($columns as $col) {
    if ($col->Null === 'NO' && $col->Default === null && $col->Extra !== 'auto_increment') {
        echo "Column " . $col->Field . " is NOT NULL with NO DEFAULT.\n";
    }
}
echo "Done.\n";
