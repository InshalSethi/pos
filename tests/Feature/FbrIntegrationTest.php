<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\FbrEntry;
use App\Models\FbrSetting;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FbrIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $companyA;
    protected Company $companyB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->companyA = Company::factory()->create([
            'user_id' => $this->user->id,
            'company_name' => 'Company A POS',
        ]);

        $this->companyB = Company::factory()->create([
            'user_id' => $this->user->id,
            'company_name' => 'Company B POS',
        ]);

        $this->user->update(['current_company_id' => $this->companyA->id]);

        $this->actingAs($this->user);
    }

    public function test_fbr_settings_can_be_retrieved_and_updated_per_company()
    {
        $response = $this->getJson('/api/fbr-settings?company_id=' . $this->companyA->id);
        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $updateResponse = $this->putJson('/api/fbr-settings', [
            'company_id' => $this->companyA->id,
            'is_enabled' => true,
            'environment' => 'sandbox',
            'pos_id' => '100999',
            'ntn' => '7654321-0',
            'strn' => '1122334455667',
            'business_name' => 'Company A Retail',
            'branch_name' => 'Branch 1',
            'api_token' => 'sample_sandbox_token',
            'base_url' => 'https://sandbox.fbr.gov.pk/api/v1',
            'auto_sync' => true,
            'sync_sales' => true,
            'sync_purchases' => true,
            'sync_transactions' => true,
            'sync_payments' => true,
        ]);

        $updateResponse->assertStatus(200)
                       ->assertJsonPath('success', true)
                       ->assertJsonPath('setting.pos_id', '100999');

        $this->assertDatabaseHas('fbr_settings', [
            'company_id' => $this->companyA->id,
            'is_enabled' => true,
            'pos_id' => '100999',
        ]);
    }

    public function test_sales_are_recorded_to_fbr_when_fbr_is_enabled_for_company()
    {
        FbrSetting::create([
            'company_id' => $this->companyA->id,
            'is_enabled' => true,
            'pos_id' => '100123',
            'auto_sync' => true,
            'sync_sales' => true,
        ]);

        $sale = Sale::create([
            'user_id' => $this->user->id,
            'company_id' => $this->companyA->id,
            'sale_number' => 'INV-TEST-001',
            'sale_date' => now(),
            'total_amount' => 1500.00,
            'subtotal' => 1500.00,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('fbr_entries', [
            'company_id' => $this->companyA->id,
            'reference_number' => 'INV-TEST-001',
            'type' => 'sale',
            'status' => 'synced',
        ]);

        $fbrEntry = FbrEntry::where('reference_number', 'INV-TEST-001')->first();
        $this->assertNotNull($fbrEntry->fbr_invoice_number);
        $this->assertNotNull($fbrEntry->fbr_qr_code);
    }

    public function test_sales_are_not_recorded_when_fbr_is_disabled_for_company()
    {
        FbrSetting::create([
            'company_id' => $this->companyB->id,
            'is_enabled' => false,
        ]);

        $sale = Sale::create([
            'user_id' => $this->user->id,
            'company_id' => $this->companyB->id,
            'sale_number' => 'INV-TEST-COMPB',
            'sale_date' => now(),
            'total_amount' => 2000.00,
            'subtotal' => 2000.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        $this->assertDatabaseMissing('fbr_entries', [
            'company_id' => $this->companyB->id,
            'reference_number' => 'INV-TEST-COMPB',
        ]);
    }
}
