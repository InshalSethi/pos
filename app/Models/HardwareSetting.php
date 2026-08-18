<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HardwareSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'barcode_scanner_enabled' => 'boolean',
        'barcode_scanner_sound' => 'boolean',
        'barcode_scanner_auto_clear' => 'boolean',
        'barcode_scanner_auto_increment' => 'boolean',
        'qr_scanner_enabled' => 'boolean',
        'qr_scanner_sound' => 'boolean',
        'barcode_printer_enabled' => 'boolean',
        'thermal_printer_enabled' => 'boolean',
        'thermal_printer_auto_print' => 'boolean',
        'standard_printer_enabled' => 'boolean',
        'standard_printer_auto_print' => 'boolean',
        'barcode_scanner_sensitivity' => 'integer',
        'barcode_printer_port' => 'integer',
        'barcode_printer_darkness' => 'integer',
        'barcode_printer_dpi' => 'integer',
        'thermal_printer_port' => 'integer',
    ];

    /**
     * Get or create hardware settings for the current active company or global.
     */
    public static function getSettings(): self
    {
        $companyId = session('active_company_id');
        
        $settings = static::where('company_id', $companyId)->first();

        if (!$settings) {
            $settings = static::create([
                'company_id' => $companyId,
                'terminal_name' => 'Default Counter / Terminal 1',
                'barcode_scanner_enabled' => true,
                'barcode_scanner_mode' => 'hid_keyboard',
                'barcode_scanner_suffix' => 'enter',
                'barcode_scanner_sensitivity' => 80,
                'barcode_scanner_sound' => true,
                'barcode_scanner_auto_clear' => true,
                'barcode_scanner_auto_increment' => true,
                'qr_scanner_enabled' => true,
                'qr_scanner_mode' => 'hardware_2d',
                'qr_scanner_auto_action' => 'product_lookup',
                'qr_scanner_sound' => true,
                'barcode_printer_enabled' => true,
                'barcode_printer_model' => 'tspl',
                'barcode_printer_connection' => 'system_print',
                'barcode_printer_paper_width' => '50mm',
                'barcode_printer_paper_height' => '25mm',
                'barcode_printer_darkness' => 8,
                'barcode_printer_dpi' => 203,
                'thermal_printer_enabled' => true,
                'thermal_printer_paper_size' => '80mm',
                'thermal_printer_connection' => 'system_print',
                'thermal_printer_auto_cutter' => 'full',
                'thermal_printer_cash_drawer_pulse' => 'pin2',
                'thermal_printer_density' => 'normal',
                'thermal_printer_auto_print' => false,
                'standard_printer_enabled' => true,
                'standard_printer_paper_size' => 'a4',
                'standard_printer_color_mode' => 'color',
                'standard_printer_orientation' => 'portrait',
                'standard_printer_auto_print' => false,
            ]);
        }

        return $settings;
    }
}
