<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('notes');
        });

        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('notes');
        });

        // Migrate existing single attachment string into attachments JSON array
        DB::table('payments')->whereNotNull('attachment')->get()->each(function ($p) {
            if ($p->attachment) {
                DB::table('payments')->where('id', $p->id)->update([
                    'attachments' => json_encode([$p->attachment])
                ]);
            }
        });

        DB::table('payment_receipts')->whereNotNull('attachment')->get()->each(function ($pr) {
            if ($pr->attachment) {
                DB::table('payment_receipts')->where('id', $pr->id)->update([
                    'attachments' => json_encode([$pr->attachment])
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });

        Schema::table('payment_receipts', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
