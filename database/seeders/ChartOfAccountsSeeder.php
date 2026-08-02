<?php

namespace Database\Seeders;

use App\Services\ChartOfAccountService;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Provisioning Enterprise Chart of Accounts for all companies...');
        ChartOfAccountService::seedAllCompanies();
    }
}
