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
        if (Schema::hasIndex('licenses', 'licenses_license_key_unique')) {
            Schema::table('licenses', function (Blueprint $table) {
                $table->dropUnique('licenses_license_key_unique');
            });
        }

        Schema::table('licenses', function (Blueprint $table) {
            $table->text('license_key')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('license_key', 255)->change();
        });
    }
};
