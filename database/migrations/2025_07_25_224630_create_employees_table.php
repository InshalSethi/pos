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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Link to user account

            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('national_id')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('profile_image')->nullable();

            // Employment Information
            $table->unsignedBigInteger('department_id')->nullable(); // Will add foreign key later
            $table->unsignedBigInteger('position_id')->nullable(); // Will add foreign key later
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->date('hire_date');
            $table->date('probation_end_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern'])->default('full_time');
            $table->enum('employment_status', ['active', 'inactive', 'terminated', 'on_leave'])->default('active');
            $table->text('termination_reason')->nullable();

            // Salary Information
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->enum('salary_type', ['monthly', 'hourly', 'daily'])->default('monthly');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_email')->nullable();

            // Additional Information
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active']);
            $table->index(['employment_status']);
            $table->index(['department_id']);
            $table->index(['position_id']);
            $table->index(['manager_id']);
            $table->index(['hire_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
