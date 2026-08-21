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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->string('category')->default('Equipment'); // 'Furniture & Fixtures', 'Machinery & Equipment', 'Vehicles', 'Electronics & IT', 'Buildings & Shop', 'Other'
            $table->string('serial_number')->nullable();
            $table->integer('quantity')->default(1);
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->decimal('current_value', 15, 2);
            $table->decimal('salvage_value', 15, 2)->default(0.00);
            $table->integer('useful_life_years')->default(5);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'none'])->default('straight_line');
            $table->decimal('accumulated_depreciation', 15, 2)->default(0.00);
            $table->string('location')->nullable(); // e.g. "Main Kitchen", "Front Dining", "Bakery Workshop"
            $table->enum('status', ['active', 'in_maintenance', 'disposed', 'written_off'])->default('active');
            $table->foreignId('chart_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
