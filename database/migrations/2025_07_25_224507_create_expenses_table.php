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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->unsignedBigInteger('employee_id')->nullable(); // Will add foreign key later
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who created the expense
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('receipt_image')->nullable();
            $table->json('receipt_images')->nullable(); // Multiple receipt images
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'paid'])->default('draft');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_card', 'check', 'petty_cash'])->nullable();
            $table->string('reference_number')->nullable(); // Invoice/receipt reference
            $table->string('vendor_name')->nullable();
            $table->text('notes')->nullable();

            // Approval workflow fields
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Payment tracking
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();

            // Accounting integration
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->onDelete('set null');

            $table->timestamps();

            $table->index(['status']);
            $table->index(['expense_date']);
            $table->index(['category_id']);
            $table->index(['employee_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
