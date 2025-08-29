<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            
            // Receipt type and reference
            $table->enum('receipt_type', [
                'customer_payment',
                'customer_advance',
                'supplier_refund',
                'supplier_rebate',
                'interest_income',
                'rental_income',
                'commission_income',
                'asset_sale',
                'bank_transfer_in',
                'cash_deposit',
                'miscellaneous_income',
                'other_receipt'
            ]);
            
            // Reference to the related entity (invoice, sale, etc.)
            $table->string('reference_type')->nullable(); // Model class name
            $table->unsignedBigInteger('reference_id')->nullable(); // Model ID
            $table->string('reference_number')->nullable(); // External reference
            
            // Receipt details
            $table->decimal('amount', 15, 2);
            $table->date('receipt_date');
            $table->string('payment_method')->default('bank_transfer'); // cash, bank_transfer, check, card, online
            $table->string('transaction_reference')->nullable(); // Bank transaction ref, check number, etc.
            $table->text('description');
            $table->text('notes')->nullable();
            
            // Bank account information (where money is received)
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            
            // Payer information
            $table->string('payer_type')->nullable(); // 'customer', 'supplier', 'other'
            $table->unsignedBigInteger('payer_id')->nullable();
            $table->string('payer_name'); // For manual entry or backup
            
            // Status and verification
            $table->enum('status', ['draft', 'pending', 'verified', 'deposited', 'cancelled'])->default('draft');
            
            // Verification workflow
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            // Deposit tracking
            $table->foreignId('deposited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('deposited_at')->nullable();
            
            // Accounting integration
            $table->foreignId('journal_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bank_transaction_id')->nullable()->constrained()->onDelete('set null');
            
            // Invoice allocation (for customer payments)
            $table->json('invoice_allocations')->nullable(); // [{invoice_id: 1, amount: 500}, ...]
            
            // Additional fields for specific receipt types
            $table->json('additional_data')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['receipt_type']);
            $table->index(['status']);
            $table->index(['receipt_date']);
            $table->index(['payer_type', 'payer_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['created_by']);
            $table->index(['bank_account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
