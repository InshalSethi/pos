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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name');
            $table->string('account_number');
            $table->string('bank_name');
            $table->string('bank_branch')->nullable();
            $table->enum('account_type', ['checking', 'savings', 'credit_card', 'line_of_credit', 'other']);
            $table->string('routing_number')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('iban')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->date('opening_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->date('last_reconciled_date')->nullable();
            $table->decimal('last_statement_balance', 15, 2)->nullable();
            $table->foreignId('chart_account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['is_active']);
            $table->index(['currency']);
            $table->index(['account_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
