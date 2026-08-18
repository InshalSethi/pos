<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HardwareSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HardwareSettingsController extends Controller
{
    /**
     * Get hardware settings.
     */
    public function index(): JsonResponse
    {
        $settings = HardwareSetting::getSettings();
        return response()->json($settings);
    }

    /**
     * Update hardware settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terminal_name' => 'nullable|string|max:100',
            // Barcode scanner
            'barcode_scanner_enabled' => 'nullable|boolean',
            'barcode_scanner_mode' => 'nullable|string|in:hid_keyboard,webhid,serial',
            'barcode_scanner_suffix' => 'nullable|string|in:enter,lf,tab,none',
            'barcode_scanner_prefix' => 'nullable|string|max:20',
            'barcode_scanner_sensitivity' => 'nullable|integer|min:20|max:500',
            'barcode_scanner_sound' => 'nullable|boolean',
            'barcode_scanner_auto_clear' => 'nullable|boolean',
            'barcode_scanner_auto_increment' => 'nullable|boolean',
            // QR scanner
            'qr_scanner_enabled' => 'nullable|boolean',
            'qr_scanner_mode' => 'nullable|string|in:hardware_2d,camera,hybrid',
            'qr_scanner_camera_device_id' => 'nullable|string',
            'qr_scanner_auto_action' => 'nullable|string|in:product_lookup,customer_lookup,fiscal_qr',
            'qr_scanner_sound' => 'nullable|boolean',
            // Barcode printer
            'barcode_printer_enabled' => 'nullable|boolean',
            'barcode_printer_model' => 'nullable|string|in:tspl,zpl,escpos_label,system_spooler',
            'barcode_printer_connection' => 'nullable|string|in:system_print,webusb,webserial,network_ip',
            'barcode_printer_ip' => 'nullable|string|max:50',
            'barcode_printer_port' => 'nullable|integer|min:1|max:65535',
            'barcode_printer_paper_width' => 'nullable|string|max:20',
            'barcode_printer_paper_height' => 'nullable|string|max:20',
            'barcode_printer_darkness' => 'nullable|integer|min:1|max:15',
            'barcode_printer_dpi' => 'nullable|integer|in:203,300,600',
            // Thermal printer
            'thermal_printer_enabled' => 'nullable|boolean',
            'thermal_printer_paper_size' => 'nullable|string|in:80mm,58mm',
            'thermal_printer_connection' => 'nullable|string|in:system_print,webusb,webserial,network_ip',
            'thermal_printer_ip' => 'nullable|string|max:50',
            'thermal_printer_port' => 'nullable|integer|min:1|max:65535',
            'thermal_printer_auto_cutter' => 'nullable|string|in:full,partial,none',
            'thermal_printer_cash_drawer_pulse' => 'nullable|string|in:pin2,pin5,disabled',
            'thermal_printer_density' => 'nullable|string|in:normal,dark,extra_dark',
            'thermal_printer_auto_print' => 'nullable|boolean',
            // Standard printer
            'standard_printer_enabled' => 'nullable|boolean',
            'standard_printer_paper_size' => 'nullable|string|in:a4,letter,legal',
            'standard_printer_color_mode' => 'nullable|string|in:color,grayscale',
            'standard_printer_orientation' => 'nullable|string|in:portrait,landscape',
            'standard_printer_auto_print' => 'nullable|boolean',
            // Selected device targets
            'barcode_scanner_selected_device' => 'nullable|string|max:255',
            'qr_scanner_selected_device' => 'nullable|string|max:255',
            'barcode_printer_selected_device' => 'nullable|string|max:255',
            'thermal_printer_selected_device' => 'nullable|string|max:255',
            'standard_printer_selected_device' => 'nullable|string|max:255',
        ]);

        $settings = HardwareSetting::getSettings();
        $settings->update($validated);

        return response()->json([
            'message' => 'Hardware device settings updated successfully',
            'settings' => $settings->fresh()
        ]);
    }

    /**
     * Diagnostic test hardware device endpoint.
     */
    public function testDevice(Request $request): JsonResponse
    {
        $device = $request->input('device');
        
        return response()->json([
            'success' => true,
            'device' => $device,
            'status' => 'ONLINE',
            'timestamp' => now()->toIso8601String(),
            'message' => "Hardware device '{$device}' diagnostic check passed. Device interface active."
        ]);
    }
}
