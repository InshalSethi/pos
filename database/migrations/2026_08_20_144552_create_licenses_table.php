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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_key')->unique();
            $table->string('device_id')->nullable();
            $table->string('plan')->default('starter');
            $table->string('status')->default('active'); // active, expired, revoked
            $table->date('start_date')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamp('last_opened_at')->nullable(); // For time-tampering protection
            $table->json('features')->nullable(); // Optional dynamic limits (e.g. max users)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
