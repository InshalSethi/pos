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
        Schema::table('hardware_settings', function (Blueprint $table) {
            $table->string('barcode_scanner_selected_device')->nullable()->after('barcode_scanner_mode');
            $table->string('qr_scanner_selected_device')->nullable()->after('qr_scanner_mode');
            $table->string('barcode_printer_selected_device')->nullable()->after('barcode_printer_model');
            $table->string('thermal_printer_selected_device')->nullable()->after('thermal_printer_paper_size');
            $table->string('standard_printer_selected_device')->nullable()->after('standard_printer_paper_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hardware_settings', function (Blueprint $table) {
            $table->dropColumn([
                'barcode_scanner_selected_device',
                'qr_scanner_selected_device',
                'barcode_printer_selected_device',
                'thermal_printer_selected_device',
                'standard_printer_selected_device',
            ]);
        });
    }
};
