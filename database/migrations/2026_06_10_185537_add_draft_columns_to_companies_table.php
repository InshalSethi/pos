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
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'status')) {
                $table->string('status')->default('active'); // 'active' | 'draft'
            }
            if (!Schema::hasColumn('companies', 'draft_step')) {
                $table->unsignedTinyInteger('draft_step')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['status', 'draft_step']);
        });
    }
};
