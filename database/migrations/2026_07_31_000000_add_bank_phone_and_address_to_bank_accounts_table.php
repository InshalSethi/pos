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
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_accounts', 'bank_phone')) {
                $table->string('bank_phone')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('bank_accounts', 'bank_address')) {
                $table->text('bank_address')->nullable()->after('bank_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('bank_accounts', 'bank_phone')) {
                $table->dropColumn('bank_phone');
            }
            if (Schema::hasColumn('bank_accounts', 'bank_address')) {
                $table->dropColumn('bank_address');
            }
        });
    }
};
