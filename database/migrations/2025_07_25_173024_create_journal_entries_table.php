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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();
            $table->date('entry_date');
            $table->string('reference')->nullable();
            $table->text('description');
            $table->enum('entry_type', ['manual', 'automatic', 'adjustment', 'closing']);
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->decimal('total_debit', 15, 2);
            $table->decimal('total_credit', 15, 2);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('posted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reversed_at')->nullable();
            $table->string('source_type')->nullable(); // e.g., 'sale', 'purchase', 'payment'
            $table->unsignedBigInteger('source_id')->nullable(); // ID of the source record
            $table->timestamps();

            $table->index(['entry_date']);
            $table->index(['status']);
            $table->index(['entry_type']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
