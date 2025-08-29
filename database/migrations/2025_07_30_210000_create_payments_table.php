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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            
            // Payment type and reference
            $table->enum('payment_type', [
                'supplier_payment',
                'expense_payment', 
                'salary_payment',
                'sale_return_payment',
                'purchase_invoice_payment',
                'other_payment'
            ]);
            
            // Reference to the related entity
            $table->string('reference_type')->nullable(); // Model class name
            $table->unsignedBigInteger('reference_id')->nullable(); // Model ID
            
            // Payment details
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method')->default('bank_transfer'); // cash, bank_transfer, check, card
            $table->string('reference_number')->nullable();
            $table->text('description');
            $table->text('notes')->nullable();
            
            // Bank account information
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            
            // Payee information
            $table->string('payee_type')->nullable(); // 'supplier', 'employee', 'customer', 'other'
            $table->unsignedBigInteger('payee_id')->nullable();
            $table->string('payee_name'); // For manual entry or backup
            
            // Status and approval
            $table->enum('status', ['draft', 'pending', 'approved', 'paid', 'cancelled'])->default('draft');
            
            // Approval workflow
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Payment execution
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('paid_at')->nullable();
            
            // Accounting integration
            $table->foreignId('journal_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bank_transaction_id')->nullable()->constrained()->onDelete('set null');
            
            // Additional fields for specific payment types
            $table->json('additional_data')->nullable(); // For storing type-specific data
            
            $table->timestamps();
            
            // Indexes
            $table->index(['payment_type']);
            $table->index(['status']);
            $table->index(['payment_date']);
            $table->index(['payee_type', 'payee_id']);
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
        Schema::dropIfExists('payments');
    }
};
