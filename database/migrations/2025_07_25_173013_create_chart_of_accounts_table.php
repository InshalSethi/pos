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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();
            $table->string('account_name');
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('account_subtype', [
                'current_asset', 'fixed_asset', 'other_asset',
                'current_liability', 'long_term_liability', 'other_liability',
                'equity',
                'operating_revenue', 'other_revenue',
                'cost_of_goods_sold', 'operating_expense', 'other_expense'
            ]);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system_account')->default(false);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->unsignedBigInteger('parent_account_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->index(['account_type']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
