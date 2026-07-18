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
        Schema::table('sales', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('sale_date');
            $table->string('order_number')->nullable()->after('sale_number');
            $table->text('footer')->nullable()->after('notes');
            $table->string('color')->nullable()->after('status');
            $table->foreignId('category_id')->nullable()->after('customer_id')->constrained('categories')->onDelete('set null');
            $table->text('attachments')->nullable()->after('footer'); // JSON list of file details or paths
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['due_date', 'order_number', 'footer', 'color', 'category_id', 'attachments']);
        });
    }
};
