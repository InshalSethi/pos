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
        Schema::create('fbr_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('type'); // 'sale', 'purchase', 'transaction', 'payment'
            $table->string('reference_type')->nullable(); // Polymorphic / Model class name
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number'); // Sale #, PO #, Transaction #, Receipt #
            $table->integer('invoice_type')->default(1);
            $table->string('buyer_ntn')->nullable();
            $table->string('buyer_name')->nullable();
            $table->decimal('total_quantity', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->json('payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('fbr_invoice_number')->nullable(); // IRN / USIN returned by FBR
            $table->text('fbr_qr_code')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'synced', 'failed'
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fbr_entries');
    }
};
