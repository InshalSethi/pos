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
        Schema::create('hardware_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('terminal_name')->default('Default Counter / Terminal 1');

            // 1. Barcode Scanner Settings
            $table->boolean('barcode_scanner_enabled')->default(true);
            $table->string('barcode_scanner_mode')->default('hid_keyboard'); // hid_keyboard, webhid, serial
            $table->string('barcode_scanner_suffix')->default('enter'); // enter, lf, tab, none
            $table->string('barcode_scanner_prefix')->nullable();
            $table->integer('barcode_scanner_sensitivity')->default(80); // max ms between keystrokes
            $table->boolean('barcode_scanner_sound')->default(true);
            $table->boolean('barcode_scanner_auto_clear')->default(true);
            $table->boolean('barcode_scanner_auto_increment')->default(true);

            // 2. QR Code Scanner Settings
            $table->boolean('qr_scanner_enabled')->default(true);
            $table->string('qr_scanner_mode')->default('hardware_2d'); // hardware_2d, camera, hybrid
            $table->string('qr_scanner_camera_device_id')->nullable();
            $table->string('qr_scanner_auto_action')->default('product_lookup'); // product_lookup, customer_lookup, fiscal_qr
            $table->boolean('qr_scanner_sound')->default(true);

            // 3. Barcode Label Printer Settings
            $table->boolean('barcode_printer_enabled')->default(true);
            $table->string('barcode_printer_model')->default('tspl'); // tspl, zpl, escpos_label, system_spooler
            $table->string('barcode_printer_connection')->default('system_print'); // system_print, webusb, webserial, network_ip
            $table->string('barcode_printer_ip')->nullable();
            $table->integer('barcode_printer_port')->default(9100);
            $table->string('barcode_printer_paper_width')->default('50mm');
            $table->string('barcode_printer_paper_height')->default('25mm');
            $table->integer('barcode_printer_darkness')->default(8);
            $table->integer('barcode_printer_dpi')->default(203);

            // 4. Thermal Receipt Printer Settings
            $table->boolean('thermal_printer_enabled')->default(true);
            $table->string('thermal_printer_paper_size')->default('80mm'); // 80mm, 58mm
            $table->string('thermal_printer_connection')->default('system_print'); // system_print, webusb, webserial, network_ip
            $table->string('thermal_printer_ip')->nullable();
            $table->integer('thermal_printer_port')->default(9100);
            $table->string('thermal_printer_auto_cutter')->default('full'); // full, partial, none
            $table->string('thermal_printer_cash_drawer_pulse')->default('pin2'); // pin2, pin5, disabled
            $table->string('thermal_printer_density')->default('normal'); // normal, dark, extra_dark
            $table->boolean('thermal_printer_auto_print')->default(false);

            // 5. Standard Printer Settings
            $table->boolean('standard_printer_enabled')->default(true);
            $table->string('standard_printer_paper_size')->default('a4'); // a4, letter, legal
            $table->string('standard_printer_color_mode')->default('color'); // color, grayscale
            $table->string('standard_printer_orientation')->default('portrait'); // portrait, landscape
            $table->boolean('standard_printer_auto_print')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware_settings');
    }
};
