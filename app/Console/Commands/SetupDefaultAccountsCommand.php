<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\ChartOfAccountsSetupService;
use Illuminate\Console\Command;

class SetupDefaultAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:default-accounts {company_id? : The ID of a specific company to setup accounts for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds or creates default standard system accounts and maps them into accounting settings for companies.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $companyIdInput = $this->argument('company_id');

        if ($companyIdInput) {
            $company = Company::find($companyIdInput);

            if (!$company) {
                $this->error("Company with ID {$companyIdInput} not found.");
                return Command::FAILURE;
            }

            $this->info("Provisioning default accounts and settings for Company: {$company->company_name} (ID: {$company->id})...");
            
            $settings = ChartOfAccountsSetupService::setupForCompany($company->id);

            $this->info("Successfully provisioned accounts and mapped settings for Company ID: {$company->id}.");
            $this->line("Sales Revenue Account ID: {$settings->sales_invoice_revenue_account_id}");
            $this->line("Accounts Receivable ID: {$settings->sales_invoice_receivable_account_id}");
            $this->line("Cash Account ID: {$settings->cash_account_id}");
            
            return Command::SUCCESS;
        }

        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->warn('No companies found in the database.');
            return Command::SUCCESS;
        }

        $this->info("Found {$companies->count()} company(ies). Provisioning default accounts & settings...");

        $this->output->progressStart($companies->count());

        foreach ($companies as $company) {
            ChartOfAccountsSetupService::setupForCompany($company->id);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info('All companies successfully provisioned with default standard accounts and mapped settings!');

        return Command::SUCCESS;
    }
}
