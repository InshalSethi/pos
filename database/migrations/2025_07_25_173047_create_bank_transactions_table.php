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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->date('transaction_date');
            $table->string('reference_number')->nullable();
            $table->text('description');
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->decimal('running_balance', 15, 2);
            $table->enum('status', ['pending', 'cleared', 'reconciled'])->default('pending');
            $table->foreignId('journal_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->string('partner_type')->nullable(); // 'customer', 'supplier', etc.
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['bank_account_id']);
            $table->index(['transaction_date']);
            $table->index(['status']);
            $table->index(['partner_type', 'partner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
