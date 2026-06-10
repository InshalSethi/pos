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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('company_email');
            $table->string('company_phone');
            $table->string('company_logo')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('owner_role');
            $table->string('team_size');
            $table->json('intended_tasks');
            $table->string('business_type');
            $table->string('business_scale');
            $table->string('country');
            $table->string('system_language')->default('en');
            $table->string('base_currency');
            $table->string('timezone_offset');
            $table->string('fiscal_year_start');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
